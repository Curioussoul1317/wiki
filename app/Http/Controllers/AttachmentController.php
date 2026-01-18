<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function store(Request $request, Document $document)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($document->team_id !== $team->id) {
            abort(403);
        }

        if (!$user->canEditInTeam($team)) {
            abort(403, 'You do not have permission to upload files.');
        }

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        $path = $file->storeAs(
            "attachments/{$team->id}/{$document->id}",
            $filename,
            'public'
        );

        $attachment = Attachment::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'filename' => $filename,
            'original_filename' => $originalName,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
        ]);

        return response()->json([
            'success' => true,
            'attachment' => [
                'id' => $attachment->id,
                'filename' => $attachment->original_filename,
                'url' => $attachment->getUrl(),
                'size' => $attachment->getFormattedSize(),
                'mime_type' => $attachment->mime_type,
                'is_image' => $attachment->isImage(),
            ],
        ]);
    }

    public function destroy(Attachment $attachment)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($attachment->document->team_id !== $team->id) {
            abort(403);
        }

        if (!$user->canEditInTeam($team)) {
            abort(403);
        }

        $attachment->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function download(Attachment $attachment)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($attachment->document->team_id !== $team->id) {
            abort(403);
        }

        return Storage::disk('public')->download(
            $attachment->path,
            $attachment->original_filename
        );
    }
}