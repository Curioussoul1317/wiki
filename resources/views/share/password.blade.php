@extends('layouts.guest')

@section('title', 'Password Required')

@section('content')
    <div class="auth-header">
        <div class="auth-logo">🔒</div>
        <h1 class="auth-title">Password Required</h1>
        <p class="auth-subtitle">This document is password protected</p>
    </div>

    <form method="POST" action="{{ route('share.verify', $shareLink->token) }}">
        @csrf
        
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autofocus>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">
            Access Document
        </button>
    </form>
@endsection