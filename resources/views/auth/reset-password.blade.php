@extends('layouts.app')

@section('title', 'Choose a New Password - NpAdvanture')

@section('content')
<section class="section bg-light">
    <div class="container" style="max-width:520px">
        <div class="booking-card">
            <h1>Choose a New Password</h1>
            <p class="muted">Enter your email and choose a new password.</p>

            @if($errors->any())
                <div class="badge badge-red" style="margin-top:20px">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('password.update') }}" method="POST" style="margin-top:25px">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $email) }}" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <input id="password" name="password" type="password" class="form-control" required minlength="8" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="8" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Reset Password</button>
            </form>
        </div>
    </div>
</section>
@endsection
