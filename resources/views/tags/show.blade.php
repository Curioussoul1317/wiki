@extends('layouts.app')

@section('title', 'Tag: ' . $tag->name)

@section('content')
<div class="d-flex align-items-center mb-4">
    <a href="{{ route('tags.index') }}" class="btn btn-outline-secondary me-3">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="badge me-3" style="background-color: {{ $tag->color }}; font-size: 1.2rem; padding: 0.5rem 1rem;">
        {{ $tag->name }}
    </span>
    <span class="text-muted">{{ $documents->total() }} documents</span>
</div>

<div class="card">
    <div class="card-header">
        <i class="bi bi-file-text me-2"></i>Documents with this tag
    </div>
    <div class="card-body p-0">
        @forelse($documents as $document)
            <a href="{{ route('documents.show', $document) }}" class="d-flex align-items-center p-3 border-bottom text-decoration-none text-dark">
                <span class="fs-4 me-3">{{ $document->emoji }}</span>
                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $document->title }}</div>
                    <small class="text-muted">
                        @if($document->collection)
                            <i class="bi bi-folder me-1"></i>{{ $document->collection->name }} •
                        @endif
                        Updated {{ $document->updated_at->diffForHumans() }}
                    </small>
                </div>
                <i class="bi bi-chevron-right text-muted"></i>
            </a>
        @empty
            <div class="empty-state py-5">
                <i class="bi bi-file-text empty-state-icon"></i>
                <p class="mb-0">No documents with this tag yet</p>
            </div>
        @endforelse
    </div>
</div>

@if($documents->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $documents->links() }}
    </div>
@endif
@endsection