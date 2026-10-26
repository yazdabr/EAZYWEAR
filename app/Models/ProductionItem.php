<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionItem extends Model
{
    use HasFactory;

    protected $table = 'production_items';

    protected $fillable = [
        'production_record_id',
        'size_id',
        'quantity',
        'is_longsleeve',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'is_longsleeve' => 'boolean',
    ];

    public function productionRecord(): BelongsTo
    {
        return $this->belongsTo(
            ProductionRecord::class,
            'production_record_id'
        );
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class, 'size_id');
    }
}