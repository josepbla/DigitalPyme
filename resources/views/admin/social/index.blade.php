@extends('layouts.app')

@section('title', 'Gestión de redes sociales | DigitalPyme')
@section('main_class', 'admin-main')

@section('content')
    <p class="eyebrow">Panel de administración</p>
    <div class="admin-list-heading">
        <div>
            <h1>Gestión de redes sociales</h1>
            <p class="intro">Prepara el contenido aquí y publícalo manualmente en Meta Business Suite.</p>
        </div>
        <a class="button-secondary" href="{{ route('admin.dashboard') }}">Volver al dashboard</a>
    </div>

    @if (session('status'))
        <p class="notice notice-success" role="status">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div class="admin-social-error" role="alert">
            <strong>Revisa estos datos:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="admin-social-composer" aria-labelledby="social-compose-title">
        <h2 id="social-compose-title">Preparar publicación</h2>
        <p>La programación es una agenda interna; la publicación se realiza manualmente desde Meta Business Suite.</p>

        @if ($clients->isEmpty())
            <div class="admin-empty-state">
                <h2>Primero necesitas un cliente</h2>
                <p>Registra un cliente al recibir su solicitud y vuelve aquí para preparar contenido.</p>
            </div>
        @else
            <form method="POST" action="{{ route('admin.social.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="admin-social-form-grid">
                    <label class="admin-social-field" for="social-client">
                        <span>Cliente</span>
                        <select id="social-client" name="client_id" required>
                            <option value="">Selecciona un cliente</option>
                            @foreach ($clients as $client)
                                <option value="{{ $client->id }}" @selected((string) old('client_id') === (string) $client->id)>{{ $client->name }} · {{ $client->email }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="admin-social-field" for="social-platform">
                        <span>Plataforma</span>
                        <select id="social-platform" name="platform" required>
                            @foreach ($platformLabels as $value => $label)
                                <option value="{{ $value }}" @selected(old('platform', 'instagram') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="admin-social-field admin-social-field-wide" for="social-caption">
                        <span>Texto</span>
                        <textarea id="social-caption" name="caption" maxlength="5000" required placeholder="Escribe el texto de la publicación">{{ old('caption') }}</textarea>
                    </label>

                    <label class="admin-social-field" for="social-image">
                        <span>Imagen (opcional, hasta 8 MB)</span>
                        <input id="social-image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                    </label>

                    <label class="admin-social-field" for="social-status">
                        <span>Estado inicial</span>
                        <select id="social-status" name="status" required>
                            <option value="draft" @selected(old('status', 'draft') === 'draft')>Borrador</option>
                            <option value="scheduled" @selected(old('status') === 'scheduled')>Programada (agenda manual)</option>
                        </select>
                    </label>

                    <label class="admin-social-field admin-social-field-wide" for="social-scheduled-for">
                        <span>Fecha y hora prevista</span>
                        <input id="social-scheduled-for" name="scheduled_for" type="datetime-local" value="{{ old('scheduled_for') }}">
                    </label>
                </div>
                <div class="admin-social-form-actions">
                    <button class="submit-button" type="submit">Guardar en agenda</button>
                    <span class="field-hint">La imagen se conserva en almacenamiento privado y solo la ve el equipo administrador.</span>
                </div>
            </form>
        @endif
    </section>

    <section aria-labelledby="social-list-title">
        <div class="admin-social-list-heading">
            <div>
                <h2 id="social-list-title">Agenda y publicaciones</h2>
                <p>{{ $posts->total() }} {{ $posts->total() === 1 ? 'publicación' : 'publicaciones' }}</p>
            </div>
        </div>

        <form class="admin-request-filters" method="GET" action="{{ route('admin.social.index') }}" aria-label="Filtrar publicaciones">
            <label class="admin-status-field" for="social-filter-client">
                <span>Cliente</span>
                <select id="social-filter-client" name="client_id">
                    <option value="">Todos los clientes</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected((string) ($filters['client_id'] ?? '') === (string) $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="admin-status-field" for="social-filter-platform">
                <span>Plataforma</span>
                <select id="social-filter-platform" name="platform">
                    <option value="">Todas</option>
                    @foreach ($platformLabels as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['platform'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="admin-status-field" for="social-filter-status">
                <span>Estado</span>
                <select id="social-filter-status" name="status">
                    <option value="">Todos</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <button class="submit-button" type="submit">Filtrar</button>
            @if (array_filter($filters))
                <a class="admin-clear-filter" href="{{ route('admin.social.index') }}">Limpiar</a>
            @endif
        </form>

        @if ($posts->isEmpty())
            <div class="admin-empty-state">
                <h2>No hay publicaciones para mostrar</h2>
                <p>Prepara un borrador o una fecha prevista en el formulario superior.</p>
            </div>
        @else
            <div class="admin-social-list">
                @foreach ($posts as $post)
                    <article class="admin-social-post">
                        <div class="admin-social-post-main">
                            @if ($post->image_path)
                                <img class="admin-social-image" src="{{ route('admin.social.image', $post) }}" alt="Imagen de la publicación para {{ $post->client->name }}" loading="lazy">
                            @else
                                <span class="admin-social-image-placeholder" aria-hidden="true">Sin imagen</span>
                            @endif
                            <div>
                                <div class="admin-social-meta">
                                    <span class="admin-social-platform">{{ $platformLabels[$post->platform] ?? $post->platform }}</span>
                                    <span class="admin-status-badge">{{ $statusLabels[$post->status] ?? $post->status }}</span>
                                </div>
                                <strong>{{ $post->client->name }}</strong>
                                <span class="admin-social-client">{{ $post->client->email }}</span>
                                @if ($post->scheduled_for)
                                    <span class="admin-social-schedule">Prevista: {{ $post->scheduled_for->format('d/m/Y H:i') }}</span>
                                @endif
                                @if ($post->published_at)
                                    <span class="admin-social-schedule">Publicada: {{ $post->published_at->format('d/m/Y H:i') }}</span>
                                @endif
                                <p class="admin-social-caption">{{ $post->caption }}</p>
                                @if ($post->published_url)
                                    <a class="admin-social-published-link" href="{{ $post->published_url }}" target="_blank" rel="noopener noreferrer">Abrir publicación</a>
                                @endif
                                @if ($post->error_message)
                                    <p class="field-error">{{ $post->error_message }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="admin-social-tools">
                            @if ($post->status !== 'published')
                                <details>
                                    <summary>Registrar publicación o resultado</summary>
                                    <form method="POST" action="{{ route('admin.social.publication.update', $post) }}">
                                        @csrf
                                        @method('PATCH')
                                        <label class="admin-social-field" for="post-status-{{ $post->id }}">
                                            <span>Estado</span>
                                            <select id="post-status-{{ $post->id }}" name="status" required>
                                                @foreach ($statusLabels as $value => $label)
                                                    <option value="{{ $value }}" @selected($post->status === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="admin-social-field" for="post-scheduled-{{ $post->id }}">
                                            <span>Fecha prevista si reprogramas</span>
                                            <input id="post-scheduled-{{ $post->id }}" name="scheduled_for" type="datetime-local" value="{{ $post->scheduled_for?->format('Y-m-d\TH:i') }}">
                                        </label>
                                        <label class="admin-social-field" for="post-url-{{ $post->id }}">
                                            <span>Enlace publicado</span>
                                            <input id="post-url-{{ $post->id }}" name="published_url" type="url" maxlength="2048" value="{{ old('published_url', $post->published_url) }}" placeholder="https://...">
                                        </label>
                                        <label class="admin-social-field" for="post-error-{{ $post->id }}">
                                            <span>Motivo si hubo un problema</span>
                                            <textarea id="post-error-{{ $post->id }}" name="error_message" maxlength="5000">{{ old('error_message', $post->error_message) }}</textarea>
                                        </label>
                                        <button class="admin-status-save" type="submit">Guardar resultado</button>
                                    </form>
                                </details>
                            @else
                                <details>
                                    <summary>Métricas registradas</summary>
                                    <form method="POST" action="{{ route('admin.social.metrics.update', $post) }}">
                                        @csrf
                                        @method('PATCH')
                                        <label class="admin-social-field" for="post-reach-{{ $post->id }}">
                                            <span>Alcance</span>
                                            <input id="post-reach-{{ $post->id }}" name="reach" type="number" min="0" value="{{ old('reach', $post->reach) }}" required>
                                        </label>
                                        <label class="admin-social-field" for="post-likes-{{ $post->id }}">
                                            <span>Me gusta</span>
                                            <input id="post-likes-{{ $post->id }}" name="likes" type="number" min="0" value="{{ old('likes', $post->likes) }}" required>
                                        </label>
                                        <label class="admin-social-field" for="post-comments-{{ $post->id }}">
                                            <span>Comentarios</span>
                                            <input id="post-comments-{{ $post->id }}" name="comments" type="number" min="0" value="{{ old('comments', $post->comments) }}" required>
                                        </label>
                                        @if ($post->metrics_updated_at)
                                            <span class="admin-client-meta">Actualizadas {{ $post->metrics_updated_at->format('d/m/Y H:i') }}</span>
                                        @endif
                                        <button class="admin-status-save" type="submit">Guardar métricas</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <nav class="admin-pagination" aria-label="Paginación de publicaciones">
                <span>Mostrando {{ $posts->firstItem() }}–{{ $posts->lastItem() }} de {{ $posts->total() }}</span>
                <div>
                    @if ($posts->previousPageUrl())
                        <a href="{{ $posts->previousPageUrl() }}" rel="prev">Anterior</a>
                    @else
                        <span aria-disabled="true">Anterior</span>
                    @endif
                    @if ($posts->nextPageUrl())
                        <a href="{{ $posts->nextPageUrl() }}" rel="next">Siguiente</a>
                    @else
                        <span aria-disabled="true">Siguiente</span>
                    @endif
                </div>
            </nav>
        @endif
    </section>
@endsection