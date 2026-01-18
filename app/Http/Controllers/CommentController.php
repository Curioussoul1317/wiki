<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Comment;
use App\Models\Document;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Document $document)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($document->team_id !== $team->id) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
            'parent_id' => 'nullable|exists:comments,id',
            'position' => 'nullable|array',
        ]);

        $comment = Comment::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
            'position' => $validated['position'] ?? null,
        ]);

        $comment->load('user');

        ActivityLog::log('commented', $document);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $comment,
            ]);
        }

        return back()->with('success', 'Comment added successfully.');
    }

    public function update(Request $request, Comment $comment)
    {
        $user = auth()->user();

        if ($comment->user_id !== $user->id) {
            abort(403, 'You can only edit your own comments.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $comment->update([
            'content' => $validated['content'],
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $comment->fresh('user'),
            ]);
        }

        return back()->with('success', 'Comment updated successfully.');
    }

    public function destroy(Comment $comment)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($comment->user_id !== $user->id && !$user->isTeamAdmin($team)) {
            abort(403, 'You do not have permission to delete this comment.');
        }

        $comment->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
            ]);
        }

        return back()->with('success', 'Comment deleted successfully.');
    }

    public function resolve(Comment $comment)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($comment->document->team_id !== $team->id) {
            abort(403);
        }

        $comment->resolve();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $comment->fresh(),
            ]);
        }

        return back()->with('success', 'Comment resolved.');
    }

    public function unresolve(Comment $comment)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($comment->document->team_id !== $team->id) {
            abort(403);
        }

        $comment->unresolve();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'comment' => $comment->fresh(),
            ]);
        }

        return back()->with('success', 'Comment unresolved.');
    }
}