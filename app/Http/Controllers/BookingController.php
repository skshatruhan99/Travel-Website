<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingTraveler;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Tour;
use App\Models\TourDate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = $request->user()->bookings()->with(['tour', 'items', 'reviews', 'refunds'])->latest()->get();

        return view('pages.account', compact('bookings'));
    }

    public function create(Request $request)
    {
        if (! $request->filled('tour_id')) {
            return redirect()->route('tour')->withErrors(['tour' => 'Choose a tour before booking.']);
        }

        $data = $request->validate([
            'tour_id' => ['required', 'integer', 'exists:tours,id'],
            'tour_date_id' => ['nullable', 'integer', 'exists:tour_dates,id'],
            'travel_start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'adults' => ['required', 'integer', 'min:1', 'max:20'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
        ]);

        $tour = Tour::with('destination')->where('is_published', true)->findOrFail($data['tour_id']);
        $tourDate = $this->resolveTourDate($tour, $data);
        $adults = (int) $data['adults'];
        $children = (int) ($data['children'] ?? 0);
        $travelerCount = $adults + $children;

        if ($travelerCount < $tour->min_group_size || ($tour->max_group_size && $travelerCount > $tour->max_group_size)) {
            return redirect()->route('tour.show', $tour->slug)
                ->withErrors(['travelers' => 'The selected traveler count is outside this tour’s group size.']);
        }

        if ($tourDate) {
            $availableSeats = max(0, $tourDate->capacity - $this->reservedSeats($tourDate));
            if ($travelerCount > $availableSeats) {
                throw ValidationException::withMessages([
                    'travelers' => 'Only '.$availableSeats.' seats are available for this departure.',
                ]);
            }
        }

        $subtotal = ($adults * (float) $tour->price) + ($children * (float) ($tour->child_price ?? $tour->price));
        [$discount, $tax, $total, $coupon] = $this->calculateBookingTotals($subtotal, $request->input('coupon_code'));

        return view('pages.booking', [
            'tour' => $tour,
            'tourDate' => $tourDate,
            'data' => $data,
            'adults' => $adults,
            'children' => $children,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'coupon' => $coupon,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tour_id' => ['required', 'integer', 'exists:tours,id'],
            'tour_date_id' => ['nullable', 'integer', 'exists:tour_dates,id'],
            'travel_start_date' => ['nullable', 'date', 'after_or_equal:today'],
            'adults' => ['required', 'integer', 'min:1', 'max:20'],
            'children' => ['nullable', 'integer', 'min:0', 'max:20'],
            'coupon_code' => ['nullable', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'special_request' => ['nullable', 'string', 'max:2000'],
        ]);

        $tour = Tour::where('is_published', true)->findOrFail($data['tour_id']);
        $adults = (int) $data['adults'];
        $children = (int) ($data['children'] ?? 0);
        $travelerCount = $adults + $children;

        if ($travelerCount < $tour->min_group_size || ($tour->max_group_size && $travelerCount > $tour->max_group_size)) {
            return back()->withErrors(['travelers' => 'The selected traveler count is outside this tour’s group size.'])->withInput();
        }

        $subtotal = ($adults * (float) $tour->price) + ($children * (float) ($tour->child_price ?? $tour->price));

        $booking = DB::transaction(function () use ($request, $data, $tour, $adults, $children, $travelerCount, $subtotal) {
            $tourDate = $this->resolveTourDate($tour, $data, true);
            if ($tourDate) {
                $availableSeats = max(0, $tourDate->capacity - $this->reservedSeats($tourDate));
                if ($travelerCount > $availableSeats) {
                    throw ValidationException::withMessages([
                        'travelers' => 'Only '.$availableSeats.' seats are available for this departure.',
                    ]);
                }
            }

            $discount = 0.0;
            $coupon = null;

            if (filled($request->input('coupon_code'))) {
                $normalizedCode = strtoupper(trim((string) $request->input('coupon_code')));
                $coupon = Coupon::active()->where('code', $normalizedCode)->lockForUpdate()->first();

                if (! $coupon) {
                    throw ValidationException::withMessages(['coupon_code' => 'This coupon code is invalid or expired.']);
                }

                if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'This coupon has reached its usage limit.',
                    ]);
                }

                if (CouponUsage::where('coupon_id', $coupon->id)
                    ->where('user_id', $request->user()->id)
                    ->exists()) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'You have already used this coupon.',
                    ]);
                }

                if (! $coupon->canBeUsedForAmount($subtotal)) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'This coupon is not valid for the current booking amount.',
                    ]);
                }

                $discount = $coupon->calculateDiscount($subtotal);
            }

            $discountedSubtotal = max(0, $subtotal - $discount);
            $tax = round($discountedSubtotal * 0.05, 2);
            $total = round($discountedSubtotal + $tax, 2);

            $booking = Booking::create([
                'user_id' => $request->user()->id,
                'tour_id' => $tour->id,
                'tour_date_id' => $tourDate?->id,
                'booking_reference' => 'NP'.strtoupper(Str::random(8)),
                'travel_start_date' => $tourDate?->departure_date?->toDateString() ?? $data['travel_start_date'],
                'travel_end_date' => $tourDate?->return_date?->toDateString(),
                'adults' => $adults,
                'children' => $children,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total_amount' => $total,
                'currency' => 'NPR',
                'status' => 'pending',
                'seat_hold_expires_at' => now()->addMinutes(20),
                'special_request' => $data['special_request'] ?? null,
            ]);

            BookingTraveler::create([
                'booking_id' => $booking->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]);

            if ($coupon) {
                $coupon->increment('used_count');
                CouponUsage::create([
                    'coupon_id' => $coupon->id,
                    'booking_id' => $booking->id,
                    'user_id' => $request->user()->id,
                    'discount_amount' => $discount,
                ]);
            }

            return $booking;
        });

        return redirect()->route('payment', ['reference' => $booking->booking_reference]);
    }

    public function cancel(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        [$cancelled, $refundRequested] = DB::transaction(function () use ($request, $booking, $data) {
            $successfulPayments = Payment::where('booking_id', $booking->id)
                ->where('status', 'success')
                ->lockForUpdate()
                ->get();
            $lockedBooking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedBooking->user_id === $request->user()->id, 403);
            $successfulPayments = Payment::where('booking_id', $lockedBooking->id)
                ->where('status', 'success')
                ->lockForUpdate()
                ->get();

            if (! in_array($lockedBooking->status, ['pending', 'confirmed'], true)) {
                return [false, false];
            }

            $refundRequested = false;
            if ($lockedBooking->status === 'confirmed' && $successfulPayments->isNotEmpty()) {
                foreach ($successfulPayments as $successfulPayment) {
                    Refund::firstOrCreate(
                        ['payment_id' => $successfulPayment->id],
                        [
                            'booking_id' => $lockedBooking->id,
                            'payment_id' => $successfulPayment->id,
                            'amount' => $successfulPayment->amount,
                            'currency' => $successfulPayment->currency,
                            'status' => 'requested',
                            'reason' => $data['reason'] ?? null,
                            'requested_at' => now(),
                        ]
                    );
                }
                $refundRequested = true;
            }

            $lockedBooking->update([
                'status' => 'cancelled',
                'seat_hold_expires_at' => null,
            ]);

            return [true, $refundRequested];
        });

        if (! $cancelled) {
            return back()->withErrors(['booking' => 'This booking can no longer be cancelled.']);
        }

        $message = $refundRequested
            ? 'Booking cancelled. Your refund request was sent to the team for manual processing.'
            : 'Booking cancelled successfully.';

        return back()->with('success', $message);
    }

    private function calculateBookingTotals(float $subtotal, ?string $couponCode): array
    {
        $coupon = null;
        $discount = 0.0;

        if (filled($couponCode)) {
            $normalizedCode = strtoupper(trim((string) $couponCode));
            $coupon = Coupon::active()->where('code', $normalizedCode)->first();

            if (! $coupon) {
                throw ValidationException::withMessages(['coupon_code' => 'This coupon code is invalid or expired.']);
            }

            if ($userId = auth()->id()) {
                $alreadyUsed = CouponUsage::where('coupon_id', $coupon->id)
                    ->where('user_id', $userId)
                    ->exists();

                if ($alreadyUsed) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'You have already used this coupon.',
                    ]);
                }
            }

            if (! $coupon->canBeUsedForAmount($subtotal)) {
                throw ValidationException::withMessages([
                    'coupon_code' => 'This coupon is not valid for the current booking amount.',
                ]);
            }

            $discount = $coupon->calculateDiscount($subtotal);
        }

        $discountedSubtotal = max(0, $subtotal - $discount);
        $tax = round($discountedSubtotal * 0.05, 2);
        $total = round($discountedSubtotal + $tax, 2);

        return [$discount, $tax, $total, $coupon];
    }

    private function resolveTourDate(Tour $tour, array $data, bool $lockForUpdate = false): ?TourDate
    {
        $tourDateId = $data['tour_date_id'] ?? null;
        $travelStartDate = $data['travel_start_date'] ?? null;

        if (! filled($tourDateId) && ! filled($travelStartDate)) {
            throw ValidationException::withMessages([
                'travel_start_date' => 'Choose a travel date from the calendar.',
            ]);
        }

        $query = TourDate::query()->where('tour_id', $tour->id);

        if (filled($tourDateId)) {
            $query->whereKey($tourDateId);
        } else {
            $query->whereDate('departure_date', $travelStartDate);
        }

        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $tourDate = $query->first();

        if (filled($tourDateId) && ! $tourDate) {
            abort(404);
        }

        if (! $tourDate) {
            return null;
        }

        if (! $tourDate->is_available || $tourDate->departure_date->lt(today())) {
            throw ValidationException::withMessages([
                'travel_start_date' => 'That date is not available for this tour. Please choose another date.',
            ]);
        }

        if (filled($travelStartDate) && $tourDate->departure_date->toDateString() !== $travelStartDate) {
            throw ValidationException::withMessages([
                'travel_start_date' => 'The selected date does not match the scheduled departure.',
            ]);
        }

        return $tourDate;
    }

    private function reservedSeats(TourDate $tourDate): int
    {
        return (int) $tourDate->bookings()->reservingSeats()->sum(DB::raw('adults + children'));
    }
}
