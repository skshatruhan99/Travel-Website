<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_has_a_working_newsletter_subscription_form(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('newsletter.subscribe'))
            ->assertSee('newsletter-email')
            ->assertSee('Subscribe');
    }

    public function test_visitors_can_subscribe_and_duplicate_subscriptions_are_idempotent(): void
    {
        $this->post(route('newsletter.subscribe'), ['email' => '  TRAVELER@EXAMPLE.TEST '])
            ->assertRedirect()
            ->assertSessionHas('newsletter_success', 'Thanks for subscribing to our newsletter.');

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'traveler@example.test',
            'is_active' => true,
        ]);
        $this->assertDatabaseCount('newsletter_subscribers', 1);

        $this->post(route('newsletter.subscribe'), ['email' => 'traveler@example.test'])
            ->assertRedirect()
            ->assertSessionHas('newsletter_success', 'You are already subscribed to our newsletter.');

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_unsubscribed_email_can_subscribe_again(): void
    {
        $subscriber = NewsletterSubscriber::create([
            'email' => 'returning@example.test',
            'is_active' => false,
        ]);

        $this->post(route('newsletter.subscribe'), ['email' => $subscriber->email])
            ->assertRedirect()
            ->assertSessionHas('newsletter_success', 'Thanks for subscribing to our newsletter.');

        $this->assertDatabaseCount('newsletter_subscribers', 1);
        $this->assertDatabaseHas('newsletter_subscribers', [
            'id' => $subscriber->id,
            'is_active' => true,
        ]);
    }

    public function test_invalid_newsletter_email_is_rejected(): void
    {
        $this->from(route('home'))
            ->post(route('newsletter.subscribe'), ['email' => 'not-an-email'])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_admin_can_list_filter_update_and_delete_newsletter_subscribers(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();
        $active = NewsletterSubscriber::create(['email' => 'active@example.test', 'is_active' => true]);
        NewsletterSubscriber::create(['email' => 'inactive@example.test', 'is_active' => false]);

        $this->actingAs($admin)->get(route('admin.newsletter-subscribers.index'))
            ->assertOk()
            ->assertSee('active@example.test')
            ->assertSee('inactive@example.test');

        $this->get(route('admin.newsletter-subscribers.index', ['status' => 'active']))
            ->assertOk()
            ->assertSee('active@example.test')
            ->assertDontSee('inactive@example.test');

        $this->patch(route('admin.newsletter-subscribers.status', $active), ['is_active' => 0])
            ->assertRedirect();
        $this->assertDatabaseHas('newsletter_subscribers', [
            'id' => $active->id,
            'is_active' => false,
        ]);

        $this->delete(route('admin.newsletter-subscribers.destroy', $active))
            ->assertRedirect();
        $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $active->id]);
    }

    public function test_newsletter_management_is_admin_only(): void
    {
        $subscriber = NewsletterSubscriber::create([
            'email' => 'private@example.test',
            'is_active' => true,
        ]);

        $this->get(route('admin.newsletter-subscribers.index'))->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get(route('admin.newsletter-subscribers.index'))
            ->assertForbidden();

        $this->patch(route('admin.newsletter-subscribers.status', $subscriber), ['is_active' => 0])
            ->assertForbidden();
    }
}
