<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FulfillmentHold extends Model
{
    public const HELD = 'HELD';
    public const CONVERTED = 'CONVERTED';
    public const RELEASED = 'RELEASED';

    protected $fillable = [
        'transaction_id',
        'fulfillment_slot_id',
        'status',
        'released_at',
    ];

    protected function casts(): array
    {
        return [
            'released_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function fulfillmentSlot(): BelongsTo
    {
        return $this->belongsTo(FulfillmentSlot::class);
    }
}