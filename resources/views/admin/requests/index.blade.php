@extends('layouts.app')

@section('title', 'Solicitudes de diagnóstico | DigitalPyme')
@section('main_class', 'admin-main')

@section('content')
    <p class="eyebrow">Panel de administración</p>
    <div class="admin-list-heading">
        <div>
            <h1>Solicitudes de diagnóstico</h1>
            <p class="intro">{{ $requests->total() }} {{ $requests->total() === 1 ? 'solicitud recibida' : 'solicitudes recibidas' }}</p>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="button-secondary" type="submit">Cerrar sesión</button>
        </form>
    </div>

    <form class="admin-request-filters" method="GET" action="{{ route('admin.requests.index') }}" role="search" aria-label="Filtrar solicitudes">
        <label class="admin-search-field" for="request-search">
            <span>Buscar cliente</span>
            <input id="request-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Nombre, empresa o correo">
        </label>
        <label class="admin-status-field" for="request-status">
            <span>Estado</span>
            <select id="request-status" name="status">
                <option value="">Todos los estados</option>
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <button class="submit-button" type="submit">Filtrar</button>
        @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? '') !== '')
            <a class="admin-clear-filter" href="{{ route('admin.requests.index') }}">Limpiar filtros</a>
        @endif
    </form>

    @if ($requests->isEmpty())
        <section class="admin-empty-state" aria-live="polite">
            <h2>No hay solicitudes para mostrar</h2>
            <p>{{ ($filters['q'] ?? '') !== '' || ($filters['status'] ?? '') !== '' ? 'Prueba con otros filtros.' : 'Cuando alguien envíe un diagnóstico, aparecerá aquí.' }}</p>
        </section>
    @else
        <div class="admin-table-wrap">
            <table class="admin-requests-table">
                <caption class="visually-hidden">Solicitudes de diagnóstico recibidas</caption>
                <thead>
                    <tr>
                        <th scope="col">Recibida</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Servicios</th>
                        <th scope="col">Mensaje</th>
                        <th scope="col">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $diagnosticRequest)
                        <tr>
                            <td data-label="Recibida">{{ $diagnosticRequest->created_at->format('d/m/Y H:i') }}</td>
                            <td data-label="Cliente">
                                <strong>{{ $diagnosticRequest->client->name }}</strong>
                                <a class="admin-client-email" href="mailto:{{ $diagnosticRequest->client->email }}">{{ $diagnosticRequest->client->email }}</a>
                                @if ($diagnosticRequest->client->company_name)
                                    <span class="admin-client-meta">{{ $diagnosticRequest->client->company_name }}</span>
                                @endif
                                @if ($diagnosticRequest->client->phone)
                                    <span class="admin-client-meta">{{ $diagnosticRequest->client->phone }}</span>
                                @endif
                            </td>
                            <td data-label="Servicios">
                                <ul class="admin-service-list">
                                    @foreach ($diagnosticRequest->services as $service)
                                        <li>{{ $service->name }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td data-label="Mensaje">
                                @if ($diagnosticRequest->message)
                                    <details class="admin-message">
                                        <summary>Ver mensaje</summary>
                                        <p>{{ $diagnosticRequest->message }}</p>
                                    </details>
                                @else
                                    <span class="admin-client-meta">Sin mensaje</span>
                                @endif
                            </td>
                            <td data-label="Estado">
                                <span class="admin-status-badge admin-status-{{ $diagnosticRequest->status }}">{{ $statusLabels[$diagnosticRequest->status] ?? $diagnosticRequest->status }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <nav class="admin-pagination" aria-label="Paginación de solicitudes">
            <span>Mostrando {{ $requests->firstItem() }}–{{ $requests->lastItem() }} de {{ $requests->total() }}</span>
            <div>
                @if ($requests->previousPageUrl())
                    <a href="{{ $requests->previousPageUrl() }}" rel="prev">Anterior</a>
                @else
                    <span aria-disabled="true">Anterior</span>
                @endif
                @if ($requests->nextPageUrl())
                    <a href="{{ $requests->nextPageUrl() }}" rel="next">Siguiente</a>
                @else
                    <span aria-disabled="true">Siguiente</span>
                @endif
            </div>
        </nav>
    @endif
@endsection