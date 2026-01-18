@extends('layouts.app')

@section('title', $team->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">{{ $team->name }}</h1>
        @if($team->description)
            <p class="text-muted mb-0">{{ $team->description }}</p>
        @endif
    </div>
    @if(auth()->user()->isTeamAdmin($team))
        <a href="{{ route('teams.edit', $team) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit Team
        </a>
    @endif
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Team Members -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-2"></i>Team Members ({{ $team->users->count() }})</span>
                @if(auth()->user()->isTeamAdmin($team))
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#inviteMemberModal">
                        <i class="bi bi-plus me-1"></i> Add Member
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                @foreach($team->users as $member)
                    <div class="d-flex align-items-center p-3 border-bottom">
                        <img src="{{ $member->avatar_url }}" alt="" class="rounded-circle me-3" width="40" height="40">
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $member->name }}</div>
                            <small class="text-muted">{{ $member->email }}</small>
                        </div>
                        <span class="badge bg-{{ $member->pivot->role === 'owner' ? 'primary' : ($member->pivot->role === 'admin' ? 'success' : 'secondary') }} me-2">
                            {{ ucfirst($member->pivot->role) }}
                        </span>
                        @if(auth()->user()->isTeamAdmin($team) && $member->id !== auth()->id() && $member->pivot->role !== 'owner')
                            <div class="dropdown">
                                <button class="btn btn-sm btn-link text-muted" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <form action="{{ route('teams.members.update', [$team, $member]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="role" value="admin">
                                            <button type="submit" class="dropdown-item">Make Admin</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('teams.members.update', [$team, $member]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="role" value="editor">
                                            <button type="submit" class="dropdown-item">Make Editor</button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('teams.members.update', [$team, $member]) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="role" value="viewer">
                                            <button type="submit" class="dropdown-item">Make Viewer</button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('teams.members.remove', [$team, $member]) }}" method="POST" onsubmit="return confirm('Remove this member?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">Remove</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Team Info -->
        <div class="card mb-4">
            <div class="card-header">Team Info</div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt class="text-muted small">Created</dt>
                    <dd>{{ $team->created_at->format('M d, Y') }}</dd>
                    
                    <dt class="text-muted small">Members</dt>
                    <dd>{{ $team->users->count() }}</dd>
                    
                    <dt class="text-muted small">Documents</dt>
                    <dd>{{ $team->documents()->count() }}</dd>
                    
                    <dt class="text-muted small">Collections</dt>
                    <dd>{{ $team->collections()->count() }}</dd>
                </dl>
            </div>
        </div>

        <!-- Danger Zone -->
        @if(auth()->user()->isTeamOwner($team))
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">Danger Zone</div>
                <div class="card-body">
                    <p class="small text-muted">Deleting a team will permanently remove all documents, collections, and data.</p>
                    <form action="{{ route('teams.destroy', $team) }}" method="POST" onsubmit="return confirm('Are you sure? This cannot be undone!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete Team
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-header">Leave Team</div>
                <div class="card-body">
                    <form action="{{ route('teams.leave', $team) }}" method="POST" onsubmit="return confirm('Are you sure you want to leave this team?')">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-box-arrow-right me-1"></i> Leave Team
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Invite Member Modal -->
<!-- Invite Member Modal -->
<div class="modal fade" id="inviteMemberModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('teams.members.invite', $team) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Team Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="John Doe">
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="user@example.com">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="6" placeholder="Min 6 characters">
                        <div class="form-text">Share this password with the user so they can login</div>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role">
                            <option value="viewer">Viewer - Can view documents</option>
                            <option value="editor">Editor - Can create and edit documents</option>
                            <option value="admin">Admin - Can manage team settings</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Member</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection