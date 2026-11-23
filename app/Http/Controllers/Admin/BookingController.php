<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index()
    {
        return view('admin.bookings', [
            'bookings' => Booking::with(['user', 'tour', 'items'])->latest()->paginate(20),
        ]);
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'tour.destination', 'items.room.hotel.destination', 'items.activity.destination', 'travelers', 'payments', 'refunds']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,completed,cancelled'],
        ]);

        $transitions = [
            'draft' => ['pending', 'cancelled'],
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed'],
            'completed' => [],
            'cancelled' => [],
            'refunded' => [],
        ];

        if (! in_array($data['status'], $transitions[$booking->status] ?? [], true)) {
            return back()->withErrors(['status' => 'That booking status change is not allowed.']);
        }

        $booking->update(['status' => $data['status']]);

        return back()->with('success', 'Booking status updated.');
    }

    public function processRefund(Request $request, Booking $booking, Refund $refund): RedirectResponse
    {
        $data = $request->validate([
            'provider_reference' => ['required', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($booking, $refund, $data) {
            $refund = Refund::whereKey($refund->id)->where('booking_id', $booking->id)->lockForUpdate()->firstOrFail();
            $payment = Payment::whereKey($refund->payment_id)->lockForUpdate()->firstOrFail();
            $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();

            abort_unless($refund->status === 'requested', 409, 'This refund request has already been processed.');
            abort_unless($payment->status === 'success', 409, 'The payment is not eligible for a refund.');

            $refund->update([
                'status' => 'processed',
                'provider_reference' => $data['provider_reference'],
                'processed_at' => now(),
            ]);
            $payment->update(['status' => 'refunded']);

            if (
                $lockedBooking->status === 'cancelled'
                && ! Payment::where('booking_id', $lockedBooking->id)->where('status', 'success')->exists()
            ) {
                $lockedBooking->update(['status' => 'refunded']);
            }
        });

        return back()->with('success', 'Refund recorded. The booking and payment are marked refunded.');
    }
}
