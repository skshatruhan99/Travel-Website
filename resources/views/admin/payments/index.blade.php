@extends('admin.layout')

@section('title', 'Payments | NpAdvanture Admin')

@section('admin-content')
<section class="admin-page">
    <div class="admin-page-heading"><div><span class="admin-eyebrow">TRANSACTION RECORDS</span><h1>Payments</h1><p>Read-only record of gateway and checkout transactions.</p></div></div>
    <article class="admin-stat" style="max-width:360px;margin-bottom:20px"><span class="admin-stat-icon green"><i class="fa-solid fa-wallet"></i></span><span class="admin-stat-label">Successful payments</span><strong>NPR {{ number_format($totalReceived, 2) }}</strong><small>Confirmed transactions only</small></article>
    <section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table">
        <thead><tr><th>Payment reference</th><th>Booking / tour</th><th>Customer</th><th>Method</th><th>Amount</th><th>Status</th><th>Paid</th></tr></thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td><span class="booking-ref">{{ $payment->payment_reference }}</span><small class="customer-email">{{ $payment->gateway ?: 'Checkout' }}</small></td>
                    <td>{{ $payment->booking?->booking_reference }}<small class="customer-email">{{ $payment->booking?->tour?->title ?? $payment->booking?->items?->first()?->name ?? 'Tour unavailable' }}</small></td>
                    <td>{{ $payment->booking?->user?->name ?? 'Guest' }}</td>
                    <td>{{ ucfirst($payment->method) }}</td>
                    <td>NPR {{ number_format($payment->amount, 2) }}</td>
                    <td><span class="admin-status {{ 'status-' . $payment->status }}">{{ ucfirst($payment->status) }}</span></td>
                    <td>{{ $payment->paid_at?->format('d M Y H:i') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="admin-empty">No payment records found.</td></tr>
            @endforelse
        </tbody>
    </table></div><div class="admin-pagination">{{ $payments->links() }}</div></section>
</section>
@endsection