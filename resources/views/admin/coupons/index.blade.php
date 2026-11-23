@extends('admin.layout')

@section('title', 'Coupons | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading">
        <div>
            <span class="admin-eyebrow">PROMOTIONS</span>
            <h1>Coupons</h1>
            <p>Manage discount codes for bookings and promotions.</p>
        </div>
        <a href="{{ route('admin.coupons.create') }}" class="admin-primary-action"><i class="fa-solid fa-plus"></i> Add coupon</a>
    </div>

    <section class="admin-panel">
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Min spend</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        <tr>
                            <td>
                                <span class="customer-name">{{ $coupon->code }}</span>
                                <small class="customer-email">{{ $coupon->description ?: 'No description' }}</small>
                            </td>
                            <td>{{ ucfirst($coupon->type) }}</td>
                            <td>{{ $coupon->type === 'percentage' ? $coupon->value . '%' : 'NPR ' . number_format($coupon->value, 2) }}</td>
                            <td>NPR {{ number_format($coupon->minimum_amount, 2) }}</td>
                            <td>
                                <span class="admin-status {{ $coupon->is_active ? 'status-confirmed' : '' }}">
                                    {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="admin-actions-cell">
                                <a class="admin-role-button" href="{{ route('admin.coupons.edit', $coupon) }}">Edit</a>
                                <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Delete this coupon?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="admin-role-button" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="admin-empty">No coupons have been created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="admin-pagination">{{ $coupons->links() }}</div>
    </section>
</section>
@endsection
