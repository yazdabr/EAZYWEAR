<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Inventory;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'size_id',
        'color_id',
        'sku',
        'price',
        'weight',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'weight' => 'integer',
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class, 'product_variant_id');
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'product_variant_id');
    }

    public function hasTransactionHistory(): bool
    {
        return $this->transactionItems()->exists();
    }

    public function hasStockHistory(): bool
    {
        return $this->inventory?->stockMovements()->exists() ?? false;
    }
}
