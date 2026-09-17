<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Size extends Model
{
    protected $fillable = [
        'name',
    ];

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'size_id');
    }
    public function productionItems(): HasMany
    {
        return $this->hasMany(
            ProductionItem::class,
            'size_id'
        );
    }
}