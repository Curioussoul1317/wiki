<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Document;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        $query = $request->get('q', '');
        $type = $request->get('type', 'all');

        $results = [
            'documents' => collect(),
            'collections' => collect(),
        ];

        if (strlen($query) >= 2) {
            if ($type === 'all' || $type === 'documents') {
                $results['documents'] = Document::where('team_id', $team->id)
                    ->notTemplates()
                    ->search($query)
                    ->with(['creator', 'collection'])
                    ->orderBy('updated_at', 'desc')
                    ->limit(20)
                    ->get();
            }

            if ($type === 'all' || $type === 'collections') {
                $results['collections'] = Collection::where('team_id', $team->id)
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'LIKE', "%{$query}%")
                            ->orWhere('description', 'LIKE', "%{$query}%");
                    })
                    ->orderBy('name')
                    ->limit(10)
                    ->get();
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'query' => $query,
                'results' => $results,
                'total' => $results['documents']->count() + $results['collections']->count(),
            ]);
        }

        return view('search.index', compact('query', 'type', 'results'));
    }

    public function quick(Request $request)
    {
        $user = auth()->user();
        $team = $user->currentTeam();

        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $documents = Document::where('team_id', $team->id)
            ->notTemplates()
            ->where('title', 'LIKE', "%{$query}%")
            ->select('id', 'title', 'emoji', 'slug', 'collection_id', 'updated_at')
            ->with('collection:id,name')
            ->orderBy('updated_at', 'desc')
            ->limit(8)
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'title' => $doc->title,
                    'emoji' => $doc->emoji,
                    'slug' => $doc->slug,
                    'type' => 'document',
                    'collection' => $doc->collection?->name,
                    'url' => route('documents.show', $doc),
                ];
            });

        $collections = Collection::where('team_id', $team->id)
            ->where('name', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'icon', 'slug')
            ->orderBy('name')
            ->limit(4)
            ->get()
            ->map(function ($col) {
                return [
                    'id' => $col->id,
                    'title' => $col->name,
                    'emoji' => $col->icon,
                    'slug' => $col->slug,
                    'type' => 'collection',
                    'url' => route('collections.show', $col),
                ];
            });

        return response()->json([
            'results' => $documents->merge($collections)->values(),
        ]);
    }
}