<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kiểm tra hoặc tạo một danh mục mặc định nếu bảng categories đang trống
        $category = Category::first();
        if (!$category) {
            $category = Category::create([
                'name' => 'Nông Sản Chung',
            ]);
        }

        $products = [
            ['name' => 'Táo Bằng Đường Hà Giang', 'price' => 45000],
            ['name' => 'Cam Sành Hà Giang', 'price' => 35000],
            ['name' => 'Xoài Cát Hòa Lộc', 'price' => 65000],
            ['name' => 'Dưa Hấu Long An', 'price' => 20000],
            ['name' => 'Sầu Riêng Ri6', 'price' => 120000],
            ['name' => 'Bưởi Da Xanh Bến Tre', 'price' => 55000],
            ['name' => 'Thanh Long Ruột Đỏ', 'price' => 30000],
            ['name' => 'Chôm Chôm Nhãn', 'price' => 40000],
            ['name' => 'Vải Thiều Bắc Giang', 'price' => 50000],
            ['name' => 'Nhãn Lồng Hưng Yên', 'price' => 45000],
            
            ['name' => 'Rau Cải Ngọt Đà Lạt', 'price' => 15000],
            ['name' => 'Cà Rốt Đà Lạt', 'price' => 25000],
            ['name' => 'Khoai Tây Đà Lạt', 'price' => 30000],
            ['name' => 'Bắp Cải Tươi', 'price' => 18000],
            ['name' => 'Súp Lơ Xanh', 'price' => 35000],
            ['name' => 'Cà Rốt Hữu Cơ', 'price' => 28000],
            ['name' => 'Cà Đĩa Tươi', 'price' => 12000],
            ['name' => 'Cà Choa Sạch', 'price' => 22000],
            ['name' => 'Bầu Sao Tươi Sạch', 'price' => 15000],
            ['name' => 'Bí Đỏ Hồ Lô', 'price' => 20000],

            ['name' => 'Gạo ST25 Ông Thọ', 'price' => 180000],
            ['name' => 'Gạo Tám Xoan Bắc Hương', 'price' => 150000],
            ['name' => 'Đậu Đen Xanh Lòng', 'price' => 45000],
            ['name' => 'Đậu Xanh Bóc Vỏ', 'price' => 50000],
            ['name' => 'Hạt Điều Rang Salt', 'price' => 220000],
            ['name' => 'Hạt Macca Gia Lai', 'price' => 250000],
            ['name' => 'Cà Phê Moka Cầu Đất', 'price' => 160000],
            ['name' => 'Trà Oolong Lâm Đồng', 'price' => 190000],
            ['name' => 'Mật Ong Hoa Cà Phê', 'price' => 130000],
            ['name' => 'Nấm Hương Khô Tây Bắc', 'price' => 95000],
        ];

        foreach ($products as $item) {
            Product::create([
                'category_id' => $category->id,
                'name'        => $item['name'],
                'description' => 'Sản phẩm ' . $item['name'] . ' đảm bảo chất lượng, tươi ngon và an toàn.',
                'price'       => $item['price'],
                'stock'       => rand(10, 100),
            ]);
        }
    }
}