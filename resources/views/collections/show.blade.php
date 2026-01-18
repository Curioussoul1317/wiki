@extends('layouts.app')

@section('title', $collection->name)

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('collections.index') }}">Collections</a></li>
        @foreach($breadcrumbs as $crumb)
            @if($crumb['id'] !== $collection->id)
                <li class="breadcrumb-item">
                    <a href="{{ route('collections.show', $crumb['id']) }}">{{ $crumb['name'] }}</a>
                </li>
            @endif
        @endforeach
        <li class="breadcrumb-item active">{{ $collection->name }}</li>
    </ol>
</nav>

<!-- Collection Header -->
<div class="d-flex align-items-start mb-4">
    <span class="fs-1 me-3" style="color: {{ $collection->color }}">{{ $collection->icon }}</span>
    <div class="flex-grow-1">
        <h1 class="h2 mb-2">{{ $collection->name }}</h1>
        @if($collection->description)
            <p class="text-muted mb-0">{{ $collection->description }}</p>
        @endif
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('documents.create', ['collection' => $collection->id]) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> New Document
        </a>
        <div class="dropdown">
            <button class="btn btn-outline-secondary" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('collections.edit', $collection) }}"><i class="bi bi-pencil me-2"></i>Edit Collection</a></li>
                <li><a class="dropdown-item" href="{{ route('collections.create', ['parent' => $collection->id]) }}"><i class="bi bi-folder-plus me-2"></i>Add Sub-collection</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('collections.destroy', $collection) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Sub-collections -->
        @if($collection->children->isNotEmpty())
            <div class="card mb-4">
                <div class="card-header">
                    <i class="bi bi-folder me-2"></i>Sub-collections
                </div>
                <div class="card-body p-0">
                    @foreach($collection->children as $child)
                        <a href="{{ route('collections.show', $child) }}" class="d-flex align-items-center p-3 border-bottom text-decoration-none text-dark">
                            <span class="fs-4 me-3" style="color: {{ $child->color }}">{{ $child->icon }}</span>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $child->name }}</div>
                                <small class="text-muted">{{ $child->getDocumentCount() }} documents</small>
                            </div>
                            <i class="bi bi-chevron-right text-muted"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Documents -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-file-text me-2"></i>Documents</span>
                <span class="badge bg-secondary">{{ $documents->total() }}</span>
            </div>
            <div class="card-body p-0">
                @forelse($documents as $document)
                    <div class="d-flex align-items-center p-3 border-bottom">
                        <span class="fs-4 me-3">{{ $document->emoji }}</span>
                        <div class="flex-grow-1">
                            <a href="{{ route('documents.show', $document) }}" class="fw-semibold text-decoration-none text-dark d-block">
                                {{ $document->title }}
                            </a>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                @foreach($document->tags->take(2) as $tag)
                                    <span class="tag" style="background: {{ $tag->color }}20; color: {{ $tag->color }}">
                                        {{ $tag->name }}
                                    </span>
                                @endforeach
                                <small class="text-muted">
                                    Updated {{ $document->updated_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('documents.edit', $document) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                <li>
                                    <form action="{{ route('documents.destroy', $document) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i>Delete</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                @empty
                    <div class="empty-state py-5">
                        <i class="bi bi-file-text empty-state-icon"></i>
                        <p class="mb-3">No documents in this collection yet</p>
                        <a href="{{ route('documents.create', ['collection' => $collection->id]) }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus me-1"></i> Add Document
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        @if($documents->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $documents->links() }}
            </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">Collection Info</div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt class="text-muted small">Created by</dt>
                    <dd>{{ $collection->creator->name }}</dd>
                    
                    <dt class="text-muted small">Created</dt>
                    <dd>{{ $collection->created_at->format('M d, Y') }}</dd>
                    
                    <dt class="text-muted small">Total documents</dt>
                    <dd>{{ $collection->getDocumentCount() }}</dd>
                    
                    <dt class="text-muted small">Sub-collections</dt>
                    <dd>{{ $collection->children->count() }}</dd>
                    
                    <dt class="text-muted small">Visibility</dt>
                    <dd>
                        @if($collection->permission === 'public')
                            <span class="badge bg-success">Public</span>
                        @elseif($collection->permission === 'team')
                            <span class="badge bg-primary">Team</span>
                        @else
                            <span class="badge bg-secondary">Private</span>
                        @endif
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection