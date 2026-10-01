<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\DiagnosticRequest;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDiagnosticRequestListTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_list_rejects_guests_and_non_admin_users(): void
    {
        $this->get(route('admin.requests.index'))
            ->assertRedirect(route('login'));

        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->get(route('admin.requests.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_requests_with_their_client_services_and_status(): void
    {
        $this->seed(ServiceSeeder::class);
        $admin = User::factory()->create(['is_admin' => true]);
        $service = Service::query()->where('slug', 'paginas-web')->firstOrFail();
        $this->createRequest('Ana Torres', 'ana@example.com', 'Taller Luna', 'new', $service);

        $this->actingAs($admin)
            ->get(route('admin.requests.index'))
            ->assertOk()
            ->assertSee('Solicitudes de diagnóstico')
            ->assertSee('Ana Torres')
            ->assertSee('ana@example.com')
            ->assertSee('Taller Luna')
            ->assertSee('Páginas web')
            ->assertSee('Nueva');
    }

    public function test_admin_can_filter_requests_by_client_and_status(): void
    {
        $this->seed(ServiceSeeder::class);
        $admin = User::factory()->create(['is_admin' => true]);
        $service = Service::query()->where('slug', 'paginas-web')->firstOrFail();
        $this->createRequest('Ana Torres', 'ana@example.com', 'Taller Luna', 'reviewing', $service);
        $this->createRequest('Luis Pérez', 'luis@example.com', 'Estudio Norte', 'new', $service);

        $this->actingAs($admin)
            ->get(route('admin.requests.index', ['q' => 'Taller Luna', 'status' => 'reviewing']))
            ->assertOk()
            ->assertSee('Ana Torres')
            ->assertSee('En revisión')
            ->assertDontSee('Luis Pérez');
    }

    public function test_admin_request_list_paginates_and_keeps_filters_in_navigation(): void
    {
        $this->seed(ServiceSeeder::class);
        $admin = User::factory()->create(['is_admin' => true]);
        $service = Service::query()->where('slug', 'paginas-web')->firstOrFail();

        for ($number = 1; $number <= 16; $number++) {
            $this->createRequest(
                'Cliente '.$number,
                'cliente'.$number.'@example.com',
                'Empresa '.$number,
                'new',
                $service,
            );
        }

        $this->actingAs($admin)
            ->get(route('admin.requests.index', ['status' => 'new']))
            ->assertOk()
            ->assertSee('16 solicitudes recibidas')
            ->assertSee('Cliente 16')
            ->assertDontSee('Cliente 1</strong>')
            ->assertSee('status=new&amp;page=2', false);

        $this->get(route('admin.requests.index', ['status' => 'new', 'page' => 2]))
            ->assertOk()
            ->assertSee('Cliente 1')
            ->assertSee('status=new');
    }

    private function createRequest(
        string $name,
        string $email,
        string $companyName,
        string $status,
        Service $service,
    ): DiagnosticRequest {
        $client = Client::query()->create([
            'name' => $name,
            'email' => $email,
            'company_name' => $companyName,
            'status' => 'lead',
        ]);

        $request = $client->diagnosticRequests()->create([
            'status' => $status,
            'message' => 'Necesito una propuesta para mi negocio.',
            'privacy_accepted_at' => now(),
        ]);
        $request->services()->attach($service);

        return $request;
    }
}