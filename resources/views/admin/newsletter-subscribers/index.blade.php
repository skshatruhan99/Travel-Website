@extends('admin.layout')

@section('title', 'Newsletter Subscribers | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading">
        <div><span class="admin-eyebrow">AUDIENCE</span><h1>Newsletter subscribers</h1><p>Manage active subscriptions and opt-outs.</p></div>
    </div>
    @if(session('success'))<div class="admin-alert" role="status"><i class="fa-solid fa-circle-check"></i>{{ session('success') }}</div>@endif
    @if($errors->any())<div class="admin-form-errors" role="alert">{{ $errors->first() }}</div>@endif
    <section class="admin-panel">
        <form action="{{ route('admin.newsletter-subscribers.index') }}" method="GET" class="admin-status-form" style="margin:18px">
            <label for="subscriber-status">Subscription status</label>
            <select id="subscriber-status" name="status" class="admin-status-select">
                <option value="">All subscribers</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Unsubscribed</option>
            </select>
            <button class="admin-role-button" type="submit">Filter</button>
        </form>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Email</th><th>Subscribed</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($subscribers as $subscriber)
                        <tr>
                            <td>{{ $subscriber->email }}</td>
                            <td>{{ $subscriber->created_at->format('M j, Y g:i A') }}</td>
                            <td><span class="admin-status {{ $subscriber->is_active ? 'status-confirmed' : 'status-cancelled' }}">{{ $subscriber->is_active ? 'Active' : 'Unsubscribed' }}</span></td>
                            <td class="admin-actions-cell">
                                <form action="{{ route('admin.newsletter-subscribers.status', $subscriber) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $subscriber->is_active ? 0 : 1 }}">
                                    <button class="admin-role-button" type="submit">{{ $subscriber->is_active ? 'Unsubscribe' : 'Reactivate' }}</button>
                                </form>
                                <form action="{{ route('admin.newsletter-subscribers.destroy', $subscriber) }}" method="POST" onsubmit="return confirm('Permanently delete this subscriber?');">
                                    @csrf @method('DELETE')
                                    <button class="admin-role-button" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="admin-empty">No newsletter subscribers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="admin-pagination">{{ $subscribers->links() }}</div>
    </section>
</section>
@endsection
