<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { color: #1f2937; font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.5; }
        .header { border-bottom: 2px solid #1f2937; margin-bottom: 24px; padding-bottom: 12px; }
        .muted { color: #6b7280; }
        .right { text-align: right; }
        table { border-collapse: collapse; margin-top: 24px; width: 100%; }
        th, td { border-bottom: 1px solid #d1d5db; padding: 10px 8px; text-align: left; }
        th:last-child, td:last-child { text-align: right; }
        .total { font-size: 16px; font-weight: bold; margin-top: 20px; text-align: right; }
        .details { margin-top: 20px; }
    </style>
</head>
<body>
    @php($booking = $invoice->booking)
    <div class="header">
        <strong>NPADVENTURE</strong>
        <span class="right">Invoice {{ $invoice->invoice_number }}</span>
        <div class="muted">Booking {{ $booking->booking_reference }} · {{ $booking->created_at->format('d M Y') }}</div>
    </div>

    <h1>{{ $booking->tour?->title ?? $booking->items->first()?->name ?? 'Tour unavailable' }}</h1>
    <p class="muted">
        {{ $booking->tour?->destination?->name ?? ($booking->items->firstWhere('item_type', 'hotel_room')?->room?->hotel?->destination?->name ?? $booking->items->firstWhere('item_type', 'activity')?->activity?->destination?->name ?? 'Destination unavailable') }}
        @if($booking->travel_start_date) · {{ \Illuminate\Support\Carbon::parse($booking->travel_start_date)->format('d M Y') }}@endif
        @if($booking->travel_end_date) – {{ \Illuminate\Support\Carbon::parse($booking->travel_end_date)->format('d M Y') }}@endif
        · {{ $booking->adults + $booking->children }} {{ $booking->items->contains('item_type', 'hotel_room') ? \Illuminate\Support\Str::plural('guest', $booking->adults + $booking->children) : \Illuminate\Support\Str::plural($booking->items->contains('item_type', 'flight') ? 'passenger' : ($booking->items->contains('item_type', 'activity') ? 'participant' : 'traveler'), $booking->adults + $booking->children) }}
    </p>

    <div class="details">
        @foreach($booking->travelers as $traveler)
            <div>{{ trim($traveler->first_name . ' ' . $traveler->last_name) }} · {{ $traveler->email }} · {{ $traveler->phone }}</div>
        @endforeach
    </div>

    <table>
        <thead><tr><th>Description</th><th>Qty</th><th>Amount</th></tr></thead>
        <tbody>
            @forelse($booking->items as $item)
                <tr><td>{{ $item->name }}</td><td>{{ $item->quantity }}</td><td>{{ $invoice->currency }} {{ number_format($item->total_price, 2) }}</td></tr>
            @empty
                <tr><td>{{ $booking->tour?->title ?? 'Tour' }}</td><td>{{ $booking->adults + $booking->children }}</td><td>{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td></tr>
            @endforelse
            @if($invoice->discount > 0)
                <tr><td>Coupon discount</td><td>1</td><td>- {{ $invoice->currency }} {{ number_format($invoice->discount, 2) }}</td></tr>
            @endif
            <tr><td>Taxes and fees</td><td>1</td><td>{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td></tr>
        </tbody>
    </table>

    <p class="total">Invoice total: {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}</p>
    @if($payment = $booking->payments->where('method', 'cash')->firstWhere('status', 'pending'))
        <p class="right muted">Payment due in cash on arrival · {{ $payment->payment_reference }}</p>
    @elseif($payment = $booking->payments->firstWhere('status', 'success'))
        <p class="right muted">Paid via {{ ucfirst($payment->method) }} · {{ $payment->payment_reference }}</p>
    @elseif($payment = $booking->payments->firstWhere('status', 'refunded'))
        <p class="right muted">Refunded via {{ ucfirst($payment->method) }} · {{ $payment->payment_reference }}</p>
    @endif
</body>
</html>
