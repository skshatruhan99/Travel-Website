@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number)

@section('content')

<section class="page-hero">
    <div class="container">
        <h1>Booking {{ ucfirst($booking->status) }}</h1>
        <p>Your booking and payment details are shown below.</p>
    </div>
</section>


<section class="section">

    <div class="container">

        <div class="invoice">

            <div class="invoice-header">

                <div>
                    <h2>NPADVENTURE</h2>
                    <p class="muted">Travel & Experiences</p>
                </div>

                <div>
                    <strong>Invoice {{ $invoice->invoice_number }}</strong>
                    <p class="muted">Booking #{{ $booking->booking_reference }}</p>
                    <p class="muted">{{ $booking->created_at->format('d M Y') }}</p>
                </div>

            </div>


            <div style="margin-bottom:30px">

                <span class="badge badge-green">
                    <i class="fa-solid fa-check"></i>
                    {{ ucfirst($booking->status) }}
                </span>

                <h2 style="margin-top:15px">
                    {{ $booking->tour?->title ?? $booking->items->first()?->name ?? 'Tour unavailable' }}
                </h2>

                <p class="muted">
                    {{ $booking->tour?->destination?->name ?? ($booking->items->firstWhere('item_type', 'hotel_room')?->room?->hotel?->destination?->name ?? $booking->items->firstWhere('item_type', 'activity')?->activity?->destination?->name ?? 'Destination unavailable') }}
                    @if($booking->travel_start_date)
                        · {{ \Illuminate\Support\Carbon::parse($booking->travel_start_date)->format('d M Y') }}
                    @endif
                    @if($booking->travel_end_date)
                        – {{ \Illuminate\Support\Carbon::parse($booking->travel_end_date)->format('d M Y') }}
                    @endif
                    · {{ $booking->adults + $booking->children }} {{ $booking->items->contains('item_type', 'hotel_room') ? \Illuminate\Support\Str::plural('guest', $booking->adults + $booking->children) : \Illuminate\Support\Str::plural($booking->items->contains('item_type', 'flight') ? 'passenger' : ($booking->items->contains('item_type', 'activity') ? 'participant' : 'traveler'), $booking->adults + $booking->children) }}
                </p>

            </div>


            <div class="table-wrapper">

                <table class="data-table">

                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Amount</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($booking->items as $item)
                            <tr><td>{{ $item->name }}</td><td>{{ $item->quantity }}</td><td>{{ $invoice->currency }} {{ number_format($item->total_price, 2) }}</td></tr>
                        @empty
                            <tr><td>{{ $booking->tour?->title ?? 'Tour unavailable' }}</td><td>{{ $booking->adults + $booking->children }}</td><td>{{ $invoice->currency }} {{ number_format($invoice->subtotal, 2) }}</td></tr>
                        @endforelse

                        @if(($booking->discount ?? 0) > 0)
                            <tr>
                                <td>Coupon Discount</td>
                                <td>1</td>
                                <td>- {{ $invoice->currency }} {{ number_format($invoice->discount, 2) }}</td>
                            </tr>
                        @endif

                        <tr>
                            <td>Taxes & Fees</td>
                            <td>1</td>
                            <td>{{ $invoice->currency }} {{ number_format($invoice->tax, 2) }}</td>
                        </tr>

                    </tbody>

                </table>

            </div>


            <div class="invoice-total">

                <strong>Invoice Total</strong>

                <strong class="price">
                    {{ $invoice->currency }} {{ number_format($invoice->total, 2) }}
                </strong>
            </div>

            <div class="muted" style="margin-top:15px">
                @if($payment)
                    @if($payment->method === 'cash' && $payment->status === 'pending')
                        Payment due in cash on arrival · Reference {{ $payment->payment_reference }}
                    @else
                        {{ $payment->status === 'refunded' ? 'Refunded payment via' : 'Paid via' }}
                        {{ ucfirst($payment->method) }} · Reference {{ $payment->payment_reference }}
                    @endif
                    @if($payment->paid_at) · {{ $payment->paid_at->format('d M Y, g:i A') }}@endif
                @endif
                @foreach($booking->refunds as $refund)
                    <p>Refund {{ ucfirst($refund->status) }}: {{ $refund->currency }} {{ number_format($refund->amount, 2) }}.</p>
                @endforeach
            </div>

            @if($booking->travelers->isNotEmpty())
                <h3 style="margin-top:24px">Traveler details</h3>
                <ul>
                    @foreach($booking->travelers as $traveler)
                        <li>{{ trim($traveler->first_name . ' ' . $traveler->last_name) }} — {{ $traveler->email }}</li>
                    @endforeach
                </ul>
            @endif


            <div style="margin-top:30px">

                <a class="btn btn-primary" href="{{ route('invoice.download', $booking->booking_reference) }}">
                    <i class="fa-solid fa-file-pdf"></i>
                    Download PDF
                </a>
                <button class="btn btn-primary"
                        onclick="window.print()">
                    <i class="fa-solid fa-print"></i>
                    Print Invoice
                </button>

                <a href="{{ route('account') }}"
                   class="btn btn-outline">
                    View My Booking
                </a>

            </div>

        </div>

    </div>

</section>

@endsection