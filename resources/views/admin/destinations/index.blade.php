@extends('admin.layout')

@section('title', 'Destinations | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading"><div><span class="admin-eyebrow">PLACE DIRECTORY</span><h1>Destinations</h1><p>Manage places available for tour listings.</p></div><a href="{{ route('admin.destinations.create') }}" class="admin-primary-action"><i class="fa-solid fa-plus"></i> Add destination</a></div>
    <section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table">
        <thead><tr><th>Destination</th><th>Country</th><th>Visibility</th><th>Actions</th></tr></thead>
        <tbody>
            @forelse($destinations as $destination)
                <tr>
                    <td><span class="customer-name">{{ $destination->name }}</span><small class="customer-email">{{ $destination->slug }}</small></td>
                    <td>{{ $destination->country }}</td>
                    <td><span class="admin-status {{ $destination->is_active ? 'status-confirmed' : '' }}">{{ $destination->is_active ? 'Active' : 'Hidden' }}</span></td>
                    <td class="admin-actions-cell"><a class="admin-role-button" href="{{ route('admin.destinations.edit', $destination) }}">Edit</a><form action="{{ route('admin.destinations.destroy', $destination) }}" method="POST" onsubmit="return confirm('Delete this destination and its tours?');">@csrf @method('DELETE')<button class="admin-role-button" type="submit">Delete</button></form></td>
                </tr>
            @empty
                <tr><td colspan="4" class="admin-empty">No destinations yet. Add a destination before creating tours.</td></tr>
            @endforelse
        </tbody>
    </table></div><div class="admin-pagination">{{ $destinations->links() }}</div></section>
</section>
@endsection