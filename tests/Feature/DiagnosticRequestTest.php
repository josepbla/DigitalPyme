<?php

namespace Tests\Feature;

use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnosticRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_explains_the_diagnostic_form_data_use(): void
    {
        $this->get(route('privacy-policy'))
            ->assertOk()
            ->assertSee('Política de Privacidad')
            ->assertSee('tu nombre, correo electrónico, al menos un servicio de interés')
            ->assertSee('Almacenamiento y conservación')
            ->assertSee('Actualmente no hay un borrado automático configurado')
            ->assertSee('Cookies y pagos')
            ->assertSee('Tus solicitudes sobre datos')
            ->assertSee(route('diagnostics.create'));
    }

    public function test_homepage_displays_navigation_and_its_section_destinations(): void
    {
        $this->seed(ServiceSeeder::class);
        $websiteService = \App\Models\Service::query()
            ->where('slug', 'paginas-web')
            ->firstOrFail();
        $shopService = \App\Models\Service::query()
            ->where('slug', 'tiendas-online')
            ->firstOrFail();
        $automationService = \App\Models\Service::query()
            ->where('slug', 'automatizacion-de-procesos')
            ->firstOrFail();

        $this->get('/')
            ->assertOk()
            ->assertSee('aria-label="Navegación principal"', false)
            ->assertSee('Soluciones digitales a la medida de tu negocio.')
            ->assertSee('Creamos páginas web, tiendas online y automatizamos tus procesos para que vendas más.')
            ->assertSee('Lo que hacemos por tu empresa')
            ->assertSee('Páginas Web')
            ->assertSee('Tiendas Online')
            ->assertSee('Automatización de Procesos')
            ->assertSee('Gestión de Redes Sociales')
            ->assertSee('Precios Claros y a Medida')
            ->assertSee('Analizamos tu caso y te presentamos una propuesta detallada sin compromisos.')
            ->assertSee('Pedir Cotización')
            ->assertSee('Casos de Éxito')
            ->assertSee('Nosotros')
            ->assertSee('images/digital-workspace.jpg')
            ->assertSee('data-service-id="'.$websiteService->id.'"', false)
            ->assertSee('data-service-id="'.$shopService->id.'"', false)
            ->assertSee('role="search"', false)
            ->assertSee('Carrito')
            ->assertSee('id="checkout-dialog"', false)
            ->assertSee('data-checkout-step="4"', false)
            ->assertSee('href="'.route('privacy-policy').'"', false)
            ->assertSee('href="#servicios"', false)
            ->assertSee('href="'.route('home').'#servicio-tiendas-online"', false)
            ->assertSee('href="'.route('home').'#servicio-automatizacion"', false)
            ->assertSee('href="'.route('home').'#servicio-redes-sociales"', false)
            ->assertSee('href="'.route('home').'#precios"', false)
            ->assertSee('href="'.route('home').'#casos"', false)
            ->assertSee('href="'.route('home').'#nosotros"', false)
            ->assertSee('id="servicios"', false)
            ->assertSee('id="servicio-paginas-web"', false)
            ->assertSee('id="servicio-tiendas-online"', false)
            ->assertSee('id="servicio-automatizacion"', false)
            ->assertSee('id="servicio-redes-sociales"', false)
            ->assertSee('id="precios"', false)
            ->assertSee('id="casos"', false)
            ->assertSee('id="nosotros"', false)
            ->assertSee('Solicitar Diagnóstico')
            ->assertDontSee('Arquitectura web')
            ->assertDontSee('UX/UI');
    }

    public function test_diagnostic_form_displays_active_services(): void
    {
        $this->seed(ServiceSeeder::class);

        $this->get(route('diagnostics.create'))
            ->assertOk()
            ->assertSee('Solicita tu diagnóstico gratis')
            ->assertSee('Páginas web')
            ->assertSee('Tiendas online')
            ->assertSee('Automatización de procesos')
            ->assertSee('Gestión de redes sociales');
    }

    public function test_service_link_prefills_its_checkbox_in_the_diagnostic_form(): void
    {
        $this->seed(ServiceSeeder::class);
        $service = \App\Models\Service::query()
            ->where('slug', 'automatizacion-de-procesos')
            ->firstOrFail();

        $this->get(route('diagnostics.create', ['service' => $service->id]))
            ->assertOk()
            ->assertSee('id="service-'.$service->id.'"', false)
            ->assertSee('value="'.$service->id.'" checked', false);
    }

    public function test_checkout_submission_is_saved_and_returns_json_confirmation(): void
    {
        $this->seed(ServiceSeeder::class);
        $serviceIds = \App\Models\Service::query()->pluck('id')->all();

        $this->postJson(route('diagnostics.store'), [
            'name' => 'Lucía Gómez',
            'email' => 'lucia@example.com',
            'company_name' => 'Taller Luna',
            'services' => $serviceIds,
            'privacy' => '1',
        ])->assertCreated()
            ->assertJsonPath('message', 'Recibimos tu solicitud. Nos pondremos en contacto contigo pronto.')
            ->assertJsonStructure(['request_id']);

        $this->assertDatabaseHas('clients', [
            'email' => 'lucia@example.com',
            'company_name' => 'Taller Luna',
        ]);
        $this->assertDatabaseCount('diagnostic_requests', 1);
        $this->assertDatabaseCount('diagnostic_request_service', count($serviceIds));
    }

    public function test_diagnostic_request_is_saved_with_contact_and_services(): void
    {
        $this->seed(ServiceSeeder::class);
        $serviceIds = \App\Models\Service::query()->pluck('id')->all();

        $this->post(route('diagnostics.store'), [
            'name' => 'María Pérez',
            'email' => 'MARIA@example.com',
            'phone' => '600123456',
            'company_name' => 'Tienda Norte',
            'website' => 'https://tiendanorte.example',
            'services' => $serviceIds,
            'message' => 'Quiero mejorar mi tienda online.',
            'privacy' => '1',
        ])->assertRedirect(route('diagnostics.create'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('clients', [
            'name' => 'María Pérez',
            'email' => 'maria@example.com',
            'company_name' => 'Tienda Norte',
        ]);
        $this->assertDatabaseHas('diagnostic_requests', [
            'status' => 'new',
            'message' => 'Quiero mejorar mi tienda online.',
        ]);
        $this->assertDatabaseCount('diagnostic_request_service', count($serviceIds));
        $this->assertDatabaseMissing('diagnostic_requests', ['privacy_accepted_at' => null]);
    }

    public function test_diagnostic_request_requires_privacy_consent(): void
    {
        $this->seed(ServiceSeeder::class);
        $serviceId = \App\Models\Service::query()->value('id');

        $this->from(route('diagnostics.create'))
            ->post(route('diagnostics.store'), [
                'name' => 'María Pérez',
                'email' => 'maria@example.com',
                'services' => [$serviceId],
            ])
            ->assertSessionHasErrors('privacy');

        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('diagnostic_requests', 0);
    }
}