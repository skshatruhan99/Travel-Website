<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_shows_editable_profile_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('profile'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('name="phone"', false)
            ->assertSee('name="avatar"', false)
            ->assertDontSee('Change Password')
            ->assertDontSee('name="current_password"', false);
    }

    public function test_uploaded_photo_url_uses_the_current_site_origin(): void
    {
        Storage::fake('public');
        $avatarPath = $this->fakePng()->store('avatars', 'public');
        $user = User::factory()->create(['avatar' => $avatarPath]);

        $this->actingAs($user)
            ->withServerVariables([
                'HTTP_HOST' => '127.0.0.1:8000',
                'SERVER_NAME' => '127.0.0.1',
                'SERVER_PORT' => 8000,
            ])
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('src="/storage/'.$avatarPath.'"', false);
    }

    public function test_user_can_update_profile_details_and_upload_a_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'avatar' => 'https://example.test/old-avatar.jpg',
        ]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Updated Traveler',
            'email' => 'updated@example.test',
            'phone' => '+977 9800000000',
            'avatar' => $this->fakePng(),
        ])->assertRedirect();

        $user->refresh();

        $this->assertSame('Updated Traveler', $user->name);
        $this->assertSame('updated@example.test', $user->email);
        $this->assertSame('+977 9800000000', $user->phone);
        $this->assertStringStartsWith('avatars/', $user->avatar);
        Storage::disk('public')->assertExists($user->avatar);
    }

    public function test_user_can_remove_an_uploaded_profile_photo(): void
    {
        Storage::fake('public');
        $avatarPath = $this->fakePng()->store('avatars', 'public');
        $user = User::factory()->create(['avatar' => $avatarPath]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'remove_avatar' => '1',
        ])->assertRedirect();

        $this->assertNull($user->fresh()->avatar);
        Storage::disk('public')->assertMissing($avatarPath);
    }

    public function test_profile_photo_must_be_an_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->from(route('profile'))->put(route('profile.update'), [
            'name' => 'Updated Traveler',
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->create('not-an-image.txt', 10, 'text/plain'),
        ])->assertRedirect(route('profile'))
            ->assertSessionHasErrors('avatar');

        $this->assertSame($user->name, $user->fresh()->name);
    }

    public function test_profile_photo_accepts_twenty_megabytes_and_rejects_larger_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $this->fakePng(20 * 1024 * 1024),
        ])->assertSessionHasNoErrors();

        $savedAvatar = $user->fresh()->avatar;
        $this->assertNotNull($savedAvatar);
        Storage::disk('public')->assertExists($savedAvatar);

        $this->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $this->fakePng(20 * 1024 * 1024 + 1),
        ])->assertSessionHasErrors('avatar');
    }

    public function test_user_can_change_password_with_their_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.password'), [
            'current_password' => 'password',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ])->assertRedirect()
            ->assertSessionHas('success', 'Password updated successfully.');

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    private function fakePng(int $sizeInBytes = 0): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jXioAAAAASUVORK5CYII=',
            true
        );

        $this->assertNotFalse($png);

        if ($sizeInBytes > strlen($png)) {
            $png = str_pad($png, $sizeInBytes, "\0");
        }

        return UploadedFile::fake()->createWithContent('profile.png', $png);
    }
}
