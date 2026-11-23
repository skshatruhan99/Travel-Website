<?php

namespace App\Jobs;

use App\Models\Invoice;
use Dompdf\Dompdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateInvoicePdf implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $invoiceId) {}

    public function handle(): void
    {
        $invoice = Invoice::with([
            'booking.tour.destination',
            'booking.items.room.hotel.destination',
            'booking.items.activity.destination',
            'booking.travelers',
            'booking.payments',
        ])->findOrFail($this->invoiceId);

        $dompdf = new Dompdf([
            'isRemoteEnabled' => false,
        ]);
        $dompdf->loadHtml(view('pdf.invoice', compact('invoice'))->render());
        $dompdf->setPaper('A4');
        $dompdf->render();

        $path = 'invoices/'.$invoice->invoice_number.'.pdf';
        if (! Storage::disk('local')->put($path, $dompdf->output())) {
            throw new RuntimeException('Could not save the generated invoice PDF.');
        }

        $invoice->update(['pdf_path' => $path]);
    }
}
