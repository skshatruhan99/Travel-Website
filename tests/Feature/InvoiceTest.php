<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingTraveler;
use App\Models\Destination;
use App\Models\Payment;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_uses_booking_data_and_generates_a_downloadable_pdf(): void
    {
        Storage::fake('local');
        [$user, $booking] = $this->paidBooking();

        $this->actingAs($user)
            ->get(route('invoice', ['reference' => $booking->booking_reference]))
            ->assertOk()
            ->assertSee('Custom Trek')
            ->assertSee('NPINVOICE01')
            ->assertSee('NPR 525.00')
            ->assertDontSee('Annapurna Adventure Tour')
            ->assertSee(route('invoice.download', $booking->booking_reference));

        $invoice = $booking->invoice()->firstOrFail();
        $this->assertSame('NPR', $invoice->currency);
        $this->assertTrue(Storage::disk('local')->exists($invoice->pdf_path));
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($invoice->pdf_path));

        $this->actingAs($user)
            ->get(route('invoice.download', $booking->booking_reference))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_invoice_cannot_be_viewed_by_another_user(): void
    {
        [$owner, $booking] = $this->paidBooking();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('invoice', ['reference' => $booking->booking_reference]))
            ->assertNotFound();
    }

    public function test_invoice_is_available_when_cash_is_due_on_arrival(): void
    {
        Storage::fake('local');
        [$user, $booking] = $this->paidBooking();
        $booking->payments()->firstOrFail()->update([
            'gateway' => 'Cash on Delivery',
            'method' => 'cash',
            'status' => 'pending',
            'paid_at' => null,
        ]);

        $this->actingAs($user)
            ->get(route('invoice', ['reference' => $booking->booking_reference]))
            ->assertOk()
            ->assertSee('Payment due in cash on arrival');
    }

    private function paidBooking(): array
    {
        $user = User::factory()->create();
        $destination = Destination::create([
            'name' => 'Pokhara',
            'slug' => 'invoice-pokhara',
            'country' => 'Nepal',
        ]);
        $tour = Tour::create([
            'destination_id' => $destination->id,
            'title' => 'Custom Trek',
            'slug' => 'custom-trek',
            'price' => 500,
            'is_published' => true,
        ]);
        $booking = Booking::create([
            'user_id' => $user->id,
            'tour_id' => $tour->id,
            'booking_reference' => 'NPINVOICE01',
            'travel_start_date' => '2026-11-10',
            'travel_end_date' => '2026-11-12',
            'adults' => 1,
            'children' => 1,
            'subtotal' => 500,
            'discount' => 0,
            'tax' => 25,
            'total_amount' => 525,
            'currency' => 'NPR',
            'status' => 'confirmed',
        ]);
        BookingTraveler::create([
            'booking_id' => $booking->id,
            'first_name' => 'Asha',
            'last_name' => 'Rai',
            'email' => 'asha@example.test',
            'phone' => '+977123456789',
        ]);
        Payment::create([
            'booking_id' => $booking->id,
            'payment_reference' => 'PAYINVOICE01',
            'gateway' => 'Test gateway',
            'method' => 'wallet',
            'amount' => 525,
            'currency' => 'NPR',
            'status' => 'success',
            'paid_at' => now(),
        ]);

        return [$user, $booking];
    }
}
