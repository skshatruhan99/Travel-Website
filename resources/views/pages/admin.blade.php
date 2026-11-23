@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading">
        <div><span class="admin-eyebrow">{{ now()->format('F j, Y') }}</span><h1>Overview</h1><p>Here is what is happening across your travel business.</p></div>
        <a href="{{ route('admin.destinations.create') }}" class="admin-primary-action"><i class="fa-solid fa-plus"></i> Add destination</a>
    </div>
    <div class="admin-stat-grid">
        <article class="admin-stat"><span class="admin-stat-icon coral"><i class="fa-solid fa-suitcase-rolling"></i></span><span class="admin-stat-label">Total bookings</span><strong>{{ number_format($bookingCount) }}</strong><small>All booking requests</small></article>
        <article class="admin-stat"><span class="admin-stat-icon amber"><i class="fa-regular fa-clock"></i></span><span class="admin-stat-label">Awaiting action</span><strong>{{ number_format($pendingBookingCount) }}</strong><small>Pending bookings</small></article>
        <article class="admin-stat"><span class="admin-stat-icon green"><i class="fa-solid fa-users"></i></span><span class="admin-stat-label">Customers</span><strong>{{ number_format($userCount) }}</strong><small>Registered accounts</small></article>
        <article class="admin-stat"><span class="admin-stat-icon blue"><i class="fa-solid fa-route"></i></span><span class="admin-stat-label">Tours</span><strong>{{ number_format($tourCount) }}</strong><small>All tours</small></article>
        <article class="admin-stat"><span class="admin-stat-icon amber"><i class="fa-solid fa-star"></i></span><span class="admin-stat-label">Reviews</span><strong>{{ number_format($reviewCount) }}</strong><small>Customer feedback</small></article>
        <article class="admin-stat"><span class="admin-stat-icon blue"><i class="fa-solid fa-wallet"></i></span><span class="admin-stat-label">Payments received</span><strong>NPR {{ number_format($revenue) }}</strong><small>Successful payments</small></article>
    </div>
    <section class="admin-panel">
        <div class="admin-panel-heading"><div><span class="admin-eyebrow">LATEST ACTIVITY</span><h2>Recent bookings</h2></div><a href="{{ route('admin.bookings') }}" class="admin-text-link">All bookings <i class="fa-solid fa-arrow-right"></i></a></div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Booking</th><th>Customer</th><th>Submitted</th><th>Status</th><th class="numeric">Amount</th></tr></thead>
                <tbody>
                    @forelse($recentBookings as $booking)
                        <tr>
                            <td><span class="booking-ref">{{ $booking->booking_reference }}</span></td>
                            <td><span class="customer-name">{{ $booking->user?->name ?? 'Guest' }}</span><small class="customer-email">{{ $booking->user?->email ?? 'No account linked' }}</small></td>
                            <td>{{ $booking->created_at->format('M j, Y') }}</td>
                            <td><span class="admin-status {{ 'status-' . $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                            <td class="numeric">NPR {{ number_format($booking->total_amount) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="admin-empty">No bookings yet. New bookings will appear here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection