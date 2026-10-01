<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiagnosticRequest;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Service;
use App\Services\MercadoPagoGateway;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CheckoutController extends Controller
{
    public function store(StoreDiagnosticRequest $request, MercadoPagoGateway $gateway): JsonResponse
    {
        $accessToken = (string) config('services.mercadopago.access_token');
        $currency = (string) config('services.mercadopago.currency');

        if (config('services.mercadopago.mode') !== 'sandbox' || $accessToken === '') {
            return response()->json([
                'message' => 'El checkout de prueba aún no está configurado. Añade las credenciales TEST de Mercado Pago en el archivo .env.',
            ], 503);
        }

        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            return response()->json([
                'message' => 'Configura en .env la moneda de tu cuenta de Mercado Pago (código ISO de tres letras).',
            ], 503);
        }

        $data = $request->validated();
        $services = Service::query()
            ->where('is_active', true)
            ->whereKey($data['services'])
            ->get()
            ->keyBy('id');

        if ($services->count() !== count($data['services']) || $services->contains(fn (Service $service) => $service->price === null || (float) $service->price <= 0)) {
            return response()->json([
                'message' => 'Hay servicios sin precio configurado. Solicita primero una cotización o configura sus precios antes de habilitar el cobro.',
                'errors' => ['services' => ['Cada servicio seleccionado debe tener un precio mayor que cero.']],
            ], 422);
        }

        $amountInCents = $services->sum(fn (Service $service) => (int) round((float) $service->price * 100));
        $amount = number_format($amountInCents / 100, 2, '.', '');
        $payment = DB::transaction(function () use ($data, $services, $amount, $currency): Payment {
            $client = Client::query()->firstOrNew(['email' => mb_strtolower($data['email'])]);
            $client->fill([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'website' => $data['website'] ?? null,
            ]);
            $client->save();

            $diagnosticRequest = $client->diagnosticRequests()->create([
                'message' => $data['message'] ?? null,
                'privacy_accepted_at' => now(),
            ]);
            $diagnosticRequest->services()->sync($services->keys());

            $payment = Payment::query()->create([
                'client_id' => $client->id,
                'diagnostic_request_id' => $diagnosticRequest->id,
                'return_token' => (string) Str::uuid(),
                'status' => 'pending',
                'amount' => $amount,
                'currency' => $currency,
            ]);

            $client->clientServices()->createMany($services->map(fn (Service $service): array => [
                'payment_id' => $payment->id,
                'service_id' => $service->id,
                'status' => 'payment_pending',
                'agreed_price' => $service->price,
                'currency' => $currency,
            ])->values()->all());

            return $payment;
        });

        try {
            $resultUrl = route('payments.result', ['token' => $payment->return_token]);
            $preferenceData = [
                'items' => $services->map(fn (Service $service): array => [
                    'id' => (string) $service->id,
                    'title' => $service->name,
                    'quantity' => 1,
                    'currency_id' => $currency,
                    'unit_price' => (float) $service->price,
                ])->values()->all(),
                'payer' => ['email' => mb_strtolower($data['email'])],
                'external_reference' => (string) $payment->id,
                'metadata' => ['payment_id' => $payment->id],
                'back_urls' => [
                    'success' => $resultUrl,
                    'failure' => $resultUrl,
                    'pending' => $resultUrl,
                ],
                'auto_return' => 'approved',
            ];
            $webhookUrl = config('services.mercadopago.webhook_url');

            if ($webhookUrl) {
                $preferenceData['notification_url'] = $webhookUrl;
            }

            $preference = $gateway->createPreference($preferenceData);

            if (empty($preference['id']) || empty($preference['sandbox_init_point']) || ($preference['live_mode'] ?? null) !== false) {
                throw new \RuntimeException('Mercado Pago did not return a sandbox checkout URL.');
            }

            $payment->update(['mercadopago_preference_id' => $preference['id']]);

            return response()->json([
                'message' => 'Preferencia de pago creada.',
                'checkout_url' => $preference['sandbox_init_point'],
            ], 201);
        } catch (Throwable $exception) {
            $payment->update(['status' => 'preference_failed']);
            $payment->clientServices()->update(['status' => 'payment_failed']);
            Log::error('Mercado Pago preference creation failed.', [
                'payment_id' => $payment->id,
                'exception' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'No fue posible iniciar el pago de prueba. Inténtalo de nuevo más tarde.',
            ], 502);
        }
    }
}