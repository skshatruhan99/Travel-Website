<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateInvoicePdf;
use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function show(Request $request): View
    {
        $data = $request->validate([
            'reference' => ['required', 'string'],
        ]);

        $invoice = $this->ownedInvoice($request, $data['reference']);
        $this->ensurePdfExists($invoice);

        return view('pages.invoice', [
            'booking' => $invoice->booking,
            'invoice' => $invoice->fresh(),
            'payment' => $invoice->booking->payments->first(fn ($payment) => $payment->method === 'cash' && $payment->status === 'pending')
                ?? $invoice->booking->payments->firstWhere('status', 'success')
                ?? $invoice->booking->payments->firstWhere('status', 'refunded'),
        ]);
    }

    public function download(Request $request, string $reference): StreamedResponse
    {
        $invoice = $this->ownedInvoice($request, $reference);
        $this->ensurePdfExists($invoice);

        return Storage::disk('local')->download(
            $invoice->pdf_path,
            $invoice->invoice_number.'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    private function ownedInvoice(Request $request, string $reference): Invoice
    {
        $booking = Booking::with(['tour.destination', 'items.room.hotel.destination', 'items.activity.destination', 'travelers', 'payments', 'refunds'])
            ->where('booking_reference', $reference)
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['confirmed', 'completed', 'cancelled', 'refunded'])
            ->where(function ($query) {
                $query->whereHas('payments', fn ($paymentQuery) => $paymentQuery->whereIn('status', ['success', 'refunded']))
                    ->orWhere(function ($bookingQuery) {
                        $bookingQuery->whereIn('status', ['confirmed', 'completed'])
                            ->whereHas('payments', fn ($paymentQuery) => $paymentQuery
                                ->where('method', 'cash')
                                ->where('status', 'pending'));
                    });
            })
            ->firstOrFail();

        return Invoice::ensureForBooking($booking)->setRelation('booking', $booking);
    }

    private function ensurePdfExists(Invoice $invoice): void
    {
        if ($invoice->pdf_path && Storage::disk('local')->exists($invoice->pdf_path)) {
            return;
        }

        GenerateInvoicePdf::dispatchSync($invoice->id);
        $invoice->refresh();
    }
}
