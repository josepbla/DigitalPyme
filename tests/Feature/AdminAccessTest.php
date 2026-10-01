<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_the_admin_login_page(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_sign_in_to_the_admin_area(): void
    {
        User::factory()->create([
            'email' => 'customer@example.com',
            'password' => 'correct-password',
            'is_admin' => false,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'customer@example.com',
            'password' => 'correct-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_login_is_rate_limited_after_repeated_failures(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login.store'), [
                'email' => 'owner@example.com',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $this->post(route('admin.login.store'), [
            'email' => 'owner@example.com',
            'password' => 'wrong-password',
        ])->assertTooManyRequests();
    }

    public function test_admin_can_sign_in_and_open_the_protected_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => 'correct-password',
            'is_admin' => true,
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'OWNER@example.com',
            'password' => 'correct-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Panel de administración')
            ->assertSee('owner@example.com');
    }

    public function test_regular_authenticated_user_is_forbidden_from_the_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_log_out_and_the_session_is_ended(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->post(route('admin.logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_database_seeder_does_not_create_a_demo_account(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_admin_create_command_creates_an_administrator_with_a_hashed_password(): void
    {
        $this->artisan('admin:create')
            ->expectsQuestion('Nombre del administrador', 'DigitalPyme Owner')
            ->expectsQuestion('Correo del administrador', 'owner@example.com')
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'a-secure-password-123')
            ->expectsQuestion('Confirma la contraseña', 'a-secure-password-123')
            ->expectsOutput('La cuenta administradora se creó correctamente.')
            ->assertExitCode(0);

        $admin = User::query()->where('email', 'owner@example.com')->firstOrFail();

        $this->assertTrue($admin->is_admin);
        $this->assertTrue(password_verify('a-secure-password-123', $admin->password));
    }
}