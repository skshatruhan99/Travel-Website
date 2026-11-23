@extends('admin.layout')

@section('title', 'Tours | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
	<div class="admin-page-heading">
		<div><span class="admin-eyebrow">TRIP INVENTORY</span><h1>Tours</h1><p>Create and publish tours for the public website.</p></div>
		<a href="{{ route('admin.tours.create') }}" class="admin-primary-action"><i class="fa-solid fa-plus"></i> Add tour</a>
	</div>
	<section class="admin-panel">
		<div class="admin-table-wrap">
			<table class="admin-table">
				<thead><tr><th>Tour</th><th>Destination</th><th>Price</th><th>Visibility</th><th>Actions</th></tr></thead>
				<tbody>
					@forelse($tours as $tour)
						<tr>
							<td><span class="customer-name">{{ $tour->title }}</span><small class="customer-email">{{ $tour->duration ?: 'Duration not set' }}</small></td>
							<td>{{ $tour->destination?->name ?? 'Unknown' }}</td>
							<td>NPR {{ number_format($tour->price, 2) }}</td>
							<td><span class="admin-status {{ $tour->is_published ? 'status-confirmed' : '' }}">{{ $tour->is_published ? 'Published' : 'Draft' }}</span></td>
							<td class="admin-actions-cell">
								<a class="admin-role-button" href="{{ route('admin.tours.edit', $tour) }}">Edit</a>
								<a class="admin-role-button" href="{{ route('admin.tours.dates.index', $tour) }}">Dates</a>
								@if($tour->is_published)<a class="admin-role-button" href="{{ route('tour.show', $tour->slug) }}" target="_blank" rel="noopener">View</a>@endif
								<form action="{{ route('admin.tours.destroy', $tour) }}" method="POST" onsubmit="return confirm('Delete this tour? Existing bookings will remain saved.');">
									@csrf
									@method('DELETE')
									<button class="admin-role-button" type="submit">Delete</button>
								</form>
							</td>
						</tr>
					@empty
						<tr><td colspan="5" class="admin-empty">No tours yet. Add a tour to make it available for customers.</td></tr>
					@endforelse
				</tbody>
			</table>
		</div>
		<div class="admin-pagination">{{ $tours->links() }}</div>
	</section>
</section>
@endsection
