<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\ShareLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ShareController extends Controller
{
    public function create(Document $document)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($document->team_id !== $team->id) {
            abort(403);
        }

        if (!$user->canEditInTeam($team)) {
            abort(403);
        }

        $shareLinks = $document->shareLinks()->with('creator')->get();

        return view('documents.share', compact('document', 'shareLinks'));
    }

    public function store(Request $request, Document $document)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($document->team_id !== $team->id) {
            abort(403);
        }

        $validated = $request->validate([
            'permission' => 'in:view,edit',
            'password' => 'nullable|string|min:6',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $shareLink = ShareLink::create([
            'document_id' => $document->id,
            'created_by' => $user->id,
            'permission' => $validated['permission'] ?? 'view',
            'requires_password' => !empty($validated['password']),
            'password' => !empty($validated['password']) ? Hash::make($validated['password']) : null,
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'share_link' => $shareLink,
                'url' => $shareLink->getUrl(),
            ]);
        }

        return back()->with('success', 'Share link created: ' . $shareLink->getUrl());
    }

    public function destroy(ShareLink $shareLink)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($shareLink->document->team_id !== $team->id) {
            abort(403);
        }

        $shareLink->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return back()->with('success', 'Share link deleted.');
    }

    public function show(string $token)
    {
        $shareLink = ShareLink::where('token', $token)->firstOrFail();

        if ($shareLink->isExpired()) {
            abort(410, 'This share link has expired.');
        }

        if ($shareLink->requires_password && !session("share_verified_{$token}")) {
            return view('share.password', compact('shareLink'));
        }

        $shareLink->incrementViewCount();

        $document = $shareLink->document;
        $document->load(['creator', 'collection']);

        return view('share.show', compact('document', 'shareLink'));
    }

    public function verifyPassword(Request $request, string $token)
    {
        $shareLink = ShareLink::where('token', $token)->firstOrFail();

        if ($shareLink->isExpired()) {
            abort(410, 'This share link has expired.');
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!$shareLink->checkPassword($request->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        session(["share_verified_{$token}" => true]);

        return redirect()->route('share.show', $token);
    }
}