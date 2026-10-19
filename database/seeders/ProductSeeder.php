<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['category' => 'trai-cay', 'name' => 'Táo Bằng Đường Hà Giang', 'price' => 45000],
            ['category' => 'trai-cay', 'name' => 'Cam Sành Hà Giang', 'price' => 35000],
            ['category' => 'trai-cay', 'name' => 'Xoài Cát Hòa Lộc', 'price' => 65000],
            ['category' => 'trai-cay', 'name' => 'Dưa Hấu Long An', 'price' => 20000],
            ['category' => 'trai-cay', 'name' => 'Sầu Riêng Ri6', 'price' => 120000],
            ['category' => 'trai-cay', 'name' => 'Bưởi Da Xanh Bến Tre', 'price' => 55000],
            ['category' => 'trai-cay', 'name' => 'Thanh Long Ruột Đỏ', 'price' => 30000],
            ['category' => 'trai-cay', 'name' => 'Chôm Chôm Nhãn', 'price' => 40000],
            ['category' => 'trai-cay', 'name' => 'Vải Thiều Bắc Giang', 'price' => 50000],
            ['category' => 'trai-cay', 'name' => 'Nhãn Lồng Hưng Yên', 'price' => 45000],

            ['category' => 'rau-cu', 'name' => 'Rau Cải Ngọt Đà Lạt', 'price' => 15000],
            ['category' => 'rau-cu', 'name' => 'Cà Rốt Đà Lạt', 'price' => 25000],
            ['category' => 'rau-cu', 'name' => 'Khoai Tây Đà Lạt', 'price' => 30000],
            ['category' => 'rau-cu', 'name' => 'Bắp Cải Tươi', 'price' => 18000],
            ['category' => 'rau-cu', 'name' => 'Súp Lơ Xanh', 'price' => 35000],
            ['category' => 'rau-cu', 'name' => 'Cà Rốt Hữu Cơ', 'price' => 28000],
            ['category' => 'rau-cu', 'name' => 'Cà Đĩa Tươi', 'price' => 12000],
            ['category' => 'rau-cu', 'name' => 'Cà Choa Sạch', 'price' => 22000],
            ['category' => 'rau-cu', 'name' => 'Bầu Sao Tươi Sạch', 'price' => 15000],
            ['category' => 'rau-cu', 'name' => 'Bí Đỏ Hồ Lô', 'price' => 20000],

            ['category' => 'gao-va-ngu-coc', 'name' => 'Gạo ST25 Ông Thọ', 'price' => 180000],
            ['category' => 'gao-va-ngu-coc', 'name' => 'Gạo Tám Xoan Bắc Hương', 'price' => 150000],
            ['category' => 'gao-va-ngu-coc', 'name' => 'Đậu Đen Xanh Lòng', 'price' => 45000],
            ['category' => 'gao-va-ngu-coc', 'name' => 'Đậu Xanh Bóc Vỏ', 'price' => 50000],
            ['category' => 'hat-va-qua-kho', 'name' => 'Hạt Điều Rang Salt', 'price' => 220000],
            ['category' => 'hat-va-qua-kho', 'name' => 'Hạt Macca Gia Lai', 'price' => 250000],
            ['category' => 'ca-phe-va-tra', 'name' => 'Cà Phê Moka Cầu Đất', 'price' => 160000],
            ['category' => 'ca-phe-va-tra', 'name' => 'Trà Oolong Lâm Đồng', 'price' => 190000],
            ['category' => 'mat-ong', 'name' => 'Mật Ong Hoa Cà Phê', 'price' => 130000],
            ['category' => 'nam', 'name' => 'Nấm Hương Khô Tây Bắc', 'price' => 95000],
        ];

        $categories = Category::query()->get(['id', 'name', 'slug']);
        $categoryIds = $categories->pluck('id', 'slug');
        $missingCategories = collect($products)
            ->pluck('category')
            ->unique()
            ->diff($categoryIds->keys());

        if ($missingCategories->isNotEmpty()) {
            throw new RuntimeException(
                'Missing product categories: '.$missingCategories->implode(', ')
            );
        }

        foreach ($products as $item) {
            $categoryId = $categoryIds->get($item['category']);

            $product = Product::create([
                'category_id' => $categoryId,
                'name' => $item['name'],
                'description' => 'Sản phẩm '.$item['name'].' đảm bảo chất lượng, tươi ngon và an toàn.',
                'price' => $item['price'],
                'stock' => rand(10, 100),
            ]);

            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'search_name' => Product::normalizeSearchText($product->name),
                    'search_description' => Product::normalizeSearchText($product->description ?? ''),
                ]);
        }
    }
}
