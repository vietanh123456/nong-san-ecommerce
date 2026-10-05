<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SHIPPING = 'shipping';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_STATUS_PAID = 'paid';

    public const PAYMENT_STATUS_UNPAID = 'unpaid';

    protected $fillable = [
        'user_id',
        'address_id',
        'subtotal',
        'shipping_fee',
        'discount',
        'total',
        'payment_method',
        'payment_status',
        'status',
        'note',
        'coupon_id',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Chỉ lấy các đơn hàng được tính vào doanh thu (đã hoàn thành, chưa bị hủy).
     */
    public function scopeRevenueCountable($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }
}
