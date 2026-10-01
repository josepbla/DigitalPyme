<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiagnosticRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiagnosticRequestController extends Controller
{
    private const STATUS_LABELS = [
        'new' => 'Nueva',
        'reviewing' => 'En revisión',
        'contacted' => 'Contactada',
        'closed' => 'Cerrada',
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUS_LABELS))],
        ]);

        $requests = DiagnosticRequest::query()
            ->with([
                'client:id,name,email,phone,company_name',
                'services:id,name',
            ])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['q'] ?? null, function (Builder $query, string $term): void {
                $like = '%'.$term.'%';

                $query->whereHas('client', function (Builder $clientQuery) use ($like): void {
                    $clientQuery->where(function (Builder $searchQuery) use ($like): void {
                        $searchQuery->where('name', 'like', $like)
                            ->orWhere('email', 'like', $like)
                            ->orWhere('company_name', 'like', $like);
                    });
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.requests.index', [
            'requests' => $requests,
            'filters' => $filters,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }
}