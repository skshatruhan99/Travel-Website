@extends('layouts.app')

@section('title', 'Create Account - NpAdvanture')

@section('content')
<section class="section bg-light">
    <div class="container" style="max-width:520px">
        <div class="booking-card">
            <h1>Create Your Account</h1>
            <p class="muted">Save your trips and manage bookings in one place.</p>

            @if($errors->any())
                <div class="badge badge-red" style="margin-top:20px">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('register.store') }}" method="POST" style="margin-top:25px">
                @csrf
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input id="name" name="name" type="text" class="form-control" value="{{ old('name') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input id="phone" name="phone" type="tel" class="form-control" value="{{ old('phone') }}" maxlength="30" autocomplete="tel" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" class="form-control" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required minlength="8">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Create Account</button>
            </form>

            <p style="margin-top:20px">Already have an account? <a class="register-login-link" href="{{ route('login') }}">Login</a></p>
        </div>
    </div>
</section>
@endsection
