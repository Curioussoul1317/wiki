@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Documents</h1>
    <a href="{{ route('documents.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Document
    </a>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('documents.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Search documents...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="collection" class="form-select">
                    <option value="">All Collections</option>
                    @foreach($collections as $collection)
                        <option value="{{ $collection->id }}" {{ request('collection') == $collection->id ? 'selected' : '' }}>
                            {{ $collection->icon }} {{ $collection->name }}
                        </option>
                        @foreach($collection->children as $child)
                            <option value="{{ $child->id }}" {{ request('collection') == $child->id ? 'selected' : '' }}>
                                &nbsp;&nbsp;└ {{ $child->icon }} {{ $child->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="tag" class="form-select">
                    <option value="">All Tags</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'selected' : '' }}>
                            {{ $tag->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Documents List -->
<div class="card">
    <div class="card-body p-0">
        @forelse($documents as $document)
            <div class="d-flex align-items-center p-3 border-bottom">
                <span class="fs-4 me-3">{{ $document->emoji }}</span>
                <div class="flex-grow-1">
                    <a href="{{ route('documents.show', $document) }}" class="fw-semibold text-decoration-none text-dark d-block">
                        {{ $document->title }}
                    </a>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        @if($document->collection)
                            <span class="badge bg-light text-dark">
                                <i class="bi bi-folder me-1"></i>{{ $document->collection->name }}
                            </span>
                        @endif
                        @foreach($document->tags->take(3) as $tag)
                            <span class="tag" style="background: {{ $tag->color }}20; color: {{ $tag->color }}">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                        <small class="text-muted ms-auto">
                            Updated {{ $document->updated_at->diffForHumans() }} by {{ $document->lastEditor?->name ?? $document->creator->name }}
                        </small>
                    </div>
                </div>
                <div class="dropdown ms-3">
                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('documents.edit', $document) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                        <li><a class="dropdown-item" href="{{ route('documents.share', $document) }}"><i class="bi bi-share me-2"></i>Share</a></li>
                        <li>
                            <form action="{{ route('documents.star', $document) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-star me-2"></i>{{ $document->isStarredBy() ? 'Unstar' : 'Star' }}
                                </button>
                            </form>
                        </li>
                        <li>
                            <form action="{{ route('documents.duplicate', $document) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-copy me-2"></i>Duplicate
                                </button>
                            </form>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this document?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-trash me-2"></i>Delete
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        @empty
            <div class="empty-state py-5">
                <i class="bi bi-file-text empty-state-icon"></i>
                <h5>No documents found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['search', 'collection', 'tag']))
                        Try adjusting your filters
                    @else
                        Create your first document to get started
                    @endif
                </p>
                @unless(request()->hasAny(['search', 'collection', 'tag']))
                    <a href="{{ route('documents.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> New Document
                    </a>
                @endunless
            </div>
        @endforelse
    </div>
</div>

<!-- Pagination -->
@if($documents->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $documents->links() }}
    </div>
@endif
@endsection