<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Size extends Model
{
    protected $fillable = [
        'name',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderByRaw("
            CASE UPPER(TRIM(name))
                WHEN '3XS' THEN 1
                WHEN '2XS' THEN 2
                WHEN 'XXS' THEN 3
                WHEN 'XS' THEN 4
                WHEN 'S' THEN 5
                WHEN 'M' THEN 6
                WHEN 'L' THEN 7
                WHEN 'XL' THEN 8
                WHEN '2XL' THEN 9
                WHEN 'XXL' THEN 9
                WHEN '3XL' THEN 10
                WHEN '4XL' THEN 11
                WHEN '5XL' THEN 12
                WHEN '6XL' THEN 13
                ELSE 100
            END
        ")->orderBy('name');
    }

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