<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripPlanDay extends Model
{
    protected $fillable = ['trip_plan_id', 'day_number', 'date', 'location', 'description'];

    protected function casts(): array
    {
        return ['date' => 'date'];
    }

    public function tripPlan()
    {
        return $this->belongsTo(TripPlan::class);
    }

    public function items()
    {
        return $this->hasMany(TripPlanItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
