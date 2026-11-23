@extends('admin.layout')

@section('title', 'Booking ' . $booking->booking_reference . ' | Admin')

@section('admin-content')
<section class="admin-page admin-booking-detail">
	<div class="admin-page-heading">
		<div>
			<a class="admin-text-link" href="{{ route('admin.bookings') }}"><i class="fa-solid fa-arrow-left"></i> All bookings</a>
			<h1>Booking {{ $booking->booking_reference }}</h1>
			<p>Created {{ $booking->created_at->format('M j, Y \\a\\t g:i A') }}</p>
		</div>
		<span class="admin-status {{ 'status-' . $booking->status }}">{{ ucfirst($booking->status) }}</span>
	</div>

	@if(session('success'))
		<div class="admin-form-errors" role="status">{{ session('success') }}</div>
	@endif
	@if($errors->any())
		<div class="admin-form-errors" role="alert">{{ $errors->first() }}</div>
	@endif

	@php($isHotelBooking = $booking->items->contains('item_type', 'hotel_room'))
	@php($isFlightBooking = $booking->items->contains('item_type', 'flight'))
	@php($isActivityBooking = $booking->items->contains('item_type', 'activity'))
	<div class="admin-stat-grid">
		<article class="admin-stat"><span class="admin-stat-label">{{ $isHotelBooking ? 'Stay' : ($isFlightBooking ? 'Flight' : ($isActivityBooking ? 'Activity' : 'Tour')) }}</span><strong>{{ $booking->tour?->title ?? $booking->items->first()?->name ?? 'Tour removed' }}</strong><small>{{ $booking->tour?->destination?->name ?? ($isHotelBooking ? ($booking->items->first()?->room?->hotel?->destination?->name ?? 'Destination unavailable') : ($isFlightBooking ? 'Flight itinerary' : ($isActivityBooking ? ($booking->items->first()?->activity?->destination?->name ?? 'Destination unavailable') : 'Destination unavailable'))) }}</small></article>
		<article class="admin-stat"><span class="admin-stat-label">{{ $isHotelBooking ? 'Stay dates' : ($isFlightBooking ? 'Departure date' : 'Travel dates') }}</span><strong>{{ $booking->travel_start_date ? date('M j, Y', strtotime($booking->travel_start_date)) : 'Not set' }}</strong><small>{{ $booking->travel_end_date ? 'Until ' . date('M j, Y', strtotime($booking->travel_end_date)) : 'End date not set' }}</small></article>
		<article class="admin-stat"><span class="admin-stat-label">{{ $isHotelBooking ? 'Guests / rooms' : ($isFlightBooking ? 'Passengers' : ($isActivityBooking ? 'Participants' : 'Travelers')) }}</span><strong>{{ $booking->adults }} adults, {{ $booking->children }} children</strong><small>{{ $isHotelBooking ? ($booking->items->first()?->quantity ?? 0).' room(s)' : $booking->travelers->count().' passenger / traveler contact(s)' }}</small></article>
		<article class="admin-stat"><span class="admin-stat-label">Total</span><strong>{{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}</strong><small>Subtotal {{ number_format($booking->subtotal, 2) }} · Tax {{ number_format($booking->tax, 2) }} · Discount {{ number_format($booking->discount, 2) }}</small></article>
	</div>

	<div class="admin-panel">
		<div class="admin-panel-heading"><div><span class="admin-eyebrow">CUSTOMER</span><h2>{{ $booking->user?->name ?? 'Guest booking' }}</h2></div></div>
		<div class="admin-panel-body">
		<p>{{ $booking->user?->email ?? 'No account linked' }}</p>
		@if($booking->special_request)
			<h3>Special request</h3>
			<p>{{ $booking->special_request }}</p>
		@endif
		</div>
	</div>

	<div class="admin-panel">
		<div class="admin-panel-heading"><div><span class="admin-eyebrow">TRAVEL PARTY</span><h2>Traveler contacts</h2></div></div>
		<div class="admin-table-wrap">
			<table class="admin-table">
				<thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Nationality</th></tr></thead>
				<tbody>
					@forelse($booking->travelers as $traveler)
						<tr><td>{{ trim($traveler->first_name . ' ' . $traveler->last_name) }}</td><td>{{ $traveler->email ?? 'Not provided' }}</td><td>{{ $traveler->phone ?? 'Not provided' }}</td><td>{{ $traveler->nationality ?? 'Not provided' }}</td></tr>
					@empty
						<tr><td colspan="4" class="admin-empty">No traveler contact details recorded.</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>

	<div class="admin-panel">
		<div class="admin-panel-heading"><div><span class="admin-eyebrow">PAYMENT HISTORY</span><h2>Payments</h2></div></div>
		<div class="admin-table-wrap">
			<table class="admin-table">
				<thead><tr><th>Reference</th><th>Method</th><th>Gateway</th><th>Paid</th><th>Status</th><th class="numeric">Amount</th></tr></thead>
				<tbody>
					@forelse($booking->payments as $payment)
						<tr><td>{{ $payment->payment_reference }}</td><td>{{ ucfirst($payment->method) }}</td><td>{{ $payment->gateway ?? 'Manual' }}</td><td>{{ $payment->paid_at?->format('M j, Y g:i A') ?? 'Not paid' }}</td><td><span class="admin-status {{ 'status-' . $payment->status }}">{{ ucfirst($payment->status) }}</span></td><td class="numeric">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td></tr>
					@empty
						<tr><td colspan="6" class="admin-empty">No payment records for this booking.</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>
	</div>

	@foreach($booking->refunds as $refund)
		<div class="admin-panel">
			<div class="admin-panel-heading"><div><span class="admin-eyebrow">REFUND</span><h2>{{ ucfirst($refund->status) }}</h2></div></div>
			<div class="admin-panel-body">
				<p>{{ $refund->currency }} {{ number_format($refund->amount, 2) }} · Requested {{ $refund->requested_at->format('M j, Y g:i A') }}</p>
				@if($refund->reason)<p>Customer reason: {{ $refund->reason }}</p>@endif
				@if($refund->status === 'requested')
					<p>Process the refund in the payment provider first, then record its reference here.</p>
					<form action="{{ route('admin.bookings.refunds.process', [$booking, $refund]) }}" method="POST" class="admin-status-form">
						@csrf
						@method('PATCH')
						<label for="provider_reference">Provider refund reference</label>
						<input id="provider_reference" name="provider_reference" required maxlength="255">
						<button class="admin-role-button" type="submit">Mark refund processed</button>
					</form>
				@elseif($refund->provider_reference)
					<p>Provider reference: {{ $refund->provider_reference }}</p>
				@endif
			</div>
		</div>
	@endforeach

	@php($nextStatuses = ['draft' => ['pending', 'cancelled'], 'pending' => ['confirmed', 'cancelled'], 'confirmed' => ['completed']][$booking->status] ?? [])
	@if($nextStatuses)
		<div class="admin-panel">
			<div class="admin-panel-heading"><div><span class="admin-eyebrow">BOOKING ACTION</span><h2>Update status</h2></div></div>
			<div class="admin-panel-body">
			<form action="{{ route('admin.bookings.status', $booking) }}" method="POST" class="admin-status-form">
				@csrf
				@method('PATCH')
				<select name="status" aria-label="New booking status" class="admin-status-select">@foreach($nextStatuses as $nextStatus)<option value="{{ $nextStatus }}">{{ ucfirst($nextStatus) }}</option>@endforeach</select>
				<button class="admin-role-button" type="submit">Update status</button>
			</form>
			</div>
		</div>
	@endif
</section>
@endsection
