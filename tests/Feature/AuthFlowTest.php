<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_includes_a_phone_number_field(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="phone"', false)
            ->assertSee('Phone Number')
            ->assertSee('class="register-login-link"', false);
    }

    public function test_user_can_register_log_out_and_log_back_in_while_invalid_password_is_rejected(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Test Traveler',
            'email' => 'traveler@example.test',
            'phone' => '+977 9800000000',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $user = User::where('email', 'traveler@example.test')->firstOrFail();
        $this->assertSame('+977 9800000000', $user->phone);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();

        $this->from(route('login'))
            ->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'secure-password-123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_client_login_stays_on_the_client_side_even_when_an_admin_page_was_requested(): void
    {
        $user = User::factory()->create();

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('home'));

        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admin_login_redirects_to_the_admin_dashboard(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_guest_account_menu_includes_profile_and_profile_requires_login(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('href="'.route('profile').'"', false)
            ->assertSee('>Profile</a>', false);

        $this->get(route('profile'))->assertRedirect(route('login'));
    }

    public function test_authenticated_account_menu_contains_logout_instead_of_a_direct_logout_button(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee('<details class="account-menu">', false)
            ->assertSee('<summary class="btn btn-outline" aria-label="Account options" title="Account">', false)
            ->assertSee('Account', false)
            ->assertSee('href="'.route('account').'">My Account</a>', false)
            ->assertSee('href="'.route('profile').'">Profile</a>', false)
            ->assertSee('action="'.route('logout').'" method="POST"', false)
            ->assertSee('<button type="submit">Logout</button>', false)
            ->assertDontSee('<button type="submit" class="btn btn-outline">Logout</button>', false);

        $this->assertAuthenticatedAs($user);
    }

    public function test_google_callback_creates_and_logs_in_a_user(): void
    {
        $googleUser = new SocialiteUser();
        $googleUser->map([
            'id' => 'google-test-id',
            'name' => 'Google Traveler',
            'email' => 'google-traveler@example.test',
            'avatar' => 'https://example.test/avatar.png',
        ]);

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturnSelf();
        Socialite::shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        $this->get(route('google.callback'))->assertRedirect(route('home'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google-traveler@example.test',
            'google_id' => 'google-test-id',
        ]);
    }
}
