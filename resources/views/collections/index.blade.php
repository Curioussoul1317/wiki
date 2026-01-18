@extends('layouts.app')

@section('title', 'Collections')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Collections</h1>
    <a href="{{ route('collections.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Collection
    </a>
</div>

<div class="row g-4">
    @forelse($collections as $collection)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start">
                        <span class="fs-2 me-3" style="color: {{ $collection->color }}">{{ $collection->icon }}</span>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1">
                                <a href="{{ route('collections.show', $collection) }}" class="text-decoration-none text-dark">
                                    {{ $collection->name }}
                                </a>
                            </h5>
                            @if($collection->description)
                                <p class="card-text text-muted small mb-2">{{ Str::limit($collection->description, 80) }}</p>
                            @endif
                            <div class="d-flex gap-3 text-muted small">
                                <span><i class="bi bi-file-text me-1"></i>{{ $collection->getDocumentCount() }} docs</span>
                                @if($collection->children->count() > 0)
                                    <span><i class="bi bi-folder me-1"></i>{{ $collection->children->count() }} sub</span>
                                @endif
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('collections.edit', $collection) }}"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                <li><a class="dropdown-item" href="{{ route('collections.create', ['parent' => $collection->id]) }}"><i class="bi bi-folder-plus me-2"></i>Add Sub-collection</a></li>
                                <li><a class="dropdown-item" href="{{ route('documents.create', ['collection' => $collection->id]) }}"><i class="bi bi-file-plus me-2"></i>Add Document</a></li>
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

                <!-- Sub-collections -->
                @if($collection->children->isNotEmpty())
                    <div class="card-footer bg-light p-2">
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($collection->children->take(4) as $child)
                                <a href="{{ route('collections.show', $child) }}" class="badge bg-white text-dark border text-decoration-none">
                                    {{ $child->icon }} {{ Str::limit($child->name, 15) }}
                                </a>
                            @endforeach
                            @if($collection->children->count() > 4)
                                <span class="badge bg-secondary">+{{ $collection->children->count() - 4 }} more</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="empty-state py-5">
                <i class="bi bi-folder empty-state-icon"></i>
                <h5>No collections yet</h5>
                <p class="text-muted">Collections help you organize related documents together.</p>
                <a href="{{ route('collections.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Create your first collection
                </a>
            </div>
        </div>
    @endforelse
</div>
@endsection