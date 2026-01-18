@extends('layouts.app')

@section('title', 'New Collection')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-folder-plus me-2"></i>Create New Collection</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('collections.store') }}" method="POST">
                    @csrf

                    @if($parentCollection)
                        <input type="hidden" name="parent_id" value="{{ $parentCollection->id }}">
                        <div class="alert alert-info">
                            <i class="bi bi-diagram-3 me-2"></i>
                            Creating as a sub-collection of: <strong>{{ $parentCollection->icon }} {{ $parentCollection->name }}</strong>
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-3">
                            <label for="icon" class="form-label">Icon</label>
                            <input type="text" class="form-control text-center fs-3" id="icon" name="icon" value="{{ old('icon', '📁') }}" maxlength="10">
                        </div>
                        <div class="col-9">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="color" class="form-label">Color</label>
                            <input type="color" class="form-control form-control-color w-100" id="color" name="color" value="{{ old('color', '#6366f1') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="permission" class="form-label">Visibility</label>
                            <select class="form-select" id="permission" name="permission">
                                <option value="team" {{ old('permission') === 'team' ? 'selected' : '' }}>Team members</option>
                                <option value="public" {{ old('permission') === 'public' ? 'selected' : '' }}>Public</option>
                                <option value="private" {{ old('permission') === 'private' ? 'selected' : '' }}>Private (only me)</option>
                            </select>
                        </div>
                    </div>

                    @unless($parentCollection)
                        <div class="mb-4">
                            <label for="parent_id" class="form-label">Parent Collection</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">None (root level)</option>
                                @foreach($collections as $col)
                                    <option value="{{ $col->id }}" {{ old('parent_id') == $col->id ? 'selected' : '' }}>
                                        {{ $col->icon }} {{ $col->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endunless

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('collections.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Create Collection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection