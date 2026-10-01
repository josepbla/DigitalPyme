<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Contracts\View\View;

class PaymentResultController extends Controller
{
    public function show(string $token): View
    {
        $payment = Payment::query()->where('return_token', $token)->firstOrFail();
        $payment->load('clientServices.service');

        return view('checkout.result', compact('payment'));
    }
}