<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Collection;
use App\Models\Document;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        $query = Document::where('team_id', $team->id)
            ->notTemplates()
            ->with(['creator', 'collection', 'tags']);

        // Filter by collection
        if ($request->filled('collection')) {
            $query->where('collection_id', $request->collection);
        }

        // Filter by tag
        if ($request->filled('tag')) {
            $query->whereHas('tags', fn($q) => $q->where('tags.id', $request->tag));
        }

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Sort
        $sortBy = $request->get('sort', 'updated_at');
        $sortDir = $request->get('dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $documents = $query->paginate(20)->withQueryString();

        $collections = Collection::where('team_id', $team->id)
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();

        $tags = Tag::where('team_id', $team->id)->get();

        return view('documents.index', compact('documents', 'collections', 'tags'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        $collections = Collection::where('team_id', $team->id)
            ->orderBy('name')
            ->get();

        $parentDocument = null;
        if ($request->filled('parent')) {
            $parentDocument = Document::where('team_id', $team->id)
                ->findOrFail($request->parent);
        }

        $collection = null;
        if ($request->filled('collection')) {
            $collection = Collection::where('team_id', $team->id)
                ->findOrFail($request->collection);
        }

        $templates = Document::where('team_id', $team->id)
            ->templates()
            ->orderBy('title')
            ->get();

        return view('documents.create', compact('collections', 'parentDocument', 'collection', 'templates'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'collection_id' => 'nullable|exists:collections,id',
            'parent_id' => 'nullable|exists:documents,id',
            'emoji' => 'nullable|string|max:10',
            'is_template' => 'boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        // If using a template
        if ($request->filled('template_id')) {
            $template = Document::where('team_id', $team->id)
                ->templates()
                ->findOrFail($request->template_id);
            
            $validated['content'] = $template->content;
        }

        $document = Document::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'last_edited_by' => $user->id,
            'title' => $validated['title'],
            'content' => $validated['content'] ?? '',
            'collection_id' => $validated['collection_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'emoji' => $validated['emoji'] ?? null,
            'is_template' => $validated['is_template'] ?? false,
        ]);

        // Attach tags
        if (!empty($validated['tags'])) {
            $document->tags()->sync($validated['tags']);
        }

        // Create initial version
        $document->createVersion('Initial version');

        // Log activity
        ActivityLog::log('created', $document);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'document' => $document,
                'redirect' => route('documents.edit', $document),
            ]);
        }

        return redirect()->route('documents.edit', $document)
            ->with('success', 'Document created successfully.');
    }

    public function show(Document $document)
    {
        $this->authorizeDocument($document);

        $document->load(['creator', 'lastEditor', 'collection', 'tags', 'comments.user', 'comments.replies.user']);
        $document->markAsViewed();

        ActivityLog::log('viewed', $document);

        $children = $document->children()->orderBy('sort_order')->get();
        $breadcrumbs = $document->getBreadcrumbs();

        return view('documents.show', compact('document', 'children', 'breadcrumbs'));
    }

    public function edit(Document $document)
    {
        $this->authorizeDocument($document, 'edit');

        $user = auth()->user();
        $team = $user->currentTeam();

        $document->load(['creator', 'collection', 'tags']);

        $collections = Collection::where('team_id', $team->id)
            ->orderBy('name')
            ->get();

        $tags = Tag::where('team_id', $team->id)->get();
        $breadcrumbs = $document->getBreadcrumbs();

        return view('documents.edit', compact('document', 'collections', 'tags', 'breadcrumbs'));
    }

    public function update(Request $request, Document $document)
    {
        $this->authorizeDocument($document, 'edit');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'collection_id' => 'nullable|exists:collections,id',
            'parent_id' => 'nullable|exists:documents,id',
            'emoji' => 'nullable|string|max:10',
            'summary' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $document->id) {
            return back()->withErrors(['parent_id' => 'A document cannot be its own parent.']);
        }

        // Check if content changed for versioning
        $contentChanged = $document->content !== ($validated['content'] ?? '');
        $titleChanged = $document->title !== $validated['title'];

        if ($contentChanged || $titleChanged) {
            $document->createVersion($request->input('change_summary'));
        }

        $document->update([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? '',
            'collection_id' => $validated['collection_id'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'emoji' => $validated['emoji'] ?? $document->emoji,
            'summary' => $validated['summary'] ?? null,
            'last_edited_by' => auth()->id(),
        ]);

        // Sync tags
        $document->tags()->sync($validated['tags'] ?? []);

        ActivityLog::log('updated', $document);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'document' => $document->fresh(),
            ]);
        }

        return back()->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document)
    {
        $this->authorizeDocument($document, 'delete');

        $document->delete();

        ActivityLog::log('deleted', $document, ['name' => $document->title]);

        return redirect()->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    public function star(Document $document)
    {
        $this->authorizeDocument($document);

        $isStarred = $document->toggleStar();

        return response()->json([
            'success' => true,
            'starred' => $isStarred,
        ]);
    }

    public function duplicate(Document $document)
    {
        $this->authorizeDocument($document);

        $newDocument = $document->duplicate();

        ActivityLog::log('created', $newDocument, ['duplicated_from' => $document->id]);

        return redirect()->route('documents.edit', $newDocument)
            ->with('success', 'Document duplicated successfully.');
    }

    public function versions(Document $document)
    {
        $this->authorizeDocument($document);

        $versions = $document->versions()
            ->with('user')
            ->paginate(20);

        return view('documents.versions', compact('document', 'versions'));
    }

    public function restoreVersion(Document $document, int $versionId)
    {
        $this->authorizeDocument($document, 'edit');

        $version = $document->versions()->findOrFail($versionId);
        $document->restoreVersion($version);

        ActivityLog::log('restored', $document, ['version' => $version->version]);

        return redirect()->route('documents.edit', $document)
            ->with('success', 'Document restored to version ' . $version->version);
    }

    public function publish(Document $document)
    {
        $this->authorizeDocument($document, 'edit');

        $document->publish();

        ActivityLog::log('published', $document);

        return back()->with('success', 'Document published successfully.');
    }

    public function unpublish(Document $document)
    {
        $this->authorizeDocument($document, 'edit');

        $document->unpublish();

        ActivityLog::log('unpublished', $document);

        return back()->with('success', 'Document unpublished.');
    }

    public function move(Request $request, Document $document)
    {
        $this->authorizeDocument($document, 'edit');

        $validated = $request->validate([
            'collection_id' => 'nullable|exists:collections,id',
            'parent_id' => 'nullable|exists:documents,id',
        ]);

        $document->update([
            'collection_id' => $validated['collection_id'],
            'parent_id' => $validated['parent_id'],
        ]);

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'documents' => 'required|array',
            'documents.*.id' => 'required|exists:documents,id',
            'documents.*.sort_order' => 'required|integer',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['documents'] as $item) {
                Document::where('id', $item['id'])->update([
                    'sort_order' => $item['sort_order'],
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function export(Document $document, string $format = 'md')
    {
        $this->authorizeDocument($document);

        $filename = \Illuminate\Support\Str::slug($document->title);

        switch ($format) {
            case 'html':
                $content = view('exports.document-html', compact('document'))->render();
                return response($content)
                    ->header('Content-Type', 'text/html')
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}.html\"");

            case 'md':
            default:
                $content = "# {$document->title}\n\n{$document->content}";
                return response($content)
                    ->header('Content-Type', 'text/markdown')
                    ->header('Content-Disposition', "attachment; filename=\"{$filename}.md\"");
        }
    }

    protected function authorizeDocument(Document $document, string $action = 'view'): void
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($document->team_id !== $team->id) {
            abort(403, 'You do not have access to this document.');
        }

        if ($action === 'edit' || $action === 'delete') {
            if (!$user->canEditInTeam($team)) {
                abort(403, 'You do not have permission to edit documents.');
            }
        }
    }
}