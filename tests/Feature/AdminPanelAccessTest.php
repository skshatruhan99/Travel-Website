<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_each_registered_management_section(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $this->actingAs($admin);

        foreach ([
            'admin.dashboard',
            'admin.users',
            'admin.tours.index',
            'admin.destinations.index',
            'admin.hotels.index',
            'admin.bookings',
            'admin.payments',
            'admin.reviews',
            'admin.coupons.index',
            'admin.journal.index',
            'admin.faqs.index',
            'admin.contact-messages.index',
            'admin.newsletter-subscribers.index',
            'admin.settings',
        ] as $routeName) {
            $this->get(route($routeName))
                ->assertOk();
        }
    }
}
