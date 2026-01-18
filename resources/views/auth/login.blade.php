@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="auth-header">
        <div class="auth-logo">📚</div>
        <h1 class="auth-title">Welcome back</h1>
        <p class="auth-subtitle">Sign in to your account</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf
        
        <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>

        <div class="form-check mb-4">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Sign in</button>
    </form>
 
@endsection