<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::where('email', 'test@example.com')->firstOrFail();

        $category = Category::where('slug', 'trai-cay')->firstOrFail();

        $kilogram = Unit::where('name', 'Kilogram')->firstOrFail();

        $product = Product::updateOrCreate(
            ['name' => 'Bơ Sáp Đắk Lắk 034'],
            [
                'seller_id' => $seller->id,
                'category_id' => $category->id,
                'description' => 'Bơ sáp dẻo ngon, chuẩn đặc sản Đắk Lắk',
                'price' => 45000,
                'stock' => 50,
                'origin' => 'Đắk Lắk',
                'image' => null,
                'status' => true,
            ]
        );

        ProductVariant::updateOrCreate(
            ['sku' => 'BO-SAP-034-1KG'],
            [
                'product_id' => $product->id,
                'unit_id' => $kilogram->id,
                'quantity' => 1,
                'price' => 45000,
                'stock' => 50,
                'status' => true,
            ]
        );
    }
}