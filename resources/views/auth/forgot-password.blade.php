@extends('layouts.app')

@section('title', 'Reset Password - NpAdvanture')

@section('content')
<section class="section bg-light">
    <div class="container" style="max-width:520px">
        <div class="booking-card">
            <h1>Reset Your Password</h1>
            <p class="muted">Enter your email and we will send reset instructions.</p>

            @if(session('status'))
                <p class="badge badge-green" style="margin-top:20px">{{ session('status') }}</p>
            @endif
            @if($errors->any())
                <div class="badge badge-red" style="margin-top:20px">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('password.email') }}" method="POST" style="margin-top:25px">
                @csrf
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" class="form-control" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Send Reset Link</button>
            </form>
        </div>
    </div>
</section>
@endsection
