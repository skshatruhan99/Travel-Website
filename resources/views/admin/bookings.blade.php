@extends('admin.layout')

@section('title', 'Admin Bookings - NpAdvanture')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading"><div><span class="admin-eyebrow">RESERVATIONS</span><h1>Bookings</h1><p>Review bookings and update their status.</p></div></div>
    <section class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                    <thead><tr><th>Reference / tour or stay</th><th>Customer</th><th>Travel / stay date</th><th>Amount</th><th>Status / action</th></tr></thead>
                    <tbody>
                        @forelse($bookings as $booking)
                            <tr>
                                <td><a class="booking-ref" href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->booking_reference }}</a><small class="customer-email">{{ $booking->tour?->title ?? $booking->items->first()?->name ?? 'Tour removed' }}</small></td>
                                <td>{{ $booking->user?->name ?? 'Guest' }}</td>
                                <td>{{ $booking->travel_start_date ? \Illuminate\Support\Carbon::parse($booking->travel_start_date)->format('d M Y') : 'Not set' }}</td>
                                <td class="numeric">NPR {{ number_format($booking->total_amount) }}</td>
                                <td>
                                    <span class="admin-status {{ 'status-' . $booking->status }}">{{ ucfirst($booking->status) }}</span>
                                    @php($nextStatuses = ['draft' => ['pending', 'cancelled'], 'pending' => ['confirmed', 'cancelled'], 'confirmed' => ['completed', 'cancelled']][$booking->status] ?? [])
                                    @if($nextStatuses)
                                        <form action="{{ route('admin.bookings.status', $booking) }}" method="POST" class="admin-status-form">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" aria-label="Next status for {{ $booking->booking_reference }}" class="admin-status-select">@foreach($nextStatuses as $nextStatus)<option value="{{ $nextStatus }}">{{ ucfirst($nextStatus) }}</option>@endforeach</select>
                                            <button class="admin-role-button" type="submit">Update</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No bookings found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="admin-pagination">{{ $bookings->links() }}</div>
    </section>
</section>
@endsection
