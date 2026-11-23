@extends('admin.layout')

@section('title', 'Hotels | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
	<div class="admin-page-heading"><div><span class="admin-eyebrow">STAY INVENTORY</span><h1>Hotels</h1><p>Manage stays shown on the public website.</p></div><a href="{{ route('admin.hotels.create') }}" class="admin-primary-action"><i class="fa-solid fa-plus"></i> Add hotel</a></div>
	<section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table">
		<thead><tr><th>Hotel</th><th>Destination</th><th>Price / night</th><th>Visibility</th><th>Actions</th></tr></thead>
		<tbody>
			@forelse($hotels as $hotel)
				<tr>
					<td><span class="customer-name">{{ $hotel->name }}</span><small class="customer-email">{{ $hotel->stars ? $hotel->stars . ' stars' : 'Unrated' }}</small></td>
					<td>{{ $hotel->destination?->name ?? 'Unknown' }}</td>
					<td>NPR {{ number_format($hotel->price_per_night, 2) }}</td>
					<td><span class="admin-status {{ $hotel->is_active ? 'status-confirmed' : '' }}">{{ $hotel->is_active ? 'Active' : 'Hidden' }}</span></td>
					<td class="admin-actions-cell"><a class="admin-role-button" href="{{ route('admin.hotels.edit', $hotel) }}">Edit</a><a class="admin-role-button" href="{{ route('admin.hotels.rooms.index', $hotel) }}">Rooms</a>@if($hotel->is_active)<a class="admin-role-button" href="{{ route('hotel.show', $hotel->slug) }}" target="_blank" rel="noopener">View</a>@endif<form action="{{ route('admin.hotels.destroy', $hotel) }}" method="POST" onsubmit="return confirm('Delete this hotel?');">@csrf @method('DELETE')<button class="admin-role-button" type="submit">Delete</button></form></td>
				</tr>
			@empty
				<tr><td colspan="5" class="admin-empty">No hotels yet.</td></tr>
			@endforelse
		</tbody>
	</table></div><div class="admin-pagination">{{ $hotels->links() }}</div></section>
</section>
@endsection
