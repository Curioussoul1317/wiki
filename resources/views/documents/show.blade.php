@extends('layouts.app')

@section('title', $document->title)

@section('content')
<!-- Breadcrumb -->
@if(count($breadcrumbs) > 0)
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            @foreach($breadcrumbs as $crumb)
                <li class="breadcrumb-item">
                    @if($crumb['type'] === 'collection')
                        <a href="{{ route('collections.show', $crumb['id']) }}">{{ $crumb['name'] }}</a>
                    @else
                        <a href="{{ route('documents.show', $crumb['id']) }}">{{ $crumb['name'] }}</a>
                    @endif
                </li>
            @endforeach
            <li class="breadcrumb-item active">{{ $document->title }}</li>
        </ol>
    </nav>
@endif

<div class="row">
    <div class="col-lg-9">
        <!-- Document Header -->
        <div class="d-flex align-items-start mb-4">
            <span class="fs-1 me-3">{{ $document->emoji }}</span>
            <div class="flex-grow-1">
                <h1 class="h2 mb-2">{{ $document->title }}</h1>
                <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                    <span><i class="bi bi-person me-1"></i>Created by {{ $document->creator->name }}</span>
                    <span><i class="bi bi-calendar me-1"></i>{{ $document->created_at->format('M d, Y') }}</span>
                    <span><i class="bi bi-clock me-1"></i>{{ $document->getReadingTime() }} min read</span>
                </div>
            </div>
            <div class="d-flex gap-2">
                <form action="{{ route('documents.star', $document) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-warning">
                        <i class="bi bi-star{{ $document->isStarredBy() ? '-fill' : '' }}"></i>
                    </button>
                </form>
                <a href="{{ route('documents.edit', $document) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary" data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('documents.share', $document) }}"><i class="bi bi-share me-2"></i>Share</a></li>
                        <li><a class="dropdown-item" href="{{ route('documents.versions', $document) }}"><i class="bi bi-clock-history me-2"></i>Version History</a></li>
                        <li><a class="dropdown-item" href="{{ route('documents.export', [$document, 'md']) }}"><i class="bi bi-download me-2"></i>Export</a></li>
                        <li>
                            <form action="{{ route('documents.duplicate', $document) }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item"><i class="bi bi-copy me-2"></i>Duplicate</button>
                            </form>
                        </li>
                        <li><hr class="dropdown-divider"></li>
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
        </div>

        <!-- Tags -->
        @if($document->tags->isNotEmpty())
            <div class="mb-4">
                @foreach($document->tags as $tag)
                    <a href="{{ route('tags.show', $tag) }}" class="tag me-1" style="background: {{ $tag->color }}20; color: {{ $tag->color }}">
                        {{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <!-- Document Content -->
        <div class="card">
            <div class="card-body">
                <div class="document-content">{!! nl2br(e($document->content)) !!}</div>
            </div>
        </div>

        <!-- Child Documents -->
        @if($children->isNotEmpty())
            <div class="card mt-4">
                <div class="card-header"><i class="bi bi-diagram-3 me-2"></i>Sub-documents</div>
                <div class="card-body p-0">
                    @foreach($children as $child)
                        <a href="{{ route('documents.show', $child) }}" class="d-flex align-items-center p-3 border-bottom text-decoration-none text-dark">
                            <span class="me-3 fs-5">{{ $child->emoji }}</span>
                            <div>
                                <div class="fw-semibold">{{ $child->title }}</div>
                                <small class="text-muted">{{ $child->updated_at->diffForHumans() }}</small>
                            </div>
                            <i class="bi bi-chevron-right ms-auto text-muted"></i>
                        </a>
                    @endforeach
                </div>
                <div class="card-footer">
                    <a href="{{ route('documents.create', ['parent' => $document->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus me-1"></i> Add Sub-document
                    </a>
                </div>
            </div>
        @endif

        <!-- Comments Section -->
        <div class="card mt-4">
            <div class="card-header"><i class="bi bi-chat me-2"></i>Comments ({{ $document->allComments->count() }})</div>
            <div class="card-body">
                <form action="{{ route('documents.comments.store', $document) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="d-flex gap-3">
                        <img src="{{ auth()->user()->avatar_url }}" alt="" class="rounded-circle" width="40" height="40">
                        <div class="flex-grow-1">
                            <textarea class="form-control" name="content" rows="2" placeholder="Add a comment..." required></textarea>
                            <button type="submit" class="btn btn-primary btn-sm mt-2">Post Comment</button>
                        </div>
                    </div>
                </form>

                @forelse($document->comments as $comment)
                    <div class="d-flex gap-3 mb-3 {{ $comment->resolved ? 'opacity-50' : '' }}">
                        <img src="{{ $comment->user->avatar_url }}" alt="" class="rounded-circle" width="40" height="40">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <strong>{{ $comment->user->name }}</strong>
                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                @if($comment->resolved)<span class="badge bg-success">Resolved</span>@endif
                            </div>
                            <p class="mb-2">{{ $comment->content }}</p>
                            <div class="d-flex gap-2">
                                @unless($comment->resolved)
                                    <form action="{{ route('comments.resolve', $comment) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link text-success p-0">Resolve</button>
                                    </form>
                                @endunless
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">No comments yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-3">
        <div class="card mb-4">
            <div class="card-header">Document Info</div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt class="text-muted small">Created</dt>
                    <dd>{{ $document->created_at->format('M d, Y H:i') }}</dd>
                    <dt class="text-muted small">Last updated</dt>
                    <dd>{{ $document->updated_at->format('M d, Y H:i') }}</dd>
                    <dt class="text-muted small">Version</dt>
                    <dd>{{ $document->version }}</dd>
                    <dt class="text-muted small">Word count</dt>
                    <dd>{{ number_format($document->getWordCount()) }} words</dd>
                    @if($document->collection)
                        <dt class="text-muted small">Collection</dt>
                        <dd><a href="{{ route('collections.show', $document->collection) }}">{{ $document->collection->icon }} {{ $document->collection->name }}</a></dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .document-content { line-height: 1.8; font-size: 1rem; }
    .document-content pre { background: #f8f9fa; padding: 1rem; border-radius: 8px; }
    .document-content code { background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 4px; }
    .document-content blockquote { border-left: 4px solid #6366f1; padding-left: 1rem; color: #64748b; }
</style>
@endpush
@endsection