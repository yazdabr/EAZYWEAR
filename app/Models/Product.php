<?php

namespace App\Models;

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
        'status',
        'customization_enabled',
        'customization_price',
        'longsleeve_enabled',
        'longsleeve_price',
        'patch_enabled',
        'patch_price',
    ];

    protected $casts = [
        'status' => 'boolean',
        'customization_enabled' => 'boolean',
        'customization_price' => 'integer',
        'longsleeve_enabled' => 'boolean',
        'longsleeve_price' => 'integer',
        'patch_enabled' => 'boolean',
        'patch_price' => 'integer',
    ];
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