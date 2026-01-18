<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        $collections = Collection::where('team_id', $team->id)
            ->whereNull('parent_id')
            ->with(['children', 'documents'])
            ->orderBy('sort_order')
            ->get();

        return view('collections.index', compact('collections'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        $parentCollection = null;
        if ($request->filled('parent')) {
            $parentCollection = Collection::where('team_id', $team->id)
                ->findOrFail($request->parent);
        }

        $collections = Collection::where('team_id', $team->id)
            ->orderBy('name')
            ->get();

        return view('collections.create', compact('parentCollection', 'collections'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if (!$user->canEditInTeam($team)) {
            abort(403, 'You do not have permission to create collections.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:collections,id',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'permission' => 'in:public,team,private',
        ]);

        $collection = Collection::create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? null,
            'permission' => $validated['permission'] ?? 'team',
        ]);

        ActivityLog::log('created', $collection);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'collection' => $collection,
            ]);
        }

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Collection created successfully.');
    }

    public function show(Collection $collection)
    {
        $this->authorizeCollection($collection);

        $collection->load(['children', 'documents.creator', 'documents.tags']);

        $documents = $collection->documents()
            ->with(['creator', 'tags'])
            ->orderBy('sort_order')
            ->paginate(20);

        $breadcrumbs = $collection->getBreadcrumbs();

        return view('collections.show', compact('collection', 'documents', 'breadcrumbs'));
    }

    public function edit(Collection $collection)
    {
        $this->authorizeCollection($collection, 'edit');

        $user = auth()->user();
        $team = $user->currentTeam();

        $collections = Collection::where('team_id', $team->id)
            ->where('id', '!=', $collection->id)
            ->orderBy('name')
            ->get();

        return view('collections.edit', compact('collection', 'collections'));
    }

    public function update(Request $request, Collection $collection)
    {
        $this->authorizeCollection($collection, 'edit');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:collections,id',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|max:7',
            'permission' => 'in:public,team,private',
        ]);

        // Prevent setting self as parent
        if (isset($validated['parent_id']) && $validated['parent_id'] == $collection->id) {
            return back()->withErrors(['parent_id' => 'A collection cannot be its own parent.']);
        }

        // Prevent setting a descendant as parent (would create a loop)
        if (isset($validated['parent_id'])) {
            $descendants = $collection->getAllDescendants()->pluck('id')->toArray();
            if (in_array($validated['parent_id'], $descendants)) {
                return back()->withErrors(['parent_id' => 'Cannot move a collection into one of its sub-collections.']);
            }
        }

        $collection->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'parent_id' => $validated['parent_id'] ?? null,
            'icon' => $validated['icon'] ?? $collection->icon,
            'color' => $validated['color'] ?? $collection->color,
            'permission' => $validated['permission'] ?? $collection->permission,
        ]);

        ActivityLog::log('updated', $collection);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'collection' => $collection->fresh(),
            ]);
        }

        return redirect()->route('collections.show', $collection)
            ->with('success', 'Collection updated successfully.');
    }

    public function destroy(Collection $collection)
    {
        $this->authorizeCollection($collection, 'delete');

        // Check if collection has documents
        if ($collection->allDocuments()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete a collection that contains documents. Please move or delete the documents first.']);
        }

        // Check if collection has children
        if ($collection->children()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete a collection that has sub-collections. Please delete sub-collections first.']);
        }

        $collection->delete();

        ActivityLog::log('deleted', $collection, ['name' => $collection->name]);

        return redirect()->route('collections.index')
            ->with('success', 'Collection deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if (!$user->canEditInTeam($team)) {
            abort(403);
        }

        $validated = $request->validate([
            'collections' => 'required|array',
            'collections.*.id' => 'required|exists:collections,id',
            'collections.*.sort_order' => 'required|integer',
        ]);

        DB::transaction(function () use ($validated, $team) {
            foreach ($validated['collections'] as $item) {
                Collection::where('id', $item['id'])
                    ->where('team_id', $team->id)
                    ->update(['sort_order' => $item['sort_order']]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function tree()
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        $collections = Collection::where('team_id', $team->id)
            ->whereNull('parent_id')
            ->with(['children.children', 'documents' => function ($q) {
                $q->select('id', 'collection_id', 'title', 'emoji', 'slug')
                    ->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json($collections);
    }

    protected function authorizeCollection(Collection $collection, string $action = 'view'): void
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        if ($collection->team_id !== $team->id) {
            abort(403, 'You do not have access to this collection.');
        }

        if ($action === 'edit' || $action === 'delete') {
            if (!$user->canEditInTeam($team)) {
                abort(403, 'You do not have permission to modify collections.');
            }
        }
    }
}