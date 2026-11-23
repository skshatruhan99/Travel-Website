<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_admin_setup_is_not_available_outside_local_environment(): void
    {
        $this->get(route('admin.setup'))->assertNotFound();
    }

    public function test_local_admin_setup_closes_after_first_admin_exists(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->get(route('admin.setup'))->assertNotFound();
    }

    public function test_local_setup_creates_admin_and_logs_them_in(): void
    {
        $this->app->detectEnvironment(fn () => 'local');

        $this->withSession(['_token' => 'local-setup-token'])
            ->post(route('admin.setup.store'), [
                '_token' => 'local-setup-token',
            'name' => 'Local Admin',
            'email' => 'admin@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'admin@example.test',
            'is_admin' => true,
        ]);
    }

    public function test_regular_user_is_forbidden_from_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_view_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Recent bookings')
            ->assertSee('Payments received');
    }

    public function test_admin_can_promote_a_user_from_the_users_page(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.admin', $user), [
                'is_admin' => 1,
                'password' => 'shared-admin-password',
                'password_confirmation' => 'shared-admin-password',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_admin' => true]);
        $this->assertTrue(Hash::check('shared-admin-password', $user->fresh()->password));
    }

    public function test_promoting_a_user_requires_a_confirmed_password(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $user = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.admin', $user), ['is_admin' => 1])
            ->assertSessionHasErrorsIn('adminRole'.$user->id, 'password');

        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_admin_users_page_prompts_for_a_password_when_promoting_a_user(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        User::factory()->create();

        $this->actingAs($admin)->get(route('admin.users'))
            ->assertOk()
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('Share this password with the new admin securely.');
    }

    public function test_admin_cannot_remove_the_last_admin_or_their_own_access(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)
            ->patch(route('admin.users.admin', $admin), ['is_admin' => 0])
            ->assertSessionHasErrors('admin_role');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_admin' => true]);
    }

    public function test_admin_can_remove_their_own_access_and_log_in_as_a_regular_user(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        User::factory()->create()->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)
            ->patch(route('admin.users.admin', $admin), [
                'is_admin' => 0,
                'current_password' => 'password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');

        $this->assertGuest();
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_admin' => false]);

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));
    }

    public function test_self_demotion_requires_the_correct_password(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        User::factory()->create()->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)
            ->patch(route('admin.users.admin', $admin), [
                'is_admin' => 0,
                'current_password' => 'incorrect',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_admin' => true]);
    }
}