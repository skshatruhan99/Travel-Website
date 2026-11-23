<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripPlan extends Model
{
    protected $fillable = [
        'user_id', 'title', 'destination', 'start_date', 'end_date',
        'adults', 'children', 'budget', 'estimated_total', 'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
            'estimated_total' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function days()
    {
        return $this->hasMany(TripPlanDay::class)->orderBy('day_number');
    }

    public function items()
    {
        return $this->hasManyThrough(TripPlanItem::class, TripPlanDay::class);
    }
}
