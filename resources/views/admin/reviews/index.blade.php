@extends('admin.layout')

@section('title', 'Reviews | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
	<div class="admin-page-heading"><div><span class="admin-eyebrow">CUSTOMER FEEDBACK</span><h1>Reviews</h1><p>Approve reviews before they appear on tour pages.</p></div></div>
	<section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table">
		<thead><tr><th>Review</th><th>Customer</th><th>Tour / booking</th><th>Status</th><th>Actions</th></tr></thead>
		<tbody>
			@forelse($reviews as $review)
				<tr>
					<td><strong>{{ $review->rating }}/5</strong><small class="customer-email">{{ $review->comment ?: 'No written comment' }}</small></td>
					<td>{{ $review->user?->name ?? 'Deleted user' }}</td>
					<td>{{ $review->booking?->tour?->title ?? 'Tour unavailable' }}<small class="customer-email">{{ $review->booking?->booking_reference }}</small></td>
					<td><span class="admin-status {{ $review->status === 'approved' ? 'status-confirmed' : ($review->status === 'rejected' ? 'status-cancelled' : 'status-pending') }}">{{ ucfirst($review->status) }}</span></td>
					<td class="admin-actions-cell">
						@if($review->status !== 'approved')<form action="{{ route('admin.reviews.status', $review) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="approved"><button class="admin-role-button" type="submit">Approve</button></form>@endif
						@if($review->status !== 'rejected')<form action="{{ route('admin.reviews.status', $review) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected"><button class="admin-role-button" type="submit">Reject</button></form>@endif
						<form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Delete this review?');">@csrf @method('DELETE')<button class="admin-role-button" type="submit">Delete</button></form>
					</td>
				</tr>
			@empty
				<tr><td colspan="5" class="admin-empty">No reviews have been submitted.</td></tr>
			@endforelse
		</tbody>
	</table></div><div class="admin-pagination">{{ $reviews->links() }}</div></section>
</section>
@endsection
