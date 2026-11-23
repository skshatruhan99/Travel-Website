@extends('admin.layout')

@section('title', 'Tour dates | Admin')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading">
        <div>
            <a class="admin-text-link" href="{{ route('admin.tours.index') }}">All tours</a>
            <h1>Departure dates</h1>
            <p>{{ $tour->title }} · Create dates and set the seat limit for each departure.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="admin-form-errors" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="admin-form-errors" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="admin-panel admin-form-panel">
        <form action="{{ route('admin.tours.dates.store', $tour) }}" method="POST" class="admin-form">
            @csrf
            <h2>Add departure</h2>
            <div class="admin-form-grid">
                <div class="form-group">
                    <label for="departure_date">Departure date</label>
                    <input id="departure_date" name="departure_date" type="date" class="form-control" min="{{ now()->toDateString() }}" value="{{ old('departure_date') }}" required>
                </div>
                <div class="form-group">
                    <label for="return_date">Return date (optional)</label>
                    <input id="return_date" name="return_date" type="date" class="form-control" value="{{ old('return_date') }}">
                </div>
                <div class="form-group">
                    <label for="capacity">Seat capacity</label>
                    <input id="capacity" name="capacity" type="number" min="1" max="500" class="form-control" value="{{ old('capacity', 10) }}" required>
                </div>
            </div>
            <button class="admin-primary-action" type="submit">Add departure</button>
        </form>
    </section>

    <section class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Departure</th><th>Return</th><th>Seats reserved</th><th>Capacity</th><th>Availability</th><th>Update</th></tr>
                </thead>
                <tbody>
                    @forelse($dates as $date)
                        <tr>
                            <td>{{ $date->departure_date->format('M j, Y') }}</td>
                            <td>{{ $date->return_date?->format('M j, Y') ?? 'Not set' }}</td>
                            <td>{{ $date->reserved_seats }}</td>
                            <td colspan="3">
                                <form action="{{ route('admin.tours.dates.update', [$tour, $date]) }}" method="POST" class="admin-form-grid">
                                    @csrf
                                    @method('PATCH')
                                    <input name="capacity" type="number" min="{{ max(1, $date->reserved_seats) }}" max="500" value="{{ $date->capacity }}" aria-label="Seat capacity">
                                    <input name="return_date" type="date" value="{{ $date->return_date?->toDateString() }}" aria-label="Return date">
                                    <label><input type="checkbox" name="is_available" value="1" @checked($date->is_available)> Available</label>
                                    <button class="admin-role-button" type="submit">Save</button>
                                    <span>{{ $date->available_seats }} seats left</span>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty">No departure dates yet. Add a date to make this tour bookable.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection
