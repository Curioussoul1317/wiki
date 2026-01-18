<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        $tags = Tag::where('team_id', $team->id)
            ->withCount('documents')
            ->orderBy('name')
            ->get();

        return view('tags.index', compact('tags'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if (!$user->canEditInTeam($team)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'nullable|string|max:7',
        ]);

        $tag = Tag::create([
            'team_id' => $team->id,
            'name' => $validated['name'],
            'color' => $validated['color'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'tag' => $tag,
            ]);
        }

        return back()->with('success', 'Tag created successfully.');
    }

    public function update(Request $request, Tag $tag)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($tag->team_id !== $team->id) {
            abort(403);
        }

        if (!$user->canEditInTeam($team)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'nullable|string|max:7',
        ]);

        $tag->update([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? $tag->color,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'tag' => $tag->fresh(),
            ]);
        }

        return back()->with('success', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($tag->team_id !== $team->id) {
            abort(403);
        }

        if (!$user->canEditInTeam($team)) {
            abort(403);
        }

        $tag->documents()->detach();
        $tag->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return back()->with('success', 'Tag deleted successfully.');
    }

    public function show(Tag $tag)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($tag->team_id !== $team->id) {
            abort(403);
        }

        $documents = $tag->documents()
            ->with(['creator', 'collection'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('tags.show', compact('tag', 'documents'));
    }
}