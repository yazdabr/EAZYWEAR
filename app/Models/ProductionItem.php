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
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Relasi ke produksi utama
     */
    public function productionRecord(): BelongsTo
    {
        return $this->belongsTo(
            ProductionRecord::class,
            'production_record_id'
        );
    }

    /**
     * Relasi ke ukuran
     */
    public function size(): BelongsTo
    {
        return $this->belongsTo(
            Size::class,
            'size_id'
        );
    }
}