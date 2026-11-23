<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_site_settings(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)
            ->post(route('admin.settings.store'), [
                'site_name' => 'NpAdvanture Travel',
                'support_email' => 'hello@npadvanture.com',
                'support_phone' => '+977-9800000000',
                'address' => 'Kathmandu, Nepal',
                'currency' => 'NPR',
            ])
            ->assertRedirect(route('admin.settings'));

        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'NpAdvanture Travel']);
        $this->assertDatabaseHas('settings', ['key' => 'currency', 'value' => 'NPR']);
    }
}
