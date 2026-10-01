@extends('layouts.app')

@section('title', 'Resultado del pago | DigitalPyme')

@section('content')
    <p class="eyebrow">Pago de prueba</p>
    <h1>
        @switch($payment->status)
            @case('approved') Pago aprobado @break
            @case('rejected') Pago rechazado @break
            @case('preference_failed') No se pudo iniciar el pago @break
            @default Estamos verificando tu pago
        @endswitch
    </h1>
    <p class="intro">
        @switch($payment->status)
            @case('approved') El pago fue confirmado por Mercado Pago. Tu contratación quedó registrada. @break
            @case('rejected') Mercado Pago rechazó el pago. Puedes intentarlo nuevamente desde el checkout. @break
            @case('preference_failed') No se creó el checkout. Vuelve a intentarlo más tarde. @break
            @default Recibimos el regreso del checkout. El estado definitivo se actualizará cuando nuestro servidor confirme el pago con Mercado Pago.
        @endswitch
    </p>
    <section class="form-panel">
        <p><strong>Importe:</strong> {{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</p>
        <p><strong>Servicios:</strong> {{ $payment->clientServices->pluck('service.name')->filter()->join(', ') }}</p>
        <a class="nav-cta" href="{{ route('home') }}">Volver a DigitalPyme</a>
    </section>
@endsection