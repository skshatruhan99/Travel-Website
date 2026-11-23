<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id', 'tour_id', 'booking_reference', 'travel_start_date',
        'tour_date_id', 'travel_end_date', 'adults', 'children', 'subtotal',
        'discount', 'tax', 'total_amount', 'currency', 'status',
        'special_request', 'seat_hold_expires_at',
    ];

    protected function casts(): array
    {
        return [
            'seat_hold_expires_at' => 'datetime',
        ];
    }

    public function travelers()
    {
        return $this->hasMany(BookingTraveler::class);
    }

    public function items()
    {
        return $this->hasMany(BookingItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function tourDate()
    {
        return $this->belongsTo(TourDate::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function scopeReservingSeats(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereIn('status', ['confirmed', 'completed'])
                ->orWhere(function (Builder $query) {
                    $query->where('status', 'pending')
                        ->where('seat_hold_expires_at', '>', now());
                });
        });
    }
}
