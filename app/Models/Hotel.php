<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'destination_id', 'name', 'slug', 'short_description', 'description', 'address',
        'stars', 'price_per_night', 'rating', 'review_count', 'featured_image',
        'free_cancellation', 'breakfast_included', 'mountain_view', 'pool', 'spa', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_per_night' => 'decimal:2',
            'free_cancellation' => 'boolean',
            'breakfast_included' => 'boolean',
            'mountain_view' => 'boolean',
            'pool' => 'boolean',
            'spa' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function rooms()
    {
        return $this->hasMany(HotelRoom::class);
    }
}
