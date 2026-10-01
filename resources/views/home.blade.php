@extends('layouts.app')

@section('title', 'DigitalPyme | Presencia digital para tu negocio')
@section('main_class', 'home-main')

@section('content')
    <section class="home-hero" id="inicio" aria-labelledby="hero-title">
        <img class="home-hero-image" src="{{ asset('images/digital-workspace.jpg') }}" alt="Ordenador portátil en un espacio de trabajo digital" fetchpriority="high" decoding="async">
        <div class="home-hero-content">
            <p class="eyebrow">Tu equipo digital, sin ampliar tu equipo</p>
            <h1 id="hero-title">Soluciones digitales a la medida de tu negocio.</h1>
            <p class="intro hero-slide-message" data-hero-message aria-live="polite">Creamos páginas web, tiendas online y automatizamos tus procesos para que vendas más.</p>
            <div class="hero-actions">
                <a class="nav-cta" href="{{ route('diagnostics.create') }}">Hablemos de tu proyecto</a>
                <a class="hero-secondary" href="#servicios">Ver servicios</a>
            </div>
            <div class="hero-slide-controls" role="group" aria-label="Mensajes destacados">
                <button type="button" data-slide-previous aria-label="Mostrar mensaje anterior">&larr;</button>
                <span class="hero-slide-index" data-slide-index aria-live="off">01 / 03</span>
                <button type="button" data-slide-next aria-label="Mostrar mensaje siguiente">&rarr;</button>
            </div>
        </div>
    </section>

    <section class="section-band" id="servicios" aria-labelledby="services-title">
        <div class="section-heading">
            <p class="eyebrow">Lo que hacemos</p>
            <h2 id="services-title">Lo que hacemos por tu empresa</h2>
        </div>
        <div class="service-grid">
            @foreach ($serviceCards as $service)
                <article class="service-card" id="servicio-{{ $service['slug'] }}" data-service-card>
                    <span class="service-icon" aria-hidden="true">
                        @if ($service['slug'] === 'paginas-web')
                            <svg viewBox="0 0 48 48"><rect x="5" y="8" width="38" height="28" rx="3"/><path d="M5 16h38M13 12h.1m5 0h.1M18 42h12m-6-6v6m-4-18 4 4 8-9"/></svg>
                        @elseif ($service['slug'] === 'tiendas-online')
                            <svg viewBox="0 0 48 48"><path d="M7 9h5l4 22h21l5-16H14M18 38h.1m17 0h.1"/><circle cx="18" cy="38" r="3"/><circle cx="35" cy="38" r="3"/><path d="M19 15h14m-7-7v14"/></svg>
                        @elseif ($service['slug'] === 'automatizacion')
                            <svg viewBox="0 0 48 48"><rect x="7" y="8" width="13" height="12" rx="2"/><rect x="28" y="28" width="13" height="12" rx="2"/><path d="M20 14h8a6 6 0 0 1 6 6v8M28 34h-8a6 6 0 0 1-6-6v-8m0 0 4 4m-4-4-4 4m20 8-4-4m4 4 4-4"/></svg>
                        @else
                            <svg viewBox="0 0 48 48"><circle cx="15" cy="14" r="5"/><circle cx="34" cy="14" r="5"/><circle cx="24" cy="34" r="5"/><path d="m19 17 3 12m7-12-3 12M20 14h9"/><path d="M7 27c4-4 8-5 12-5m10 0c5 0 8 2 12 5"/></svg>
                        @endif
                    </span>
                    <h3>{{ $service['title'] }}</h3>
                    <p>{{ $service['description'] }}</p>
                    <button class="add-to-cart" type="button" data-add-to-cart data-service-id="{{ $service['serviceId'] }}" data-service-name="{{ $service['title'] }}" data-service-price="{{ $service['price'] ?? '' }}" @disabled(! $service['serviceId']) aria-pressed="false"><span data-add-label>Añadir al carrito</span><span aria-hidden="true" data-add-icon>+</span></button>
                </article>
            @endforeach
        </div>
        <p class="catalog-status" data-catalog-status aria-live="polite"></p>
        <p class="quiet-note" data-catalog-empty hidden>No encontramos servicios con ese término. Prueba con otra búsqueda.</p>
    </section>

    <section class="section-band" id="precios" aria-labelledby="pricing-title">
        <div class="section-heading">
            <p class="eyebrow">Precios claros</p>
            <h2 id="pricing-title">Precios Claros y a Medida</h2>
            <p>Analizamos tu caso y te presentamos una propuesta detallada sin compromisos.</p>
            <a class="nav-cta section-cta" href="{{ route('diagnostics.create') }}">Pedir Cotización</a>
        </div>
    </section>

    <dialog class="checkout-dialog" id="checkout-dialog" aria-labelledby="checkout-title">
        <div class="checkout-shell">
            <div class="checkout-header">
                <div>
                    <p class="eyebrow">Contratación digital</p>
                    <h2 id="checkout-title">Tu proyecto, paso a paso</h2>
                </div>
                <button class="checkout-close" type="button" data-checkout-close aria-label="Cerrar carrito">&times;</button>
            </div>

            <ol class="checkout-progress" aria-label="Progreso de contratación">
                <li data-step-indicator="1" aria-current="step"><span class="step-number">1</span><span>Carrito</span></li>
                <li data-step-indicator="2"><span class="step-number">2</span><span>Envío y Datos</span></li>
                <li data-step-indicator="3"><span class="step-number">3</span><span>Pago Seguro</span></li>
                <li data-step-indicator="4"><span class="step-number">4</span><span>Confirmación</span></li>
            </ol>

            <p class="checkout-error" id="checkout-error" role="alert" hidden></p>

            <form id="checkout-form" action="{{ route('checkout.store') }}" data-quote-action="{{ route('diagnostics.store') }}" data-currency="{{ config('services.mercadopago.currency') }}" method="POST" novalidate>
                @csrf
                <div data-checkout-step="1" class="checkout-step" aria-labelledby="cart-step-title">
                    <h3 id="cart-step-title" tabindex="-1">Resumen de tu selección</h3>
                    <p>Elige uno o más servicios. Prepararemos una propuesta según lo que necesite tu negocio.</p>
                    <p class="quiet-note" data-cart-empty>Tu carrito está vacío. Añade un servicio para comenzar.</p>
                    <ul class="checkout-items" data-cart-items aria-label="Servicios seleccionados"></ul>
                    <div class="checkout-total"><span>Total</span><strong data-cart-total>Cotización personalizada</strong></div>
                    <div class="checkout-actions checkout-actions-end">
                        <button class="nav-cta" type="button" data-checkout-next="2" disabled>Continuar con mis datos</button>
                    </div>
                </div>

                <div data-checkout-step="2" class="checkout-step" aria-labelledby="data-step-title" hidden>
                    <h3 id="data-step-title" tabindex="-1">Datos de contacto</h3>
                    <p>Solo necesitamos lo esencial para preparar y responder tu solicitud.</p>
                    <label class="checkout-field" for="checkout-name">Nombre
                        <input id="checkout-name" name="name" type="text" autocomplete="name" maxlength="150" required>
                    </label>
                    <label class="checkout-field" for="checkout-email">Correo electrónico
                        <input id="checkout-email" name="email" type="email" autocomplete="email" maxlength="255" required>
                    </label>
                    <label class="checkout-field" for="checkout-company">Empresa <span class="field-hint">(opcional)</span>
                        <input id="checkout-company" name="company_name" type="text" autocomplete="organization" maxlength="150">
                    </label>
                    <label class="privacy-option checkout-privacy" for="checkout-privacy">
                        <input id="checkout-privacy" name="privacy" type="checkbox" value="1" required>
                        <span>He leído y acepto la <a href="{{ route('privacy-policy') }}" target="_blank" rel="noopener">Política de Privacidad</a>.</span>
                    </label>
                    <div class="checkout-actions">
                        <button class="button-secondary" type="button" data-checkout-back="1">Volver al carrito</button>
                        <button class="nav-cta" type="button" data-checkout-next="3">Continuar</button>
                    </div>
                </div>

                <div data-checkout-step="3" class="checkout-step" aria-labelledby="payment-step-title" hidden>
                    <h3 id="payment-step-title" tabindex="-1">Pago y seguridad</h3>
                    <p>Los servicios con precio publicado se pagan en el checkout seguro de Mercado Pago. Los servicios sin precio se envían como solicitud de cotización, sin cobro.</p>
                    <div class="payment-notice">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 4.5 6v5c0 5 3.2 8.3 7.5 10 4.3-1.7 7.5-5 7.5-10V6z"/><path d="m9 12 2 2 4-4"/></svg>
                        <p><strong>El pago se procesa fuera de este sitio en Mercado Pago.</strong> No introduzcas aquí datos de tarjeta. Solo se cobrará si todos los servicios elegidos tienen un precio configurado.</p>
                    </div>
                    <p class="field-hint">Métodos de pago disponibles en tu cuenta de Mercado Pago:</p>
                    <div class="payment-brands" aria-label="Mercado Pago">
                        <span class="payment-brand payment-brand-visa">VISA</span>
                        <span class="payment-brand payment-brand-mastercard">mastercard</span>
                    </div>
                    <div class="checkout-actions">
                        <button class="button-secondary" type="button" data-checkout-back="2">Volver a mis datos</button>
                        <button class="nav-cta" type="submit" data-checkout-submit>Continuar</button>
                    </div>
                </div>

                <div data-checkout-step="4" class="checkout-step" aria-labelledby="confirmation-step-title" role="status" hidden>
                    <span class="checkout-success-mark" aria-hidden="true">&#10003;</span>
                    <h3 id="confirmation-step-title" tabindex="-1">Solicitud recibida</h3>
                    <p class="checkout-success-copy" data-checkout-confirmation>Hemos guardado tu solicitud y nos pondremos en contacto contigo pronto.</p>
                    <div class="checkout-actions checkout-actions-end">
                        <button class="nav-cta" type="button" data-checkout-close>Volver a DigitalPyme</button>
                    </div>
                </div>
            </form>
        </div>
    </dialog>
@endsection

@push('scripts')
    <script src="{{ asset('js/digitalpyme-store.js') }}" defer></script>
@endpush