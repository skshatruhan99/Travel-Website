@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

@php
    $profileUser = auth()->user();
    $avatarUrl = $profileUser->avatar
        ? (filter_var($profileUser->avatar, FILTER_VALIDATE_URL)
            ? $profileUser->avatar
            : '/storage/'.ltrim($profileUser->avatar, '/'))
        : null;
@endphp

<section class="dashboard">

    <div class="container">

        <div class="dashboard-layout">

            <aside class="sidebar">

                <a href="{{ route('account') }}">
                    <i class="fa-solid fa-chart-line"></i>
                    Dashboard
                </a>

                <a href="{{ route('booking') }}">
                    <i class="fa-solid fa-suitcase"></i>
                    My Bookings
                </a>

                <a href="{{ route('planner') }}">
                    <i class="fa-solid fa-map"></i>
                    Trip Planner
                </a>

                <a href="{{ route('profile') }}" class="active">
                    <i class="fa-regular fa-user"></i>
                    Profile
                </a>

            </aside>


            <div class="dashboard-content">

                <h1>Profile & Security</h1>


                <div class="profile-header">

                    <div class="avatar">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}" alt="{{ $profileUser->name }}'s profile photo">
                        @else
                            <span>{{ strtoupper(substr($profileUser->name, 0, 1)) }}</span>
                        @endif
                    </div>

                    <div>

                        <h2>{{ $profileUser->name }}</h2>

                        <p class="muted">
                            {{ $profileUser->email }}
                        </p>

                    </div>

                </div>


                <div class="booking-card">

                    <h2>Personal Information</h2>

                    @if(session('success'))
                        <p class="badge badge-green" style="margin-top:20px">{{ session('success') }}</p>
                    @endif
                    @if($errors->any())
                        <div class="badge badge-red" role="alert" style="margin-top:20px">{{ $errors->first() }}</div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" style="margin-top:25px">
                        @csrf
                        @method('PUT')

                        <div class="form-grid">

                            <div class="form-group">

                                <label>Full Name</label>

                                    <input type="text"
                                        name="name"
                                       class="form-control"
                                        value="{{ old('name', $profileUser->name) }}"
                                        required>

                            </div>


                            <div class="form-group">

                                <label>Email</label>

                                    <input type="email"
                                        name="email"
                                       class="form-control"
                                        value="{{ old('email', $profileUser->email) }}"
                                        required>

                            </div>

                        </div>


                        <div class="form-group">

                            <label for="phone">Phone</label>

                            <input id="phone"
                                   name="phone"
                                   type="tel"
                                   class="form-control"
                                   value="{{ old('phone', $profileUser->phone) }}"
                                   maxlength="30">

                        </div>

                        <div class="form-group">
                            <label for="avatar">Profile photo</label>
                            <input id="avatar"
                                   name="avatar"
                                   type="file"
                                   class="form-control"
                                   accept="image/jpeg,image/png,image/webp">
                            <small class="muted">JPG, PNG, or WebP; maximum 20 MB.</small>
                            @if($profileUser->avatar)
                                <label style="display:block;margin-top:10px">
                                    <input type="checkbox" name="remove_avatar" value="1">
                                    Remove current photo
                                </label>
                            @endif

                        </div>


                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>

                    </form>

                </div>


            </div>

        </div>

    </div>

</section>

@endsection