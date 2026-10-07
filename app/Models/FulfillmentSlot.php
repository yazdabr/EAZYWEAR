<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FulfillmentSlot extends Model
{
    protected $fillable = [
        'date',
        'capacity',
        'used_count',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'capacity' => 'integer',
            'used_count' => 'integer',
        ];
    }
    public function fulfillmentHolds(): HasMany
    {
        return $this->hasMany(FulfillmentHold::class);
    }
}