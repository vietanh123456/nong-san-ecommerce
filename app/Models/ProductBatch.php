<?php

namespace App\Models;

use App\Services\QrCodeStorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBatch extends Model
{
    protected $fillable = [
        'product_id', 'batch_code', 'origin', 'producer', 'production_date',
        'harvest_date', 'packaged_date', 'expiry_date', 'qr_code', 'qr_path',
        'qr_disk',
    ];

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'harvest_date' => 'date',
            'packaged_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function getQrUrlAttribute(): ?string
    {
        if (! $this->qr_path) {
            return null;
        }

        return app(QrCodeStorageService::class)->url($this);
    }
}
