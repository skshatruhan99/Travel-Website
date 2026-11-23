<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class BookingConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking confirmed: '.$this->booking->booking_reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmed',
            with: [
                'invoice' => $this->booking->invoice,
            ],
        );
    }

    public function attachments(): array
    {
        $invoice = $this->booking->invoice;
        if (! $invoice?->pdf_path || ! Storage::disk('local')->exists($invoice->pdf_path)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('local', $invoice->pdf_path)
                ->as($invoice->invoice_number.'.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
