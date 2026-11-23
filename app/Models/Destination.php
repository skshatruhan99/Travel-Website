<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = [
        'name', 'slug', 'country', 'location', 'short_description', 'description',
        'hero_image', 'best_time', 'weather', 'currency', 'languages',
        'latitude', 'longitude', 'is_featured', 'is_active', 'nearby_places',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'nearby_places' => 'array',
        ];
    }

    public function tours()
    {
        return $this->hasMany(Tour::class);
    }
}