<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_metrics_and_recent_requests_and_contracts(): void
    {
        $this->seed(ServiceSeeder::class);
        $admin = User::factory()->create(['is_admin' => true]);
        $service = Service::query()->where('slug', 'paginas-web')->firstOrFail();
        $client = Client::query()->create([
            'name' => 'Ana Torres',
            'email' => 'ana@example.com',
            'status' => 'lead',
        ]);

        $diagnosticRequest = $client->diagnosticRequests()->create([
            'status' => 'new',
            'message' => 'Necesito una propuesta.',
            'privacy_accepted_at' => now(),
        ]);
        $diagnosticRequest->services()->attach($service);
        $client->clientServices()->create([
            'service_id' => $service->id,
            'status' => 'completed',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeTextInOrder([
                'Total de solicitudes', '1',
                'Pendientes', '1',
                'Completadas', '1',
                'Clientes registrados', '1',
            ])
            ->assertSee('Ana Torres')
            ->assertSee('ana@example.com')
            ->assertSee('Páginas web')
            ->assertSee('Diagnóstico')
            ->assertSee('Contratación')
            ->assertSee('Pendiente')
            ->assertSee('Completado');
    }

    public function test_admin_can_update_a_diagnostic_request_status_from_the_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $client = Client::query()->create([
            'name' => 'Luis Pérez',
            'email' => 'luis@example.com',
            'status' => 'lead',
        ]);
        $diagnosticRequest = $client->diagnosticRequests()->create([
            'status' => 'new',
            'privacy_accepted_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.activity.status.update', [
                'type' => 'diagnostic',
                'id' => $diagnosticRequest->id,
            ]), ['status' => 'reviewing'])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('status', 'El estado se actualizó correctamente.');

        $this->assertDatabaseHas('diagnostic_requests', [
            'id' => $diagnosticRequest->id,
            'status' => 'reviewing',
        ]);
    }
}