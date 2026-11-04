<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEmailOutbox extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_QUEUED = 'queued';

    protected $fillable = [
        'transaction_id',
        'status',
        'attempts',
        'available_at',
        'locked_until',
        'sent_at',
        'last_error',
        'recovery_attempts',
    ];

    protected function casts(): array
    {
        return [
            'available_at' => 'datetime',
            'locked_until' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}