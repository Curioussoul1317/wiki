@extends('layouts.app')

@section('title', 'Tags')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Tags</h1>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTagModal">
        <i class="bi bi-plus-lg me-1"></i> New Tag
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        @forelse($tags as $tag)
            <div class="d-flex align-items-center p-3 border-bottom">
                <span class="badge me-3" style="background-color: {{ $tag->color }}; font-size: 0.9rem;">
                    {{ $tag->name }}
                </span>
                <div class="flex-grow-1">
                    <span class="text-muted">{{ $tag->documents_count }} documents</span>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('tags.show', $tag) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editTag({{ $tag->id }}, '{{ $tag->name }}', '{{ $tag->color }}')">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <form action="{{ route('tags.destroy', $tag) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tag?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state py-5">
                <i class="bi bi-tags empty-state-icon"></i>
                <h5>No tags yet</h5>
                <p class="text-muted">Tags help you organize documents across collections.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTagModal">
                    <i class="bi bi-plus-lg me-1"></i> Create your first tag
                </button>
            </div>
        @endforelse
    </div>
</div>

<!-- Create Tag Modal -->
<div class="modal fade" id="createTagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('tags.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Create Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Tag Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color w-100" id="color" name="color" value="#6366f1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Tag Modal -->
<div class="modal fade" id="editTagModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editTagForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tag</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Tag Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_color" class="form-label">Color</label>
                        <input type="color" class="form-control form-control-color w-100" id="edit_color" name="color">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editTag(id, name, color) {
    document.getElementById('editTagForm').action = '/tags/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_color').value = color;
    new bootstrap.Modal(document.getElementById('editTagModal')).show();
}
</script>
@endpush
@endsection