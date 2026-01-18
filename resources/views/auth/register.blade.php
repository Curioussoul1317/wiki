@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <div class="auth-header">
        <div class="auth-logo">📚</div>
        <h1 class="auth-title">Create an account</h1>
        <p class="auth-subtitle">Start organizing your team's knowledge</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        
        <div class="mb-3">
            <label for="name" class="form-label">Full name</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="team_name" class="form-label">Team name</label>
            <input type="text" class="form-control @error('team_name') is-invalid @enderror" id="team_name" name="team_name" value="{{ old('team_name') }}" required placeholder="e.g., My Company">
            @error('team_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <div class="form-text">This will be your first workspace</div>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Create account
        </button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </div>
@endsection