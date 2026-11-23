<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Tour;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('pages.admin', [
            'bookingCount' => Booking::count(),
            'pendingBookingCount' => Booking::where('status', 'pending')->count(),
            'userCount' => User::count(),
            'tourCount' => Tour::count(),
            'reviewCount' => Review::count(),
            'revenue' => Payment::where('status', 'success')->sum('amount'),
            'recentBookings' => Booking::with('user')->latest()->take(8)->get(),
        ]);
    }
}
