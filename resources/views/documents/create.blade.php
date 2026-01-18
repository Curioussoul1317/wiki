@extends('layouts.app')

@section('title', 'New Document')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-file-plus me-2"></i>Create New Document</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('documents.store') }}" method="POST">
                    @csrf

                    @if($parentDocument)
                        <input type="hidden" name="parent_id" value="{{ $parentDocument->id }}">
                        <div class="alert alert-info">
                            <i class="bi bi-diagram-3 me-2"></i>
                            Creating as a sub-document of: <strong>{{ $parentDocument->title }}</strong>
                        </div>
                    @endif

                    @if($collection)
                        <input type="hidden" name="collection_id" value="{{ $collection->id }}">
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="emoji" class="form-label">Icon</label>
                            <input type="text" class="form-control text-center fs-4" id="emoji" name="emoji" value="{{ old('emoji', '📄') }}" maxlength="10">
                        </div>
                        <div class="col-md-10">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required autofocus placeholder="Enter document title...">
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @unless($collection)
                        <div class="mb-3">
                            <label for="collection_id" class="form-label">Collection</label>
                            <select class="form-select @error('collection_id') is-invalid @enderror" id="collection_id" name="collection_id">
                                <option value="">No collection</option>
                                @foreach($collections as $col)
                                    <option value="{{ $col->id }}" {{ old('collection_id') == $col->id ? 'selected' : '' }}>
                                        {{ $col->icon }} {{ $col->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('collection_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endunless

                    @if($templates->isNotEmpty())
                        <div class="mb-3">
                            <label for="template_id" class="form-label">Start from template</label>
                            <select class="form-select" id="template_id" name="template_id">
                                <option value="">Blank document</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}">{{ $template->emoji }} {{ $template->title }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control @error('content') is-invalid @enderror" id="content" name="content" rows="10" placeholder="Start writing...">{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">You can use Markdown for formatting</div>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" class="form-check-input" id="is_template" name="is_template" value="1" {{ old('is_template') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_template">
                            Save as template
                        </label>
                        <div class="form-text">Templates can be used as starting points for new documents</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('documents.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Create Document
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection