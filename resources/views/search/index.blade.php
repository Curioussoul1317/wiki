@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
<div class="mb-4">
    <h1 class="h3 mb-3">Search Results</h1>
    
    <form action="{{ route('search') }}" method="GET" class="row g-3">
        <div class="col-md-8">
            <div class="input-group input-group-lg">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" class="form-control" name="q" value="{{ $query }}" placeholder="Search documents and collections..." autofocus>
            </div>
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select form-select-lg">
                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All</option>
                <option value="documents" {{ $type === 'documents' ? 'selected' : '' }}>Documents only</option>
                <option value="collections" {{ $type === 'collections' ? 'selected' : '' }}>Collections only</option>
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </form>
</div>

@if($query)
    <p class="text-muted mb-4">
        Found {{ $results['documents']->count() + $results['collections']->count() }} results for "{{ $query }}"
    </p>

    <!-- Collections -->
    @if($results['collections']->isNotEmpty())
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-folder me-2"></i>Collections ({{ $results['collections']->count() }})
            </div>
            <div class="card-body p-0">
                @foreach($results['collections'] as $collection)
                    <a href="{{ route('collections.show', $collection) }}" class="d-flex align-items-center p-3 border-bottom text-decoration-none text-dark">
                        <span class="fs-4 me-3" style="color: {{ $collection->color }}">{{ $collection->icon }}</span>
                        <div>
                            <div class="fw-semibold">{{ $collection->name }}</div>
                            @if($collection->description)
                                <small class="text-muted">{{ Str::limit($collection->description, 100) }}</small>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Documents -->
    @if($results['documents']->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <i class="bi bi-file-text me-2"></i>Documents ({{ $results['documents']->count() }})
            </div>
            <div class="card-body p-0">
                @foreach($results['documents'] as $document)
                    <a href="{{ route('documents.show', $document) }}" class="d-flex align-items-center p-3 border-bottom text-decoration-none text-dark">
                        <span class="fs-4 me-3">{{ $document->emoji }}</span>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $document->title }}</div>
                            <p class="text-muted small mb-1">{{ $document->getExcerpt(150) }}</p>
                            <div class="d-flex align-items-center gap-2">
                                @if($document->collection)
                                    <span class="badge bg-light text-dark">
                                        {{ $document->collection->icon }} {{ $document->collection->name }}
                                    </span>
                                @endif
                                <small class="text-muted">
                                    Updated {{ $document->updated_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($results['documents']->isEmpty() && $results['collections']->isEmpty())
        <div class="empty-state py-5">
            <i class="bi bi-search empty-state-icon"></i>
            <h5>No results found</h5>
            <p class="text-muted">Try different keywords or check your spelling</p>
        </div>
    @endif
@else
    <div class="empty-state py-5">
        <i class="bi bi-search empty-state-icon"></i>
        <h5>Enter a search term</h5>
        <p class="text-muted">Search for documents and collections</p>
    </div>
@endif
@endsection