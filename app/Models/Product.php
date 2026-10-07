<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'description',
        'price',
        'stock',
        'origin',
        'image',
        'status',
    ];

    protected $hidden = [
        'search_name',
        'search_description',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $product): void {
            $product->search_name = self::normalizeSearchText($product->name);
            $product->search_description = self::normalizeSearchText($product->description ?? '');
        });
    }

    public function scopeSearch(Builder $query, string $search): void
    {
        $search = '%'.self::normalizeSearchText($search).'%';

        $query->where(function (Builder $query) use ($search): void {
            $query
                ->where('search_name', 'like', $search)
                ->orWhere('search_description', 'like', $search);
        });
    }

    private static function normalizeSearchText(string $text): string
    {
        return mb_strtolower(Str::ascii(trim($text)));
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'status' => 'boolean',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}
