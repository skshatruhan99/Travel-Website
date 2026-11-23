<?php

namespace Tests\Feature;

use App\Jobs\SendBookingEmail;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\BookingTraveler;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_confirmation_email_is_sent_to_the_booking_contact_with_an_invoice_pdf(): void
    {
        Storage::fake('local');
        Mail::fake();

        $user = User::factory()->create();
        $destination = Destination::create([
            'name' => 'Pokhara',
            'slug' => 'email-pokhara',
            'country' => 'Nepal',
        ]);
        $tour = Tour::create([
            'destination_id' => $destination->id,
            'title' => 'Email Trek',
            'slug' => 'email-trek',
            'price' => 300,
            'is_published' => true,
        ]);
        $booking = Booking::create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'booking_reference' => 'NPEMAIL0001',
            'travel_start_date' => '2026-11-10',
            'adults' => 1,
            'children' => 0,
            'subtotal' => 300,
            'tax' => 15,
            'total_amount' => 315,
            'currency' => 'NPR',
            'status' => 'confirmed',
        ]);
        BookingTraveler::create([
            'booking_id' => $booking->id,
            'first_name' => 'Mina',
            'email' => 'mina@example.test',
            'phone' => '+977123456789',
        ]);
        Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'PAYEMAIL0001',
            'gateway' => 'Test gateway',
            'method' => 'wallet',
            'amount' => 315,
            'currency' => 'NPR',
            'status' => 'success',
            'paid_at' => now(),
        ]);

        SendBookingEmail::dispatchSync($booking->id);

        Mail::assertSent(BookingConfirmedMail::class, function (BookingConfirmedMail $mail) use ($booking) {
            return $mail->hasTo('mina@example.test')
                && $mail->booking->booking_reference === $booking->booking_reference
                && count($mail->attachments()) === 1;
        });
    }

    public function test_cash_booking_confirmation_email_explains_payment_is_due_on_arrival(): void
    {
        Storage::fake('local');
        Mail::fake();

        $user = User::factory()->create();
        $destination = Destination::create([
            'name' => 'Pokhara',
            'slug' => 'cash-email-pokhara',
            'country' => 'Nepal',
        ]);
        $tour = Tour::create([
            'destination_id' => $destination->id,
            'title' => 'Cash Email Tour',
            'slug' => 'cash-email-tour',
            'price' => 300,
            'is_published' => true,
        ]);
        $booking = Booking::create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'booking_reference' => 'NPCASHMAIL01',
            'adults' => 1,
            'children' => 0,
            'subtotal' => 300,
            'tax' => 15,
            'total_amount' => 315,
            'currency' => 'NPR',
            'status' => 'confirmed',
        ]);
        Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'PAYCASHMAIL01',
            'gateway' => 'Cash on Delivery',
            'method' => 'cash',
            'amount' => 315,
            'currency' => 'NPR',
            'status' => 'pending',
        ]);

        SendBookingEmail::dispatchSync($booking->id);

        Mail::assertSent(BookingConfirmedMail::class, fn (BookingConfirmedMail $mail) => $mail->hasTo($user->email)
            && str_contains($mail->render(), 'Total due on arrival: NPR 315.00'));
    }
}
