@extends('layouts.app')

@section('title', 'My Teams')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">My Teams</h1>
    <a href="{{ route('teams.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> New Team
    </a>
</div>

<div class="row g-4">
    @foreach($teams as $team)
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">{{ $team->name }}</h5>
                    @if($team->description)
                        <p class="card-text text-muted small">{{ Str::limit($team->description, 80) }}</p>
                    @endif
                    <div class="d-flex gap-3 text-muted small mb-3">
                        <span><i class="bi bi-people me-1"></i>{{ $team->users->count() }} members</span>
                    </div>
                    <span class="badge bg-{{ $team->pivot->role === 'owner' ? 'primary' : ($team->pivot->role === 'admin' ? 'success' : 'secondary') }}">
                        {{ ucfirst($team->pivot->role) }}
                    </span>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex gap-2">
                        <form action="{{ route('teams.switch', $team) }}" method="POST" class="flex-grow-1">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary w-100">
                                Switch to Team
                            </button>
                        </form>
                        <a href="{{ route('teams.show', $team) }}" class="btn btn-outline-secondary">
                            <i class="bi bi-gear"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection