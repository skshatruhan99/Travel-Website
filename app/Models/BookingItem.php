<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id', 'item_type', 'item_id', 'name', 'quantity',
        'unit_price', 'total_price',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function room()
    {
        return $this->belongsTo(HotelRoom::class, 'item_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'item_id');
    }
}
