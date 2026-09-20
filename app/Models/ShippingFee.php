<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingFee extends Model
{
    protected $fillable = [
        'shipping_zone_id',
        'fee',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'status' => 'boolean',
        ];
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }
}