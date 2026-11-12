<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed_with_a_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'photo' => 'photos/avatar.jpg',
        ]);

        Storage::disk('public')->put('photos/avatar.jpg', 'contenu');

        $this->actingAs($user)
            ->get('/profile')
            ->assertOk()
            ->assertSee(Storage::disk('public')->url('photos/avatar.jpg'));
    }

    public function test_user_can_upload_a_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'nom' => $user->nom,
                'courriel' => $user->courriel,
                'photo' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertNotNull($user->photo);
        Storage::disk('public')->assertExists($user->photo);
    }

    public function test_a_new_photo_replaces_the_previous_one(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'photo' => 'photos/ancienne.jpg',
        ]);

        Storage::disk('public')->put('photos/ancienne.jpg', 'contenu');

        $this
            ->actingAs($user)
            ->patch('/profile', [
                'nom' => $user->nom,
                'courriel' => $user->courriel,
                'photo' => UploadedFile::fake()->image('nouvelle.png', 300, 300),
            ])
            ->assertSessionHasNoErrors();

        $user->refresh();

        $this->assertNotSame('photos/ancienne.jpg', $user->photo);
        Storage::disk('public')->assertMissing('photos/ancienne.jpg');
        Storage::disk('public')->assertExists($user->photo);
    }

    public function test_user_can_delete_their_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'photo' => 'photos/avatar.jpg',
        ]);

        Storage::disk('public')->put('photos/avatar.jpg', 'contenu');

        $this
            ->actingAs($user)
            ->patch('/profile', [
                'nom' => $user->nom,
                'courriel' => $user->courriel,
                'supprimer_photo' => '1',
            ])
            ->assertSessionHasNoErrors();

        $this->assertNull($user->refresh()->photo);
        Storage::disk('public')->assertMissing('photos/avatar.jpg');
    }

    public function test_an_invalid_file_is_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->from('/profile')
            ->patch('/profile', [
                'nom' => $user->nom,
                'courriel' => $user->courriel,
                'photo' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('photo')
            ->assertRedirect('/profile');

        $this->assertNull($user->refresh()->photo);
    }

    public function test_deleting_the_account_removes_the_photo_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'photo' => 'photos/avatar.jpg',
        ]);

        Storage::disk('public')->put('photos/avatar.jpg', 'contenu');

        $this
            ->actingAs($user)
            ->delete('/profile', [
                'mot_de_passe' => 'password',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        Storage::disk('public')->assertMissing('photos/avatar.jpg');
    }
}
