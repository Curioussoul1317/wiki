@extends('layouts.app')

@section('title', 'Edit: ' . $document->title)

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
            <li class="breadcrumb-item active">Editing</li>
        </ol>
    </nav>
@endif

<form action="{{ route('documents.update', $document) }}" method="POST" id="documentForm">
    @csrf
    @method('PUT')

    <div class="row">
        <div class="col-lg-9">
            <div class="card">
                <div class="card-body">
                    <!-- Title Row -->
                    <div class="row mb-4">
                        <div class="col-auto">
                            <input type="text" class="form-control text-center fs-3 border-0" id="emoji" name="emoji" value="{{ old('emoji', $document->emoji) }}" style="width: 60px;" maxlength="10">
                        </div>
                        <div class="col">
                            <input type="text" class="form-control form-control-lg border-0 @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $document->title) }}" placeholder="Untitled" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="mb-3">
                        <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="25" placeholder="Start writing your document...">{{ old('content', $document->content) }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-3">
            <!-- Actions -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                        <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-eye me-1"></i> View Document
                        </a>
                    </div>
                </div>
            </div>

            <!-- Settings -->
            <div class="card mb-4">
                <div class="card-header">Settings</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="collection_id" class="form-label">Collection</label>
                        <select class="form-select" id="collection_id" name="collection_id">
                            <option value="">No collection</option>
                            @foreach($collections as $collection)
                                <option value="{{ $collection->id }}" {{ old('collection_id', $document->collection_id) == $collection->id ? 'selected' : '' }}>
                                    {{ $collection->icon }} {{ $collection->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="summary" class="form-label">Summary</label>
                        <textarea class="form-control" id="summary" name="summary" rows="3" placeholder="Brief description...">{{ old('summary', $document->summary) }}</textarea>
                        <div class="form-text">Optional short description</div>
                    </div>
                </div>
            </div>

            <!-- Tags -->
            <div class="card mb-4">
                <div class="card-header">Tags</div>
                <div class="card-body">
                    @foreach($tags as $tag)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="tags[]" value="{{ $tag->id }}" id="tag{{ $tag->id }}"
                                {{ in_array($tag->id, old('tags', $document->tags->pluck('id')->toArray())) ? 'checked' : '' }}>
                            <label class="form-check-label" for="tag{{ $tag->id }}">
                                <span class="badge" style="background: {{ $tag->color }}">{{ $tag->name }}</span>
                            </label>
                        </div>
                    @endforeach
                    @if($tags->isEmpty())
                        <p class="text-muted small mb-0">No tags available. <a href="{{ route('tags.index') }}">Create one</a></p>
                    @endif
                </div>
            </div>

            <!-- Document Info -->
            <div class="card mb-4">
                <div class="card-header">Info</div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Version</span>
                        <span>{{ $document->version }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created</span>
                        <span>{{ $document->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Updated</span>
                        <span>{{ $document->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Words</span>
                        <span id="wordCount">{{ $document->getWordCount() }}</span>
                    </div>
                </div>
            </div>

            <!-- Attachments -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Attachments</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('fileInput').click()">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
                <div class="card-body p-0" id="attachmentsList">
                    @forelse($document->attachments as $attachment)
                        <div class="d-flex align-items-center p-2 border-bottom">
                            <i class="{{ $attachment->getIconClass() }} me-2 text-muted"></i>
                            <span class="small text-truncate flex-grow-1">{{ $attachment->original_filename }}</span>
                            <button type="button" class="btn btn-sm btn-link text-danger" onclick="deleteAttachment({{ $attachment->id }})">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    @empty
                        <div class="p-3 text-center text-muted small" id="noAttachments">
                            No attachments
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Hidden file input -->
<input type="file" id="fileInput" style="display: none" onchange="uploadAttachment(this)">

@push('scripts')
<script>
    // Auto-save draft
    let saveTimeout;
    const form = document.getElementById('documentForm');
    const content = document.getElementById('content');
    const wordCountEl = document.getElementById('wordCount');

    content.addEventListener('input', function() {
        // Update word count
        const words = this.value.trim().split(/\s+/).filter(w => w.length > 0).length;
        wordCountEl.textContent = words;

        // Auto-save after 2 seconds of inactivity
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            // Could implement auto-save here
        }, 2000);
    });

    // Keyboard shortcut to save
    document.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && e.key === 's') {
            e.preventDefault();
            form.submit();
        }
    });

    // Upload attachment
    function uploadAttachment(input) {
        if (!input.files[0]) return;

        const formData = new FormData();
        formData.append('file', input.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route("documents.attachments.store", $document) }}', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to upload file');
            }
        })
        .catch(err => {
            alert('Error uploading file');
        });

        input.value = '';
    }

    // Delete attachment
    function deleteAttachment(id) {
        if (!confirm('Delete this attachment?')) return;

        fetch(`/attachments/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    }
</script>
@endpush

@push('styles')
<style>
    #content {
        font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
        font-size: 14px;
        line-height: 1.6;
        resize: vertical;
    }

    #title {
        font-weight: 700;
    }

    #title:focus, #content:focus, #emoji:focus {
        outline: none;
        box-shadow: none;
    }
</style>
@endpush
@endsection