<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\SocialPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSocialPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_social_management_is_restricted_to_admins(): void
    {
        $this->get(route('admin.social.index'))
            ->assertRedirect(route('login'));

        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.social.index'))
            ->assertForbidden();
    }

    public function test_admin_can_open_social_management_and_dashboard_links_to_it(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.social.index'))
            ->assertOk()
            ->assertSee('Gestión de redes sociales')
            ->assertSee('Preparar publicación')
            ->assertSee('Meta Business Suite')
            ->assertSee('Agenda y publicaciones');

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.social.index'));
    }

    public function test_admin_can_schedule_a_post_with_a_private_image(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $client = $this->createClient();
        $scheduledFor = now()->addDay()->startOfMinute();

        $this->actingAs($admin)
            ->post(route('admin.social.store'), [
                'client_id' => $client->id,
                'platform' => 'instagram',
                'caption' => 'Campaña de primavera para el cliente.',
                'image' => UploadedFile::fake()->image('campana.jpg'),
                'status' => 'scheduled',
                'scheduled_for' => $scheduledFor->format('Y-m-d\TH:i'),
            ])
            ->assertRedirect(route('admin.social.index'))
            ->assertSessionHas('status');

        $post = SocialPost::query()->firstOrFail();
        $this->assertSame($client->id, $post->client_id);
        $this->assertSame($admin->id, $post->created_by);
        $this->assertSame('instagram', $post->platform);
        $this->assertSame('scheduled', $post->status);
        $this->assertTrue(Storage::disk('local')->exists($post->image_path));
        $this->assertFalse(Storage::disk('public')->exists($post->image_path));

        $this->get(route('admin.social.image', $post))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/jpeg');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_can_mark_a_post_published_and_record_metrics(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $client = $this->createClient();
        $post = SocialPost::query()->create([
            'client_id' => $client->id,
            'created_by' => $admin->id,
            'platform' => 'facebook',
            'caption' => 'Publicación de prueba.',
            'status' => 'scheduled',
            'scheduled_for' => now()->addHour(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.social.publication.update', $post), [
                'status' => 'published',
                'published_url' => 'https://www.facebook.com/example/posts/123',
            ])
            ->assertRedirect(route('admin.social.index'));

        $post->refresh();
        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);
        $this->assertSame('https://www.facebook.com/example/posts/123', $post->published_url);

        $this->patch(route('admin.social.metrics.update', $post), [
            'reach' => 350,
            'likes' => 42,
            'comments' => 6,
        ])->assertRedirect(route('admin.social.index'));

        $this->assertDatabaseHas('social_posts', [
            'id' => $post->id,
            'reach' => 350,
            'likes' => 42,
            'comments' => 6,
        ]);
        $this->assertNotNull($post->fresh()->metrics_updated_at);
    }

    public function test_admin_can_record_a_manual_publication_error(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $client = $this->createClient();
        $post = SocialPost::query()->create([
            'client_id' => $client->id,
            'platform' => 'instagram',
            'caption' => 'Publicación con problema.',
            'status' => 'scheduled',
            'scheduled_for' => now()->addHour(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.social.publication.update', $post), [
                'status' => 'failed',
                'error_message' => 'El archivo no cumplía el formato solicitado.',
            ])
            ->assertRedirect(route('admin.social.index'));

        $this->assertDatabaseHas('social_posts', [
            'id' => $post->id,
            'status' => 'failed',
            'error_message' => 'El archivo no cumplía el formato solicitado.',
        ]);
    }

    public function test_scheduled_posts_require_a_future_date(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $client = $this->createClient();

        $this->actingAs($admin)
            ->from(route('admin.social.index'))
            ->post(route('admin.social.store'), [
                'client_id' => $client->id,
                'platform' => 'facebook',
                'caption' => 'Texto de prueba.',
                'status' => 'scheduled',
                'scheduled_for' => now()->subMinute()->format('Y-m-d\TH:i'),
            ])
            ->assertSessionHasErrors('scheduled_for');

        $this->assertDatabaseCount('social_posts', 0);
    }

    private function createClient(): Client
    {
        return Client::query()->create([
            'name' => 'Taller Luna',
            'email' => 'cliente@example.com',
            'status' => 'lead',
        ]);
    }
}