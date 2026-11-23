@extends('layouts.app')

@section('title', 'Cash on Delivery - NpAdvanture')

@section('content')
<section class="page-hero">
    <div class="container">
        <h1>Cash on Delivery</h1>
        <p>Confirm your booking now and pay in cash when your trip begins.</p>
    </div>
</section>

<section class="section bg-light">
    <div class="container">
        <div class="detail-grid">
            <div class="booking-card">
                <h2>Cash on Delivery</h2>
                <p class="muted" style="margin-top:8px">Booking #{{ $booking->booking_reference }}</p>

                @if($pendingPayment)
                    <p class="muted" style="margin:20px 0">A previous online payment attempt is still pending. Choosing cash on delivery will close that attempt. Any late successful charge will be flagged for a refund.</p>
                @endif

                <p class="muted" style="margin:20px 0">Pay NPR {{ number_format($booking->total_amount) }} in cash when your trip begins. No online payment is required now.</p>

                <form action="{{ route('payment.store') }}" method="POST" style="margin-top:25px">
                    @csrf
                    <input type="hidden" name="reference" value="{{ $booking->booking_reference }}">
                    <input type="hidden" name="method" value="cash">
                    <button type="submit" class="btn btn-primary" style="width:100%">
                        Confirm booking — Pay in cash on arrival
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </button>
                </form>

                @if($errors->any())
                    <p class="badge badge-red" style="margin-top:15px">{{ $errors->first() }}</p>
                @endif
            </div>

            <div class="booking-card sticky-card">
                <span class="badge badge-red">Booking Summary</span>
                <h3 style="margin-top:15px">{{ $booking->items->first()?->name ?? $booking->tour?->title ?? 'Tour booking' }}</h3>
                <div class="card-footer" style="margin-top:25px">
                    <span>Subtotal</span>
                    <strong>NPR {{ number_format($booking->subtotal) }}</strong>
                </div>
                @if($booking->discount > 0)
                    <div class="card-footer">
                        <span>Coupon Discount</span>
                        <strong>- NPR {{ number_format($booking->discount) }}</strong>
                    </div>
                @endif
                <div class="card-footer">
                    <span>Taxes & Fees</span>
                    <strong>NPR {{ number_format($booking->tax) }}</strong>
                </div>
                <div class="invoice-total">
                    <strong>Total</strong>
                    <strong class="price">NPR {{ number_format($booking->total_amount) }}</strong>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
