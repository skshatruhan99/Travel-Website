<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'booking_id',
        'invoice_number',
        'subtotal',
        'discount',
        'tax',
        'total',
        'currency',
        'pdf_path',
    ];

    public static function ensureForBooking(Booking $booking): self
    {
        return static::firstOrCreate(
            ['booking_id' => $booking->id],
            [
                'invoice_number' => 'NP-INV-'.$booking->booking_reference,
                'subtotal' => $booking->subtotal,
                'discount' => $booking->discount,
                'tax' => $booking->tax,
                'total' => $booking->total_amount,
                'currency' => $booking->currency,
            ]
        );
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
