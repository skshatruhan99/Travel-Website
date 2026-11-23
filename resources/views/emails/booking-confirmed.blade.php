<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Booking confirmed</title>
</head>
<body style="color:#1f2937;font-family:Arial,sans-serif;line-height:1.6">
    <h1>Your booking is confirmed</h1>
    @php($cashDue = $booking->payments->where('method', 'cash')->firstWhere('status', 'pending'))
    @if($cashDue)
        <p>Thank you for booking with NpAdventure. Please pay {{ $booking->currency }} {{ number_format($booking->total_amount, 2) }} in cash when your trip begins.</p>
    @else
        <p>Thank you for booking with NpAdventure. Your payment has been verified.</p>
    @endif

    <h2>{{ $booking->tour?->title ?? $booking->items->first()?->name ?? 'Tour' }}</h2>
    <p>
        Booking reference: <strong>{{ $booking->booking_reference }}</strong><br>
        @if($booking->travel_start_date)
            {{ $booking->items->contains('item_type', 'hotel_room') ? 'Check-in' : 'Departure' }}: {{ \Illuminate\Support\Carbon::parse($booking->travel_start_date)->format('d M Y') }}
            @if($booking->travel_end_date)
                {{ $booking->items->contains('item_type', 'hotel_room') ? 'to' : '–' }} {{ \Illuminate\Support\Carbon::parse($booking->travel_end_date)->format('d M Y') }}
            @endif
        @elseif($booking->tour)
            Departure: To be confirmed after booking.
        @endif
        <br>
        {{ $booking->items->contains('item_type', 'hotel_room') ? 'Guests' : ($booking->items->contains('item_type', 'flight') ? 'Passengers' : ($booking->items->contains('item_type', 'activity') ? 'Participants' : 'Travelers')) }}: {{ $booking->adults }} adults, {{ $booking->children }} children<br>
        @if($cashDue)
            Total due on arrival: {{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}
        @else
            Total paid: {{ $booking->currency }} {{ number_format($booking->total_amount, 2) }}
        @endif
    </p>

    @if($invoice)
        <p>Invoice: {{ $invoice->invoice_number }} (attached as a PDF).</p>
    @endif

    <p>We look forward to helping you enjoy your trip.</p>
</body>
</html>
