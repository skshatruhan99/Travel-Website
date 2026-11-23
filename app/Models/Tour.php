<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    protected $fillable = [
        'destination_id', 'title', 'slug', 'short_description', 'description',
        'duration', 'grade', 'min_group_size', 'max_group_size', 'price',
        'child_price', 'transport', 'max_altitude', 'rating', 'review_count',
        'featured_image', 'is_best_seller', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'child_price' => 'decimal:2',
            'is_best_seller' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function reviews()
    {
        return $this->morphMany(Review::class, 'reviewable');
    }

    public function dates()
    {
        return $this->hasMany(TourDate::class);
    }
}
