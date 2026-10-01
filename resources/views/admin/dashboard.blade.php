@extends('layouts.app')

@section('title', 'Panel de administración | DigitalPyme')
@section('main_class', 'admin-main')

@section('content')
    <p class="eyebrow">Área privada</p>
    <div class="admin-list-heading">
        <div>
            <h1>Panel de administración</h1>
            <p class="intro">Actividad de {{ auth()->user()->name }} ({{ auth()->user()->email }})</p>
        </div>
        <div class="hero-actions">
            <a class="button-secondary" href="{{ route('admin.social.index') }}">Gestión de redes sociales</a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button class="button-secondary" type="submit">Cerrar sesión</button>
            </form>
        </div>
    </div>

    @if (session('status'))
        <p class="notice notice-success" role="status">{{ session('status') }}</p>
    @endif

    <section class="admin-kpis" aria-label="Resumen">
        <article class="admin-kpi">
            <span>Total de solicitudes</span>
            <strong>{{ $totalRequests }}</strong>
        </article>
        <article class="admin-kpi">
            <span>Pendientes</span>
            <strong>{{ $pendingCount }}</strong>
        </article>
        <article class="admin-kpi">
            <span>Completadas</span>
            <strong>{{ $completedCount }}</strong>
        </article>
        <article class="admin-kpi">
            <span>Clientes registrados</span>
            <strong>{{ $totalClients }}</strong>
        </article>
    </section>

    <section class="admin-activity" aria-labelledby="admin-activity-title">
        <div class="admin-activity-heading">
            <div>
                <p class="eyebrow">Seguimiento</p>
                <h2 id="admin-activity-title">Actividad reciente</h2>
            </div>
            <a class="admin-clear-filter" href="{{ route('admin.requests.index') }}">Ver solicitudes</a>
        </div>

        @if ($activities->isEmpty())
            <div class="admin-empty-state">
                <h2>Aún no hay actividad</h2>
                <p>Las solicitudes de diagnóstico y contrataciones aparecerán aquí.</p>
            </div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-requests-table admin-dashboard-table">
                    <caption class="visually-hidden">Solicitudes y contrataciones recientes</caption>
                    <thead>
                        <tr>
                            <th scope="col">Cliente</th>
                            <th scope="col">Correo</th>
                            <th scope="col">Servicio(s)</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $activity)
                            <tr>
                                <td data-label="Cliente"><strong>{{ $activity['client_name'] }}</strong></td>
                                <td data-label="Correo"><a class="admin-client-email" href="mailto:{{ $activity['email'] }}">{{ $activity['email'] }}</a></td>
                                <td data-label="Servicio(s)">{{ $activity['services'] ?: 'Sin servicios asociados' }}</td>
                                <td data-label="Tipo">{{ $activity['type'] === 'diagnostic' ? 'Diagnóstico' : 'Contratación' }}</td>
                                <td data-label="Estado">
                                    @if (($activity['has_payment'] ?? false) === true)
                                        <span class="admin-status-badge">{{ $activity['status_label'] }}</span>
                                    @else
                                        <form class="admin-status-form" method="POST" action="{{ route('admin.activity.status.update', ['type' => $activity['type'], 'id' => $activity['id']]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <label class="visually-hidden" for="activity-status-{{ $activity['type'] }}-{{ $activity['id'] }}">Estado de {{ $activity['client_name'] }}</label>
                                            <select id="activity-status-{{ $activity['type'] }}-{{ $activity['id'] }}" name="status">
                                                @foreach ($activity['status_options'] as $value => $label)
                                                    <option value="{{ $value }}" @selected($activity['status'] === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button class="admin-status-save" type="submit">Guardar</button>
                                        </form>
                                    @endif
                                    @if ($activity['payment_status_label'] ?? null)
                                        <span class="admin-client-meta">Pago: {{ $activity['payment_status_label'] }}</span>
                                    @endif
                                </td>
                                <td data-label="Fecha">{{ $activity['created_at']->format('d/m/Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection