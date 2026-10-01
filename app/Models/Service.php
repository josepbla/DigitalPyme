<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    public function diagnosticRequests(): BelongsToMany
    {
        return $this->belongsToMany(DiagnosticRequest::class)
            ->withTimestamps();
    }

    public function clientServices(): HasMany
    {
        return $this->hasMany(ClientService::class);
    }
}