<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientService;
use App\Models\DiagnosticRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminDashboardController extends Controller
{
    private const DIAGNOSTIC_STATUSES = [
        'new' => 'Pendiente',
        'reviewing' => 'En proceso',
        'contacted' => 'Contactada',
        'closed' => 'Completado',
    ];

    private const CONTRACT_STATUSES = [
        'quoted' => 'Pendiente',
        'active' => 'En proceso',
        'completed' => 'Completado',
        'payment_pending' => 'Pago pendiente',
        'paid' => 'Pagado',
        'payment_rejected' => 'Pago rechazado',
        'payment_failed' => 'Error de pago',
    ];

    private const PAYMENT_STATUSES = [
        'pending' => 'Pendiente',
        'approved' => 'Aprobado',
        'rejected' => 'Rechazado',
        'preference_failed' => 'No iniciado',
    ];

    public function index(): View
    {
        $diagnosticRequests = DiagnosticRequest::query()
            ->with(['client:id,name,email', 'services:id,name'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (DiagnosticRequest $request): array => [
                'type' => 'diagnostic',
                'id' => $request->id,
                'client_name' => $request->client->name,
                'email' => $request->client->email,
                'services' => $request->services->pluck('name')->join(', '),
                'status' => $request->status,
                'status_label' => self::DIAGNOSTIC_STATUSES[$request->status] ?? $request->status,
                'status_options' => self::DIAGNOSTIC_STATUSES,
                'created_at' => $request->created_at,
            ]);

        $contracts = ClientService::query()
            ->with(['client:id,name,email', 'service:id,name', 'payment:id,status'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (ClientService $contract): array => [
                'type' => 'contract',
                'id' => $contract->id,
                'client_name' => $contract->client->name,
                'email' => $contract->client->email,
                'services' => $contract->service->name,
                'status' => $contract->status,
                'status_label' => self::CONTRACT_STATUSES[$contract->status] ?? $contract->status,
                'status_options' => self::CONTRACT_STATUSES,
                'has_payment' => $contract->payment !== null,
                'payment_status_label' => $contract->payment
                    ? (self::PAYMENT_STATUSES[$contract->payment->status] ?? $contract->payment->status)
                    : null,
                'created_at' => $contract->created_at,
            ]);

        return view('admin.dashboard', [
            'totalRequests' => DiagnosticRequest::query()->count(),
            'pendingCount' => DiagnosticRequest::query()->whereIn('status', ['new', 'reviewing', 'contacted'])->count()
                + ClientService::query()->whereIn('status', ['quoted', 'active', 'payment_pending'])->count(),
            'completedCount' => DiagnosticRequest::query()->where('status', 'closed')->count()
                + ClientService::query()->whereIn('status', ['completed', 'paid'])->count(),
            'totalClients' => \App\Models\Client::query()->count(),
            'activities' => $diagnosticRequests->concat($contracts)
                ->sortByDesc(fn (array $activity) => $activity['created_at'])
                ->take(10)
                ->values(),
        ]);
    }

    public function updateStatus(Request $request, string $type, int $id): RedirectResponse
    {
        $statuses = $type === 'diagnostic'
            ? self::DIAGNOSTIC_STATUSES
            : array_diff_key(self::CONTRACT_STATUSES, array_flip(['payment_pending', 'paid', 'payment_rejected', 'payment_failed']));
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys($statuses))],
        ]);

        $record = $type === 'diagnostic'
            ? DiagnosticRequest::query()->findOrFail($id)
            : ClientService::query()->findOrFail($id);

        if ($record instanceof ClientService && $record->payment_id) {
            abort(403, 'El estado de una contratación pagada se actualiza mediante la notificación de pago.');
        }

        $record->update(['status' => $validated['status']]);

        return redirect()->route('admin.dashboard')->with('status', 'El estado se actualizó correctamente.');
    }
}