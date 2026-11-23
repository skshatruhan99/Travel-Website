<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripPlanItem extends Model
{
    protected $fillable = [
        'trip_plan_day_id', 'item_type', 'item_id', 'name', 'price',
        'description', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    public function day()
    {
        return $this->belongsTo(TripPlanDay::class, 'trip_plan_day_id');
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class, 'item_id');
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class, 'item_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'item_id');
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'item_id');
    }
}
