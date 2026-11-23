<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelRoom extends Model
{
    protected $fillable = [
        'hotel_id', 'name', 'description', 'price_per_night',
        'max_guests', 'rooms_available', 'includes_breakfast', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
            'includes_breakfast' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookingItems()
    {
        return $this->hasMany(BookingItem::class, 'item_id')
            ->where('item_type', 'hotel_room');
    }
}
