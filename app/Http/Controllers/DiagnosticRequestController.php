<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiagnosticRequest;
use App\Models\Client;
use App\Models\DiagnosticRequest;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiagnosticRequestController extends Controller
{
    public function create(Request $request): View
    {
        $requestedServiceId = filter_var($request->query('service'), FILTER_VALIDATE_INT);
        $selectedServiceIds = $requestedServiceId
            ? Service::query()
                ->where('is_active', true)
                ->whereKey($requestedServiceId)
                ->pluck('id')
                ->all()
            : [];

        return view('diagnostics.create', [
            'services' => Service::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'selectedServiceIds' => $selectedServiceIds,
        ]);
    }

    public function store(StoreDiagnosticRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        // Keep the contact, request, and selected services consistent if any save fails.
        $diagnosticRequest = DB::transaction(function () use ($data) {
            $client = Client::query()->firstOrNew([
                'email' => strtolower($data['email']),
            ]);

            $client->fill([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'company_name' => $data['company_name'] ?? null,
                'website' => $data['website'] ?? null,
            ]);
            $client->save();

            $diagnosticRequest = $client->diagnosticRequests()->create([
                'message' => $data['message'] ?? null,
                'privacy_accepted_at' => now(),
            ]);

            $diagnosticRequest->services()->sync($data['services']);

            return $diagnosticRequest;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Recibimos tu solicitud. Nos pondremos en contacto contigo pronto.',
                'request_id' => $diagnosticRequest->id,
            ], 201);
        }

        return to_route('diagnostics.create')
            ->with('success', 'Tu solicitud fue recibida. Nos pondremos en contacto contigo pronto.');
    }
}