<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_filter_and_view_contact_messages(): void
    {
        $admin = $this->admin();
        $newMessage = $this->message([
            'name' => 'Asha Rai',
            'email' => 'asha@example.test',
            'category' => 'Booking help',
            'booking_reference' => 'NPABC123',
            'message' => 'Please help me update this booking.',
        ]);
        $this->message([
            'name' => 'Kiran Shrestha',
            'status' => 'resolved',
            'message' => 'This message is already resolved.',
        ]);

        $this->actingAs($admin)->get(route('admin.contact-messages.index'))
            ->assertOk()
            ->assertSee('Asha Rai')
            ->assertSee('Kiran Shrestha')
            ->assertSee(route('admin.contact-messages.show', $newMessage));

        $this->get(route('admin.contact-messages.index', ['status' => 'new']))
            ->assertOk()
            ->assertSee('Asha Rai')
            ->assertDontSee('Kiran Shrestha');

        $this->get(route('admin.contact-messages.show', $newMessage))
            ->assertOk()
            ->assertSee('asha@example.test')
            ->assertSee('Booking help')
            ->assertSee('NPABC123')
            ->assertSee('Please help me update this booking.');
    }

    public function test_admin_can_update_contact_message_status(): void
    {
        $admin = $this->admin();
        $message = $this->message();

        $this->actingAs($admin)
            ->patch(route('admin.contact-messages.status', $message), ['status' => 'in_progress'])
            ->assertRedirect();

        $this->assertDatabaseHas('contact_messages', [
            'id' => $message->id,
            'status' => 'in_progress',
        ]);

        $this->patch(route('admin.contact-messages.status', $message), ['status' => 'resolved'])
            ->assertRedirect();
        $this->assertDatabaseHas('contact_messages', [
            'id' => $message->id,
            'status' => 'resolved',
        ]);
    }

    public function test_only_admins_can_access_contact_message_management(): void
    {
        $message = $this->message();
        $user = User::factory()->create();

        $this->get(route('admin.contact-messages.index'))->assertRedirect(route('login'));

        $this->actingAs($user)
            ->get(route('admin.contact-messages.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.contact-messages.show', $message))
            ->assertForbidden();

        $this->actingAs($user)
            ->patch(route('admin.contact-messages.status', $message), ['status' => 'resolved'])
            ->assertForbidden();
    }

    public function test_contact_form_submissions_appear_in_admin_message_list(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Web Visitor',
            'email' => 'visitor@example.test',
            'subject' => 'Question about hotels',
            'message' => 'Can you recommend a hotel in Pokhara?',
        ])->assertRedirect();

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Web Visitor',
            'email' => 'visitor@example.test',
            'category' => 'Question about hotels',
            'status' => 'new',
        ]);

        $this->actingAs($this->admin())
            ->get(route('admin.contact-messages.index'))
            ->assertOk()
            ->assertSee('Web Visitor')
            ->assertSee('Question about hotels');
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    private function message(array $overrides = []): ContactMessage
    {
        return ContactMessage::create(array_merge([
            'name' => 'Test Sender',
            'email' => 'sender@example.test',
            'category' => 'General inquiry',
            'message' => 'A test contact message.',
            'status' => 'new',
        ], $overrides));
    }
}
