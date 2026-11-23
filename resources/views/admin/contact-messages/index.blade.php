@extends('admin.layout')

@section('title', 'Contact Messages | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading">
        <div><span class="admin-eyebrow">CUSTOMER SUPPORT</span><h1>Contact messages</h1><p>Review incoming questions and track their resolution.</p></div>
    </div>
    <section class="admin-panel">
        <form action="{{ route('admin.contact-messages.index') }}" method="GET" class="admin-status-form" style="margin:18px">
            <label for="status-filter">Filter status</label>
            <select id="status-filter" name="status" class="admin-status-select">
                <option value="">All messages</option>
                @foreach(['new' => 'New', 'in_progress' => 'In progress', 'resolved' => 'Resolved'] as $status => $label)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="admin-role-button" type="submit">Filter</button>
        </form>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Message</th><th>Contact</th><th>Category / booking</th><th>Received</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                    @forelse($messages as $message)
                        <tr>
                            <td><a class="booking-ref" href="{{ route('admin.contact-messages.show', $message) }}">{{ \Illuminate\Support\Str::limit($message->message, 90) }}</a></td>
                            <td>{{ $message->name }}<small class="customer-email">{{ $message->email }}{{ $message->phone ? ' · '.$message->phone : '' }}</small></td>
                            <td>{{ $message->category ?: 'General inquiry' }}<small class="customer-email">{{ $message->booking_reference ?: 'No booking reference' }}</small></td>
                            <td>{{ $message->created_at->format('M j, Y g:i A') }}</td>
                            <td><span class="admin-status {{ $message->status === 'resolved' ? 'status-confirmed' : ($message->status === 'in_progress' ? 'status-pending' : '') }}">{{ str_replace('_', ' ', ucfirst($message->status)) }}</span></td>
                            <td><a class="admin-role-button" href="{{ route('admin.contact-messages.show', $message) }}">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="admin-empty">No contact messages found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $messages->links() }}</div>
    </section>
</section>
@endsection
