<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ClientService;
use App\Models\DiagnosticRequest;
use App\Models\Payment;
use App\Models\Service;
use App\Services\MercadoPagoGateway;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class MercadoPagoCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.mercadopago.mode' => 'sandbox',
            'services.mercadopago.access_token' => 'TEST-sandbox-token',
            'services.mercadopago.webhook_secret' => 'webhook-test-secret',
            'services.mercadopago.currency' => 'PEN',
        ]);
    }

    public function test_checkout_creates_a_sandbox_preference_using_server_prices(): void
    {
        $this->seed(ServiceSeeder::class);
        $services = Service::query()->orderBy('id')->take(2)->get();
        $services[0]->update(['price' => '50.00']);
        $services[1]->update(['price' => '100.00']);

        $gateway = Mockery::mock(MercadoPagoGateway::class);
        $gateway->shouldReceive('createPreference')
            ->once()
            ->withArgs(function (array $preference) use ($services): bool {
                $this->assertSame('150.00', number_format(array_sum(array_column($preference['items'], 'unit_price')), 2, '.', ''));
                $this->assertSame('PEN', $preference['items'][0]['currency_id']);
                $this->assertSame('ana@example.com', $preference['payer']['email']);
                $this->assertSame('1', $preference['external_reference']);
                $this->assertSame((string) $services[0]->id, $preference['items'][0]['id']);

                return true;
            })
            ->andReturn([
                'id' => 'pref-test-123',
                'sandbox_init_point' => 'https://sandbox.mercadopago.test/checkout',
                'live_mode' => false,
            ]);
        $this->app->instance(MercadoPagoGateway::class, $gateway);

        $serviceIds = $services->pluck('id')->all();
        $this->postJson(route('checkout.store'), [
            'name' => 'Ana Torres',
            'email' => 'ANA@example.com',
            'services' => $serviceIds,
            'privacy' => '1',
            'amount' => '0.01',
        ])->assertCreated()
            ->assertJsonPath('checkout_url', 'https://sandbox.mercadopago.test/checkout');

        $this->assertDatabaseHas('payments', [
            'mercadopago_preference_id' => 'pref-test-123',
            'status' => 'pending',
            'amount' => '150.00',
            'currency' => 'PEN',
        ]);
        $this->assertDatabaseCount('client_services', 2);
        $this->assertDatabaseHas('client_services', ['status' => 'payment_pending']);
    }

    public function test_checkout_without_a_configured_service_price_does_not_create_a_payment(): void
    {
        $this->seed(ServiceSeeder::class);
        $service = Service::query()->firstOrFail();

        $this->postJson(route('checkout.store'), [
            'name' => 'Ana Torres',
            'email' => 'ana@example.com',
            'services' => [$service->id],
            'privacy' => '1',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('services');

        $this->assertDatabaseCount('clients', 0);
        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseCount('diagnostic_requests', 0);
    }

    public function test_checkout_never_redirects_to_a_live_preference(): void
    {
        $this->seed(ServiceSeeder::class);
        $service = Service::query()->firstOrFail();
        $service->update(['price' => '25.00']);

        $gateway = Mockery::mock(MercadoPagoGateway::class);
        $gateway->shouldReceive('createPreference')->once()->andReturn([
            'id' => 'pref-live-123',
            'sandbox_init_point' => 'https://sandbox.mercadopago.test/checkout',
            'live_mode' => true,
        ]);
        $this->app->instance(MercadoPagoGateway::class, $gateway);

        $this->postJson(route('checkout.store'), [
            'name' => 'Ana Torres',
            'email' => 'ana@example.com',
            'services' => [$service->id],
            'privacy' => '1',
        ])->assertStatus(502)
            ->assertJsonMissingPath('checkout_url');

        $this->assertDatabaseHas('payments', ['status' => 'preference_failed']);
    }

    public function test_signed_webhooks_sync_approved_pending_and_rejected_payments(): void
    {
        $this->seed(ServiceSeeder::class);
        $service = Service::query()->firstOrFail();
        $fixtures = [];

        foreach ([
            '600001' => 'approved',
            '600002' => 'pending',
            '600003' => 'rejected',
        ] as $gatewayId => $status) {
            $fixtures[$gatewayId] = $this->createPaymentFixture($service, (int) $gatewayId, $status);
        }

        $gateway = Mockery::mock(MercadoPagoGateway::class);
        $gateway->shouldReceive('getPayment')
            ->times(3)
            ->andReturnUsing(fn (string $id): array => [
                'id' => $id,
                'external_reference' => (string) $fixtures[$id]['payment']->id,
                'status' => $fixtures[$id]['remote_status'],
                'transaction_amount' => 75.00,
                'currency_id' => 'PEN',
                'date_created' => now()->toIso8601String(),
                'date_approved' => $fixtures[$id]['remote_status'] === 'approved' ? now()->toIso8601String() : null,
                'live_mode' => false,
            ]);
        $this->app->instance(MercadoPagoGateway::class, $gateway);

        foreach ($fixtures as $gatewayId => $fixture) {
            $this->sendSignedWebhook((string) $gatewayId)
                ->assertNoContent();

            $fixture['payment']->refresh();
            $fixture['contract']->refresh();
            $expectedStatus = match ($fixture['remote_status']) {
                'approved' => ['approved', 'paid'],
                'rejected' => ['rejected', 'payment_rejected'],
                default => ['pending', 'payment_pending'],
            };

            $this->assertSame($expectedStatus[0], $fixture['payment']->status);
            $this->assertSame((string) $gatewayId, $fixture['payment']->mercadopago_payment_id);
            $this->assertNotNull($fixture['payment']->mercadopago_created_at);
            $this->assertSame($expectedStatus[1], $fixture['contract']->status);
            $this->assertSame($fixture['remote_status'] === 'approved', $fixture['payment']->paid_at !== null);
        }
    }

    public function test_webhook_rejects_an_invalid_signature_without_querying_mercado_pago(): void
    {
        $gateway = Mockery::mock(MercadoPagoGateway::class);
        $gateway->shouldNotReceive('getPayment');
        $this->app->instance(MercadoPagoGateway::class, $gateway);

        $this->withHeaders([
            'x-request-id' => 'request-invalid',
            'x-signature' => 'ts=123,v1=invalid',
        ])->postJson(route('payments.webhook'), [
            'type' => 'payment',
            'data' => ['id' => '600001'],
        ])->assertUnauthorized();
    }

    public function test_checkout_return_query_parameters_cannot_mark_a_payment_as_approved(): void
    {
        $this->seed(ServiceSeeder::class);
        $service = Service::query()->firstOrFail();
        $fixture = $this->createPaymentFixture($service, 600004, 'pending');

        $this->get(route('payments.result', ['token' => $fixture['payment']->return_token]).'?status=approved&payment_id=600004')
            ->assertOk()
            ->assertSee('Estamos verificando tu pago');

        $this->assertDatabaseHas('payments', [
            'id' => $fixture['payment']->id,
            'status' => 'pending',
        ]);
    }

    private function createPaymentFixture(Service $service, int $gatewayId, string $remoteStatus): array
    {
        $client = Client::query()->create([
            'name' => 'Cliente '.$gatewayId,
            'email' => $gatewayId.'@example.com',
            'status' => 'lead',
        ]);
        $diagnostic = $client->diagnosticRequests()->create([
            'status' => 'new',
            'privacy_accepted_at' => now(),
        ]);
        $payment = Payment::query()->create([
            'client_id' => $client->id,
            'diagnostic_request_id' => $diagnostic->id,
            'return_token' => (string) Str::uuid(),
            'status' => 'pending',
            'amount' => '75.00',
            'currency' => 'PEN',
        ]);
        $contract = ClientService::query()->create([
            'client_id' => $client->id,
            'payment_id' => $payment->id,
            'service_id' => $service->id,
            'status' => 'payment_pending',
            'agreed_price' => '75.00',
            'currency' => 'PEN',
        ]);

        return compact('payment', 'contract') + ['remote_status' => $remoteStatus];
    }

    private function sendSignedWebhook(string $paymentId)
    {
        $requestId = (string) Str::uuid();
        $timestamp = (string) time();
        $manifest = 'id:'.strtolower($paymentId).';request-id:'.$requestId.';ts:'.$timestamp.';';
        $signature = hash_hmac('sha256', $manifest, 'webhook-test-secret');

        return $this->withHeaders([
            'x-request-id' => $requestId,
            'x-signature' => 'ts='.$timestamp.',v1='.$signature,
        ])->postJson(route('payments.webhook'), [
            'type' => 'payment',
            'data' => ['id' => $paymentId],
        ]);
    }
}