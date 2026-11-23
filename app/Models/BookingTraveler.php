<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingTraveler extends Model
{
    protected $fillable = [
        'booking_id', 'first_name', 'last_name', 'email', 'phone',
        'date_of_birth', 'passport_number', 'nationality',
    ];
}
