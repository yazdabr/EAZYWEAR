<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_variant_id',
        'custom_name',
        'custom_number',
        'qty',
        'price',
        'subtotal',
        'weight',
        'stock_deducted_at',
        'stock_restored_at',
        'is_longsleeve',
        'longsleeve_price',
        'is_patch',
        'patch_price',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'weight' => 'integer',
        'stock_deducted_at' => 'datetime',
        'stock_restored_at' => 'datetime',
        'is_longsleeve' => 'boolean',
        'longsleeve_price' => 'integer',
        'is_patch' => 'boolean',
        'patch_price' => 'integer',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }
}