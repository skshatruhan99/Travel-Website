<?php

namespace Tests\Feature;

use App\Models\JournalPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_edit_and_delete_an_article_visible_on_the_public_site(): void
    {
        $admin = User::factory()->create();
        $admin->forceFill(['is_admin' => true])->save();

        $this->actingAs($admin)
            ->post(route('admin.journal.store'), [
                'title' => 'A Journey Through Nepal',
                'excerpt' => 'A short travel story.',
                'content' => 'The mountains were bright at sunrise.',
                'category' => 'Travel',
                'is_published' => 1,
            ])
            ->assertRedirect(route('admin.journal.index'));

        $article = JournalPost::where('slug', 'a-journey-through-nepal')->firstOrFail();
        $this->assertDatabaseHas('journal_posts', [
            'id' => $article->id,
            'user_id' => $admin->id,
            'is_published' => true,
        ]);

        $this->get(route('journal'))
            ->assertOk()
            ->assertSee('A Journey Through Nepal');
        $this->get(route('journal.article', $article->slug))
            ->assertOk()
            ->assertSee('The mountains were bright at sunrise.');

        $this->patch(route('admin.journal.update', $article), [
            'title' => 'Sunrise in the Himalayas',
            'excerpt' => 'An updated travel story.',
            'content' => 'The Himalayas glowed at dawn.',
            'category' => 'Nepal',
            'is_published' => 1,
        ])->assertRedirect(route('admin.journal.index'));

        $article->refresh();
        $this->assertSame('sunrise-in-the-himalayas', $article->slug);
        $this->get(route('journal.article', $article->slug))
            ->assertOk()
            ->assertSee('The Himalayas glowed at dawn.');

        $this->delete(route('admin.journal.destroy', $article))
            ->assertRedirect(route('admin.journal.index'));

        $this->assertDatabaseMissing('journal_posts', ['id' => $article->id]);
        $this->get(route('journal.article', $article->slug))->assertNotFound();
    }
}
