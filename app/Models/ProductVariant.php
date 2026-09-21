<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'unit_id',
        'sku',
        'quantity',
        'price',
        'stock',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'price' => 'decimal:2',
            'stock' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->name) {
            return $this->name;
        }

        if ($this->quantity !== null && $this->unit) {
            $quantity = rtrim(
                rtrim(number_format((float) $this->quantity, 2, '.', ''), '0'),
                '.'
            );

            return trim($quantity . ' ' . $this->unit->symbol);
        }

        return $this->sku;
    }
}