@extends('layouts.app')

@section('title', 'Solicita tu diagnóstico gratis | DigitalPyme')

@section('content')
    <p class="eyebrow">Hablemos de tu negocio</p>
    <h1>Solicita tu diagnóstico gratis</h1>
    <p class="intro">Cuéntanos qué quieres mejorar. Revisaremos tu caso y nos pondremos en contacto contigo para conversar sobre los siguientes pasos.</p>

    @if (session('success'))
        <p class="notice notice-success" role="status">{{ session('success') }}</p>
    @endif

    <section class="form-panel" aria-labelledby="form-title">
        <h2 id="form-title">Tus datos y objetivos</h2>
        <form method="POST" action="{{ route('diagnostics.store') }}" novalidate>
            @csrf

            <div class="form-grid">
                <div class="field">
                    <label for="name">Nombre completo</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="150" required @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif>
                    @error('name') <p class="field-error" id="name-error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255" required @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                    @error('email') <p class="field-error" id="email-error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="phone">Teléfono <span class="field-hint">(opcional)</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="30" @if ($errors->has('phone')) aria-invalid="true" aria-describedby="phone-error" @endif>
                    @error('phone') <p class="field-error" id="phone-error">{{ $message }}</p> @enderror
                </div>

                <div class="field">
                    <label for="company_name">Nombre del negocio <span class="field-hint">(opcional)</span></label>
                    <input id="company_name" name="company_name" type="text" value="{{ old('company_name') }}" autocomplete="organization" maxlength="150" @if ($errors->has('company_name')) aria-invalid="true" aria-describedby="company-error" @endif>
                    @error('company_name') <p class="field-error" id="company-error">{{ $message }}</p> @enderror
                </div>

                <div class="field field-full">
                    <label for="website">Sitio web actual <span class="field-hint">(opcional)</span></label>
                    <input id="website" name="website" type="url" value="{{ old('website') }}" placeholder="https://tusitio.com" maxlength="255" @if ($errors->has('website')) aria-invalid="true" aria-describedby="website-error" @endif>
                    @error('website') <p class="field-error" id="website-error">{{ $message }}</p> @enderror
                </div>

                <fieldset class="field field-full" @if ($errors->has('services') || $errors->has('services.*')) aria-describedby="services-error" @endif>
                    <legend>¿En qué te gustaría recibir ayuda?</legend>
                    <div class="service-list">
                        @forelse ($services as $service)
                            <label class="service-option" for="service-{{ $service->id }}">
                                <input id="service-{{ $service->id }}" name="services[]" type="checkbox" value="{{ $service->id }}" @checked(in_array($service->id, old('services', $selectedServiceIds)))>
                                <span>
                                    <strong>{{ $service->name }}</strong>
                                    <small>{{ $service->description }}</small>
                                </span>
                            </label>
                        @empty
                            <p class="field-hint">No hay servicios disponibles en este momento.</p>
                        @endforelse
                    </div>
                    @error('services') <p class="field-error" id="services-error">{{ $message }}</p> @enderror
                    @error('services.*') <p class="field-error" id="services-error">{{ $message }}</p> @enderror
                </fieldset>

                <div class="field field-full">
                    <label for="message">¿Qué te gustaría mejorar? <span class="field-hint">(opcional)</span></label>
                    <textarea id="message" name="message" maxlength="3000" @if ($errors->has('message')) aria-invalid="true" aria-describedby="message-error" @endif>{{ old('message') }}</textarea>
                    @error('message') <p class="field-error" id="message-error">{{ $message }}</p> @enderror
                </div>

                <div class="field field-full">
                    <label class="privacy-option" for="privacy">
                        <input id="privacy" name="privacy" type="checkbox" value="1" @checked(old('privacy')) required @if ($errors->has('privacy')) aria-invalid="true" aria-describedby="privacy-error" @endif>
                        <span>Autorizo a DigitalPyme a utilizar estos datos para responder a mi solicitud.</span>
                    </label>
                    @error('privacy') <p class="field-error" id="privacy-error">{{ $message }}</p> @enderror
                </div>

                <div class="field field-full">
                    <button class="submit-button" type="submit">Solicitar diagnóstico gratis</button>
                </div>
            </div>
        </form>
    </section>
@endsection