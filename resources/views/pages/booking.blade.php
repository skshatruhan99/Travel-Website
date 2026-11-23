@extends('layouts.app')

@section('title', 'Booking Checkout')

@section('content')

<section class="page-hero">
    <div class="container">
        <h1>Complete Your Booking</h1>
        <p>Enter your information and confirm your trip.</p>
    </div>
</section>


<section class="section">

    <div class="container">

        <div class="detail-grid">

            <div class="booking-card">

                <h2>Traveler Information</h2>

                @if($errors->any())
                    <div class="badge badge-red" style="margin:15px 0">{{ $errors->first() }}</div>
                @endif

                @guest
                    <p class="badge badge-red" style="margin-top:15px">
                        Please <a href="{{ route('login') }}" style="text-decoration:underline">login</a>
                        or <a href="{{ route('register') }}" style="text-decoration:underline">create an account</a>
                        before confirming your booking.
                    </p>
                @endguest

                <form action="{{ route('booking.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tour_id" value="{{ $tour->id }}">
                    <input type="hidden" name="tour_date_id" value="{{ $tourDate?->id }}">
                    <input type="hidden" name="travel_start_date" value="{{ $tourDate ? $tourDate->departure_date->toDateString() : ($data['travel_start_date'] ?? '') }}">
                    <input type="hidden" name="adults" value="{{ $adults }}">
                    <input type="hidden" name="children" value="{{ $children }}">

                    <div class="form-grid">

                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text"
                                id="first_name"
                                name="first_name"
                                   class="form-control"
                                   placeholder="First name"
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text"
                                id="last_name"
                                name="last_name"
                                   class="form-control"
                                   placeholder="Last name"
                                   required>
                        </div>

                    </div>


                    <div class="form-group">
                           <label for="email">Email</label>
                        <input type="email"
                               id="email"
                               name="email"
                               class="form-control"
                               placeholder="you@example.com"
                               required>
                    </div>


                    <div class="form-group">
                           <label for="phone">Phone</label>
                        <input type="tel"
                               id="phone"
                               name="phone"
                               class="form-control"
                               placeholder="+91 9876543210"
                               required>
                    </div>


                    <div class="form-group">
                        <label for="coupon_code">Coupon Code</label>
                        <input type="text"
                               id="coupon_code"
                               name="coupon_code"
                               class="form-control"
                               value="{{ old('coupon_code') }}"
                               placeholder="SAVE10">
                        @error('coupon_code')
                            <p class="badge badge-red" role="alert" style="margin-top:8px">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Special Requests</label>

                        <textarea id="special_request"
                                  name="special_request"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Any special requirements?"></textarea>
                    </div>


                    <button class="btn btn-primary"
                            type="submit"
                            style="width:100%">
                        Continue to Payment
                        <i class="fa-solid fa-arrow-right"></i>
                    </button>

                </form>

            </div>


            <div>

                <div class="booking-card sticky-card">

                    <span class="badge badge-red">
                        Your Trip
                    </span>

                        <h3 style="margin-top:15px">
                        {{ $tour->title }}
                    </h3>

                    <div class="tour-meta">
                        <span>
                            <i class="fa-regular fa-calendar"></i>
                            @if($tourDate)
                                {{ $tourDate->departure_date->format('M j, Y') }}
                                @if($tourDate->return_date) – {{ $tourDate->return_date->format('M j, Y') }}@endif
                            @else
                                {{ \Illuminate\Support\Carbon::parse($data['travel_start_date'])->format('M j, Y') }}
                            @endif
                        </span>
                    </div>
                    @if(!$tourDate)
                        <p class="muted" style="margin-top:10px">Your selected date is a request; availability will be confirmed after booking.</p>
                    @endif

                    <hr style="border:0;border-top:1px solid var(--border);margin:20px 0">

                    <div class="card-footer">
                        <span>Tour Price</span>
                        <strong>NPR {{ number_format($tour->price, 2) }}</strong>
                    </div>

                    <div class="card-footer">
                        <span>Travelers</span>
                        <strong>{{ $adults }} adults, {{ $children }} children</strong>
                    </div>

                    @if(($discount ?? 0) > 0)
                        <div class="card-footer">
                            <span>Coupon Discount</span>
                            <strong>- NPR {{ number_format($discount, 2) }}</strong>
                        </div>
                    @endif

                    <div class="card-footer">
                        <span>Taxes</span>
                        <strong>NPR {{ number_format($tax, 2) }}</strong>
                    </div>

                    <div class="invoice-total">
                        <strong>Total</strong>
                        <strong class="price">NPR {{ number_format($total, 2) }}</strong>
                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection