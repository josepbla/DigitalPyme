<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'display_name',
        'company_name',
        'content',
        'rating',
        'status',
        'consent_at',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'consent_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}