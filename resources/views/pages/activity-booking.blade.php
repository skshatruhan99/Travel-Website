@extends('layouts.app')

@section('title', 'Complete Activity Booking')

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Complete Your Activity Booking</h1>
        <p>Enter the lead participant's details and review your booking.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="detail-grid">
            <div class="booking-card">
                <h2>Lead Participant</h2>
                @if($errors->any())<p class="badge badge-red" role="alert" style="margin:15px 0">{{ $errors->first() }}</p>@endif
                @guest
                    <p class="badge badge-red" style="margin-top:15px">
                        Please <a href="{{ route('login') }}" style="text-decoration:underline">login</a>
                        or <a href="{{ route('register') }}" style="text-decoration:underline">create an account</a>
                        before confirming your booking.
                    </p>
                @endguest
                <form action="{{ route('activities.booking.store', $activity->slug) }}" method="POST">
                    @csrf
                    <input type="hidden" name="activity_date" value="{{ $date->toDateString() }}">
                    <input type="hidden" name="participants" value="{{ $participants }}">
                    <div class="form-grid">
                        <div class="form-group"><label for="first_name">First name</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" class="form-control" required maxlength="100"></div>
                        <div class="form-group"><label for="last_name">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" class="form-control" maxlength="100"></div>
                    </div>
                    <div class="form-group"><label for="email">Contact email</label><input id="email" name="email" type="email" value="{{ old('email', auth()->user()?->email) }}" class="form-control" required maxlength="255"></div>
                    <div class="form-group"><label for="phone">Contact phone</label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="form-control" required maxlength="30"></div>
                    <div class="form-group"><label for="special_request">Special requests</label><textarea id="special_request" name="special_request" class="form-control" rows="3" maxlength="2000">{{ old('special_request') }}</textarea></div>
                    <button class="btn btn-primary" type="submit" style="width:100%">Reserve places and continue to payment <i class="fa-solid fa-arrow-right"></i></button>
                </form>
            </div>
            <aside class="booking-card sticky-card">
                <span class="badge badge-red">Activity Summary</span>
                <h3 style="margin-top:15px">{{ $activity->name }}</h3>
                <p>{{ $activity->destination?->name }} · {{ $date->format('M j, Y') }}</p>
                <div class="tour-meta"><span>{{ $participants }} participant(s)</span>@if($activity->duration)<span>{{ $activity->duration }}</span>@endif</div>
                <hr style="border:0;border-top:1px solid var(--border);margin:20px 0">
                <div class="card-footer"><span>{{ $participants }} × NPR {{ number_format($activity->price, 2) }}</span><strong>NPR {{ number_format($subtotal, 2) }}</strong></div>
                <div class="card-footer"><span>Taxes & fees</span><strong>NPR {{ number_format($tax, 2) }}</strong></div>
                <div class="invoice-total"><strong>Total</strong><strong class="price">NPR {{ number_format($total, 2) }}</strong></div>
            </aside>
        </div>
    </div>
</section>
@endsection
