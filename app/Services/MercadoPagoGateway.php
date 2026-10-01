<?php

namespace App\Services;

use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\MercadoPagoConfig;
use RuntimeException;

class MercadoPagoGateway
{
    public function createPreference(array $data): array
    {
        $this->authenticate();
        $preference = (new PreferenceClient())->create($data);

        return [
            'id' => $preference->id,
            'sandbox_init_point' => $preference->sandbox_init_point,
            'live_mode' => $preference->live_mode,
        ];
    }

    public function getPayment(string $paymentId): array
    {
        $this->authenticate();

        if (! ctype_digit($paymentId)) {
            throw new RuntimeException('Invalid Mercado Pago payment ID.');
        }

        $payment = (new PaymentClient())->get((int) $paymentId);

        return [
            'id' => (string) $payment->id,
            'external_reference' => $payment->external_reference,
            'status' => $payment->status,
            'transaction_amount' => $payment->transaction_amount,
            'currency_id' => $payment->currency_id,
            'date_created' => $payment->date_created,
            'date_approved' => $payment->date_approved,
            'live_mode' => $payment->live_mode,
        ];
    }

    private function authenticate(): void
    {
        $accessToken = (string) config('services.mercadopago.access_token');

        if (config('services.mercadopago.mode') !== 'sandbox' || $accessToken === '') {
            throw new RuntimeException('Mercado Pago sandbox credentials are not configured.');
        }

        MercadoPagoConfig::setAccessToken($accessToken);
    }
}