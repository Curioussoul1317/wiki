@extends('layouts.app')

@section('title', 'Share: ' . $document->title)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="mb-4">
            <a href="{{ route('documents.show', $document) }}" class="text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i> Back to document
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-share me-2"></i>Share "{{ $document->title }}"</h5>
            </div>
            <div class="card-body">
                <!-- Create New Share Link -->
                <h6 class="mb-3">Create Share Link</h6>
                <form action="{{ route('documents.share.store', $document) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="permission" class="form-label">Permission</label>
                            <select class="form-select" id="permission" name="permission">
                                <option value="view">View only</option>
                                <option value="edit">Can edit</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="password" class="form-label">Password (optional)</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Leave empty for no password">
                        </div>
                        <div class="col-md-4">
                            <label for="expires_at" class="form-label">Expires (optional)</label>
                            <input type="datetime-local" class="form-control" id="expires_at" name="expires_at">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="bi bi-link-45deg me-1"></i> Create Link
                    </button>
                </form>

                <hr>

                <!-- Existing Share Links -->
                <h6 class="mb-3">Active Share Links</h6>
                @forelse($shareLinks as $link)
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control" value="{{ $link->getUrl() }}" readonly id="link-{{ $link->id }}">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyLink('link-{{ $link->id }}')">
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                    <div class="d-flex gap-3 text-muted small">
                                        <span>
                                            <i class="bi bi-eye me-1"></i>
                                            {{ $link->permission === 'view' ? 'View only' : 'Can edit' }}
                                        </span>
                                        <span>
                                            <i class="bi bi-bar-chart me-1"></i>
                                            {{ $link->view_count }} views
                                        </span>
                                        @if($link->requires_password)
                                            <span>
                                                <i class="bi bi-lock me-1"></i>
                                                Password protected
                                            </span>
                                        @endif
                                        @if($link->expires_at)
                                            <span>
                                                <i class="bi bi-clock me-1"></i>
                                                Expires {{ $link->expires_at->diffForHumans() }}
                                            </span>
                                        @endif
                                        <span>
                                            Created by {{ $link->creator->name }}
                                        </span>
                                    </div>
                                </div>
                                <form action="{{ route('share-links.destroy', $link) }}" method="POST" class="ms-3">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this share link?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No share links yet. Create one above.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyLink(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    document.execCommand('copy');
    alert('Link copied to clipboard!');
}
</script>
@endpush
@endsection