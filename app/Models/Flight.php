<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    protected $guarded = [];

    protected $casts = [
        'departure_at' => 'datetime',
        'arrival_at' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function bookingItems()
    {
        return $this->hasMany(BookingItem::class, 'item_id')
            ->where('item_type', 'flight');
    }
}
