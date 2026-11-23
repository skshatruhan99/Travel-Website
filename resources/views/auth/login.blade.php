@extends('layouts.app')

@section('title', 'Login - NpAdvanture')

@section('content')
<section class="section bg-light">
    <div class="container" style="max-width:520px">
        <div class="booking-card">
            <h1>Welcome Back</h1>
            <p class="muted">Sign in to manage your trips and bookings.</p>

            @if(session('status'))
                <p class="admin-alert" role="status" style="margin:20px 0 0">{{ session('status') }}</p>
            @endif

            @if($errors->any())
                <div class="badge badge-red" style="margin-top:20px">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('login.store') }}" method="POST" style="margin-top:25px">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" class="form-control" required>
                </div>
                <div class="login-options">
                    <label>
                        <input type="checkbox" name="remember" value="1">
                        Remember me
                    </label>
                    <a class="forgot-password-link" href="{{ route('password.request') }}">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Login</button>
            </form>

            <div style="display:flex;align-items:center;gap:12px;margin:20px 0">
                <span style="height:1px;background:#ddd;flex:1"></span>
                <span class="muted">or</span>
                <span style="height:1px;background:#ddd;flex:1"></span>
            </div>
            <a href="{{ route('google.login') }}" class="google-login-btn">
                <svg class="google-logo" viewBox="0 0 18 18" aria-hidden="true">
                    <path fill="#4285F4" d="M17.64 9.2c0-.63-.06-1.23-.16-1.8H9v3.4h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.92c1.71-1.58 2.68-3.9 2.68-6.58z"/>
                    <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.92-2.26c-.8.54-1.84.86-3.04.86-2.34 0-4.33-1.58-5.04-3.71H.96v2.34A9 9 0 0 0 9 18z"/>
                    <path fill="#FBBC05" d="M3.96 7.29a5.41 5.41 0 0 0 0 3.42l-3 2.34A9 9 0 0 1 0 9c0-1.49.36-2.9.96-4.05l3 2.34z"/>
                    <path fill="#EA4335" d="M9 3.58c1.32 0 2.5.45 3.44 1.35l2.58-2.58A8.65 8.65 0 0 0 9 0 9 9 0 0 0 .96 4.29l3 2.34C4.67 5.16 6.66 3.58 9 3.58z"/>
                </svg>
                Continue with Google
            </a>

            <p style="margin-top:10px">New to NpAdvanture? <a class="register-link" href="{{ route('register') }}">Create an account</a></p>
        </div>
    </div>
</section>
@endsection
