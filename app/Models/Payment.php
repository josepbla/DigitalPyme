<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'diagnostic_request_id',
        'return_token',
        'mercadopago_preference_id',
        'mercadopago_payment_id',
        'status',
        'amount',
        'currency',
        'paid_at',
        'mercadopago_created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'mercadopago_created_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function diagnosticRequest(): BelongsTo
    {
        return $this->belongsTo(DiagnosticRequest::class);
    }

    public function clientServices(): HasMany
    {
        return $this->hasMany(ClientService::class);
    }
}