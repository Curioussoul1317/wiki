@extends('layouts.app')

@section('title', 'Edit: ' . $collection->name)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pencil me-2"></i>Edit Collection</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('collections.update', $collection) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-3">
                            <label for="icon" class="form-label">Icon</label>
                            <input type="text" class="form-control text-center fs-3" id="icon" name="icon" value="{{ old('icon', $collection->icon) }}" maxlength="10">
                        </div>
                        <div class="col-9">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $collection->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $collection->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="color" class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color w-100" id="color" name="color" value="{{ old('color', $collection->color) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="permission" class="form-label">Visibility</label>
                            <select class="form-select" id="permission" name="permission">
                                <option value="team" {{ old('permission', $collection->permission) === 'team' ? 'selected' : '' }}>Team members</option>
                                <option value="public" {{ old('permission', $collection->permission) === 'public' ? 'selected' : '' }}>Public</option>
                                <option value="private" {{ old('permission', $collection->permission) === 'private' ? 'selected' : '' }}>Private (only me)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="parent_id" class="form-label">Parent Collection</label>
                        <select class="form-select" id="parent_id" name="parent_id">
                            <option value="">None (root level)</option>
                            @foreach($collections as $col)
                                <option value="{{ $col->id }}" {{ old('parent_id', $collection->parent_id) == $col->id ? 'selected' : '' }}>
                                    {{ $col->icon }} {{ $col->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('collections.show', $collection) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection