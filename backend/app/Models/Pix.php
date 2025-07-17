<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pix extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'status',
        'amount',
        'expires_at',
        'paid_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'paid_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now()
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update([
            'status' => 'expired'
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
