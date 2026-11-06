<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [
        'product_code',
        'category_id',
        'name',
        'slug',
        'description',
        'material',
        'availability',
        'pre_order_closed',
        'ready_stock_closed',
        'status',
        'customization_enabled',
        'customization_price',
        'longsleeve_enabled',
        'longsleeve_price',
        'patch_enabled',
        'patch_price',
    ];

    protected $casts = [
        'pre_order_closed' => 'boolean',
        'ready_stock_closed' => 'boolean',
        'status' => 'boolean',
        'customization_enabled' => 'boolean',
        'customization_price' => 'integer',
        'longsleeve_enabled' => 'boolean',
        'longsleeve_price' => 'integer',
        'patch_enabled' => 'boolean',
        'patch_price' => 'integer',
    ];
    public function scopeOrderedForShop(Builder $query): Builder
    {
        return $query
            ->orderByRaw(
                "CASE
                    WHEN LOWER(name) LIKE ? THEN 1
                    WHEN category_id IN (
                        SELECT id FROM categories WHERE LOWER(name) LIKE ?
                    ) THEN 1
                    WHEN LOWER(name) LIKE ? THEN 2
                    WHEN category_id IN (
                        SELECT id FROM categories WHERE LOWER(name) LIKE ?
                    ) THEN 2
                    WHEN LOWER(name) LIKE ? THEN 3
                    WHEN category_id IN (
                        SELECT id FROM categories WHERE LOWER(name) = ?
                    ) THEN 3
                    ELSE 4
                END",
                [
                    '%stadium%',
                    '%stadium%',
                    '%supporter%',
                    '%supporter%',
                    '%polo%',
                    'polo',
                ]
            )
            ->orderByRaw(
                "CASE
                    WHEN LOWER(name) LIKE ? THEN 1
                    WHEN LOWER(name) LIKE ? THEN 2
                    WHEN LOWER(name) LIKE ? THEN 3
                    ELSE 4
                END",
                ['%home%', '%away%', '%third%']
            )
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
}
