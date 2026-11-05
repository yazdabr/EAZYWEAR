<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionNotification extends Model
{
    protected $fillable = [
        'transaction_id',
        'type',
        'sent_at',
        'status',
        'attempts',
        'recovery_attempts',
        'available_at',
        'locked_until',
        'last_error',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'available_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
