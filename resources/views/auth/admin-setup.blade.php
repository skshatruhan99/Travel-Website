@extends('layouts.app')

@section('title', 'Local Admin Setup - NpAdvanture')

@section('content')
<section class="section bg-light">
    <div class="container" style="max-width:520px">
        <div class="booking-card">
            <span class="badge badge-red">LOCAL DEVELOPMENT ONLY</span>
            <h1 style="margin-top:16px">Set up admin access</h1>
            <p class="muted">Create an admin account or promote an existing account, then continue directly to the dashboard.</p>

            @if($errors->any())
                <div class="badge badge-red" style="margin-top:20px">{{ $errors->first() }}</div>
            @endif

            <form action="{{ route('admin.setup.store') }}" method="POST" style="margin-top:25px">
                @csrf
                <div class="form-group">
                    <label for="name">Name</label>
                    <input id="name" name="name" type="text" class="form-control" value="{{ old('name') }}" required autofocus>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" class="form-control" minlength="8" required>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" minlength="8" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%">Create admin and open dashboard</button>
            </form>
        </div>
    </div>
</section>
@endsection