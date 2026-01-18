@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-muted mb-0">Here's what's happening in {{ $team->name }}</p>
    </div>
    <a href="{{ route('documents.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Document
    </a>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-file-text fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h2 class="mb-0">{{ $stats['total_documents'] }}</h2>
                        <p class="text-muted mb-0">Total Documents</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-folder fs-4 text-success"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h2 class="mb-0">{{ $stats['total_collections'] }}</h2>
                        <p class="text-muted mb-0">Collections</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-person-check fs-4 text-info"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h2 class="mb-0">{{ $stats['my_documents'] }}</h2>
                        <p class="text-muted mb-0">My Documents</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recently Viewed -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Recently Viewed</span>
                <a href="{{ route('documents.index') }}" class="btn btn-sm btn-link">View all</a>
            </div>
            <div class="card-body p-0">
                @forelse($recentDocuments as $document)
                    <a href="{{ route('documents.show', $document) }}" class="d-flex align-items-center p-3 border-bottom text-decoration-none text-dark">
                        <span class="me-3 fs-4">{{ $document->emoji }}</span>
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
                        <i class="bi bi-clock-history empty-state-icon"></i>
                        <p class="mb-0">No recently viewed documents</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recently Updated -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="bi bi-lightning me-2"></i>Recently Updated in Team
            </div>
            <div class="card-body p-0">
                @forelse($updatedDocuments as $document)
                    <a href="{{ route('documents.show', $document) }}" class="d-flex align-items-center p-3 border-bottom text-decoration-none text-dark">
                        <span class="me-3 fs-4">{{ $document->emoji }}</span>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $document->title }}</div>
                            <small class="text-muted">
                                Updated by {{ $document->lastEditor?->name ?? $document->creator->name }} •
                                {{ $document->updated_at->diffForHumans() }}
                            </small>
                        </div>
                    </a>
                @empty
                    <div class="empty-state py-5">
                        <i class="bi bi-file-text empty-state-icon"></i>
                        <p class="mb-0">No documents yet</p>
                        <a href="{{ route('documents.create') }}" class="btn btn-primary mt-3">Create your first document</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Starred -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-star me-2"></i>Starred
            </div>
            <div class="card-body p-0">
                @forelse($starredDocuments as $document)
                    <a href="{{ route('documents.show', $document) }}" class="d-flex align-items-center p-3 border-bottom text-decoration-none text-dark">
                        <span class="me-2">{{ $document->emoji }}</span>
                        <span class="fw-medium">{{ Str::limit($document->title, 30) }}</span>
                    </a>
                @empty
                    <div class="p-3 text-center text-muted">
                        <i class="bi bi-star d-block mb-2 fs-4"></i>
                        <small>Star documents for quick access</small>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <i class="bi bi-activity me-2"></i>Recent Activity
            </div>
            <div class="card-body p-0">
                @forelse($activities as $activity)
                    <div class="activity-item px-3">
                        <div class="activity-icon">
                            <i class="{{ $activity->getIconClass() }}"></i>
                        </div>
                        <div class="activity-content">
                            <div class="small">{{ $activity->getDescription() }}</div>
                            <div class="activity-time">{{ $activity->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                @empty
                    <div class="p-3 text-center text-muted">
                        <small>No recent activity</small>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection