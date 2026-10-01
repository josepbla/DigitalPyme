<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\MercadoPagoGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MercadoPago\Exceptions\InvalidWebhookSignatureException;
use MercadoPago\Webhook\WebhookSignatureValidator;
use Throwable;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request, MercadoPagoGateway $gateway)
    {
        $secret = (string) config('services.mercadopago.webhook_secret');

        if ($secret === '') {
            return response('Webhook secret is not configured.', 503);
        }

        $notification = $request->all();
        $query = $request->query->all();
        $dataId = (string) (data_get($notification, 'data.id')
            ?? $query['data.id']
            ?? data_get($query, 'data.id')
            ?? $request->input('id', ''));

        try {
            WebhookSignatureValidator::validate(
                $request->header('x-signature'),
                $request->header('x-request-id'),
                $dataId !== '' ? $dataId : null,
                $secret,
            );
        } catch (InvalidWebhookSignatureException) {
            Log::warning('Mercado Pago webhook signature validation failed.');

            return response('Invalid webhook signature.', 401);
        }

        $eventType = $request->input('type') ?? $request->input('topic') ?? $request->query('topic');

        if (! in_array($eventType, ['payment', 'payment.updated'], true) || $dataId === '') {
            return response()->noContent();
        }

        try {
            $remotePayment = $gateway->getPayment($dataId);
        } catch (Throwable $exception) {
            Log::error('Could not fetch Mercado Pago payment for webhook.', [
                'payment_id' => $dataId,
                'exception' => $exception->getMessage(),
            ]);

            return response('Payment lookup failed.', 500);
        }

        if (($remotePayment['live_mode'] ?? null) !== false) {
            Log::warning('Ignoring a non-sandbox Mercado Pago payment notification.', ['payment_id' => $dataId]);

            return response()->noContent();
        }

        $externalReference = (string) ($remotePayment['external_reference'] ?? '');
        $payment = ctype_digit($externalReference) ? Payment::query()->find((int) $externalReference) : null;

        if (! $payment) {
            Log::warning('Mercado Pago payment has no matching local payment.', ['payment_id' => $dataId]);

            return response()->noContent();
        }

        $reportedAmount = number_format((float) ($remotePayment['transaction_amount'] ?? -1), 2, '.', '');
        $expectedAmount = number_format((float) $payment->amount, 2, '.', '');
        $reportedCurrency = (string) ($remotePayment['currency_id'] ?? '');

        if ($reportedAmount !== $expectedAmount || $reportedCurrency !== $payment->currency || (string) ($remotePayment['id'] ?? '') !== $dataId) {
            Log::warning('Mercado Pago webhook payment details do not match the local payment.', [
                'payment_id' => $payment->id,
                'mercadopago_payment_id' => $dataId,
            ]);

            return response()->noContent();
        }

        $status = match ($remotePayment['status'] ?? '') {
            'approved' => 'approved',
            'rejected', 'cancelled', 'refunded', 'charged_back' => 'rejected',
            default => 'pending',
        };

        $newlyApproved = DB::transaction(function () use ($payment, $remotePayment, $status, $dataId): bool {
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $newlyApproved = $status === 'approved' && $lockedPayment->status !== 'approved';

            $lockedPayment->update([
                'mercadopago_payment_id' => $dataId,
                'status' => $status,
                'mercadopago_created_at' => isset($remotePayment['date_created'])
                    ? Carbon::parse($remotePayment['date_created'])
                    : $lockedPayment->mercadopago_created_at,
                'paid_at' => $status === 'approved'
                    ? (isset($remotePayment['date_approved']) ? Carbon::parse($remotePayment['date_approved']) : now())
                    : null,
            ]);

            $lockedPayment->clientServices()->update([
                'status' => match ($status) {
                    'approved' => 'paid',
                    'rejected' => 'payment_rejected',
                    default => 'payment_pending',
                },
            ]);

            return $newlyApproved;
        });

        if ($newlyApproved) {
            Log::info('Mercado Pago payment approved.', [
                'payment_id' => $payment->id,
                'mercadopago_payment_id' => $dataId,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
            ]);
        }

        return response()->noContent();
    }

}