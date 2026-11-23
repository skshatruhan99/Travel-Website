@extends('layouts.app')

@section('title', 'Complete Hotel Booking - ' . $hotel->name)

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Complete Your Stay Booking</h1>
        <p>Review your room and guest details before continuing to payment.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="detail-grid">
            <div class="booking-card">
                <h2>Guest Information</h2>
                @if($errors->any())<p class="badge badge-red" role="alert" style="margin:15px 0">{{ $errors->first() }}</p>@endif
                @guest
                    <p class="badge badge-red" style="margin-top:15px">
                        Please <a href="{{ route('login') }}" style="text-decoration:underline">login</a>
                        or <a href="{{ route('register') }}" style="text-decoration:underline">create an account</a>
                        before confirming your booking.
                    </p>
                @endguest
                <form action="{{ route('hotel.booking.store', $hotel->slug) }}" method="POST">
                    @csrf
                    <input type="hidden" name="room_id" value="{{ $room->id }}">
                    <input type="hidden" name="check_in" value="{{ $checkIn->toDateString() }}">
                    <input type="hidden" name="check_out" value="{{ $checkOut->toDateString() }}">
                    <input type="hidden" name="adults" value="{{ $adults }}">
                    <input type="hidden" name="children" value="{{ $children }}">
                    <input type="hidden" name="rooms" value="{{ $rooms }}">
                    <div class="form-grid">
                        <div class="form-group"><label for="first_name">First name</label><input id="first_name" name="first_name" value="{{ old('first_name') }}" class="form-control" required maxlength="100"></div>
                        <div class="form-group"><label for="last_name">Last name</label><input id="last_name" name="last_name" value="{{ old('last_name') }}" class="form-control" maxlength="100"></div>
                    </div>
                    <div class="form-group"><label for="email">Email</label><input id="email" name="email" type="email" value="{{ old('email', auth()->user()?->email) }}" class="form-control" required></div>
                    <div class="form-group"><label for="phone">Phone</label><input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="form-control" required maxlength="30"></div>
                    <div class="form-group"><label for="special_request">Special requests</label><textarea id="special_request" name="special_request" class="form-control" rows="3" maxlength="2000">{{ old('special_request') }}</textarea></div>
                    <button class="btn btn-primary" type="submit" style="width:100%">Reserve room and continue to payment <i class="fa-solid fa-arrow-right"></i></button>
                </form>
            </div>
            <aside class="booking-card sticky-card">
                <span class="badge badge-red">Your Stay</span>
                <h3 style="margin-top:15px">{{ $hotel->name }}</h3>
                <p>{{ $room->name }} · {{ $rooms }} room(s)</p>
                <div class="tour-meta">
                    <span>{{ $checkIn->format('M j, Y') }} – {{ $checkOut->format('M j, Y') }} · {{ $nights }} night(s)</span>
                    <span>{{ $adults }} adults, {{ $children }} children</span>
                </div>
                <hr style="border:0;border-top:1px solid var(--border);margin:20px 0">
                <div class="card-footer"><span>{{ $rooms }} room(s) × {{ $nights }} night(s)</span><strong>NPR {{ number_format($subtotal, 2) }}</strong></div>
                <div class="card-footer"><span>Taxes & fees</span><strong>NPR {{ number_format($tax, 2) }}</strong></div>
                <div class="invoice-total"><strong>Total</strong><strong class="price">NPR {{ number_format($total, 2) }}</strong></div>
            </aside>
        </div>
    </div>
</section>
@endsection
