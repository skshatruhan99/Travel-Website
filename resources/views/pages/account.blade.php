@extends('layouts.app')

@section('title', 'My Account')

@section('content')

<section class="dashboard">

    <div class="container">

        <div class="dashboard-layout">

            <aside class="sidebar">

                <a href="{{ route('account') }}" class="active">
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

                <a href="{{ route('profile') }}">
                    <i class="fa-regular fa-user"></i>
                    Profile
                </a>

                <a href="{{ route('wishlist') }}">
                    <i class="fa-regular fa-bookmark"></i>
                    Saved
                </a>

            </aside>


            <div class="dashboard-content">

                <h1>Welcome Back, Traveler!</h1>


                <div class="stats-grid">

                    <div class="stat-card">
                        <i class="fa-solid fa-suitcase"></i>
                        <strong>4</strong>
                        <span>Total Trips</span>
                    </div>

                    <div class="stat-card">
                        <i class="fa-solid fa-calendar-check"></i>
                        <strong>2</strong>
                        <span>Upcoming</span>
                    </div>

                    <div class="stat-card">
                        <i class="fa-solid fa-heart"></i>
                        <strong>8</strong>
                        <span>Saved Places</span>
                    </div>

                    <div class="stat-card">
                        <i class="fa-solid fa-star"></i>
                        <strong>12</strong>
                        <span>Reviews</span>
                    </div>

                </div>


                <div class="booking-card">

                    @if(session('success'))
                        <p class="badge badge-green" style="margin-bottom:20px">{{ session('success') }}</p>
                    @endif

                    <div class="section-heading">

                        <div>
                            <h2>Upcoming Trips</h2>
                        </div>

                        <a href="{{ route('booking') }}"
                           class="text-link">
                            View All
                        </a>

                    </div>


                    <div class="table-wrapper">

                        <table class="data-table">

                            <thead>
                                <tr><th>Trip</th><th>Travel date</th><th>Status</th><th>Amount</th><th>Actions / review</th></tr>
                            </thead>

                            <tbody>
                                @forelse($bookings as $booking)
                                    <tr>
                                        <td>{{ $booking->tour?->title ?? $booking->items->first()?->name ?? $booking->booking_reference }}</td>
                                        <td>{{ $booking->travel_start_date ? \Illuminate\Support\Carbon::parse($booking->travel_start_date)->format('d M Y') : 'Not set' }}</td>
                                        <td>
                                            {{ ucfirst($booking->status) }}
                                            @foreach($booking->refunds as $refund)
                                                <small class="muted">Refund {{ ucfirst($refund->status) }}</small>
                                            @endforeach
                                        </td>
                                        <td>NPR {{ number_format($booking->total_amount) }}</td>
                                        <td>
                                            @if(in_array($booking->status, ['pending', 'confirmed']))
                                                <form action="{{ route('booking.cancel', $booking) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-link" style="border:0;background:none;cursor:pointer">Cancel</button>
                                                </form>
                                            @endif
                                            @if($booking->tour && $booking->status === 'completed' && $booking->reviews->isEmpty())
                                                <form action="{{ route('reviews.store') }}" method="POST" class="account-review-form">
                                                    @csrf
                                                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                                    <label class="sr-only" for="review-rating-{{ $booking->id }}">Rating</label>
                                                    <select id="review-rating-{{ $booking->id }}" name="rating" aria-label="Rating" required><option value="5">5 stars</option><option value="4">4 stars</option><option value="3">3 stars</option><option value="2">2 stars</option><option value="1">1 star</option></select>
                                                    <label class="sr-only" for="review-comment-{{ $booking->id }}">Review comment</label>
                                                    <textarea id="review-comment-{{ $booking->id }}" name="comment" rows="2" maxlength="2000" placeholder="Share your experience"></textarea>
                                                    <button type="submit" class="text-link">Submit review</button>
                                                </form>
                                            @elseif($booking->reviews->isNotEmpty())
                                                <small class="muted">Review {{ ucfirst($booking->reviews->first()->status) }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5">No bookings yet. Start planning your next Nepal journey.</td></tr>
                                @endforelse
                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection