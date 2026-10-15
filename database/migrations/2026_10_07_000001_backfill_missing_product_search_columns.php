<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->where(function ($query): void {
                $query->whereNull('search_name')
                    ->orWhereNull('search_description');
            })
            ->select(['id', 'name', 'description'])
            ->orderBy('id')
            ->chunkById(500, function ($products): void {
                foreach ($products as $product) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'search_name' => Product::normalizeSearchText($product->name),
                            'search_description' => Product::normalizeSearchText($product->description ?? ''),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Search data can be safely regenerated and is not destructive to roll back.
    }
};
