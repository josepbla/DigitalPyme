@extends('layouts.app')

@section('title', 'Acceso de administrador | DigitalPyme')

@section('content')
    <p class="eyebrow">Área privada</p>
    <h1>Acceso de administrador</h1>
    <p class="intro">Inicia sesión para acceder a las herramientas internas de DigitalPyme.</p>

    <section class="form-panel" aria-labelledby="admin-login-title">
        <h2 id="admin-login-title">Iniciar sesión</h2>
        <form method="POST" action="{{ route('admin.login.store') }}">
            @csrf
            <div class="form-grid">
                <div class="field field-full">
                    <label for="email">Correo electrónico</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                    @error('email') <p class="field-error" id="email-error">{{ $message }}</p> @enderror
                </div>
                <div class="field field-full">
                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif>
                    @error('password') <p class="field-error" id="password-error">{{ $message }}</p> @enderror
                </div>
                <div class="field field-full">
                    <button class="submit-button" type="submit">Entrar al panel</button>
                </div>
            </div>
        </form>
    </section>
@endsection