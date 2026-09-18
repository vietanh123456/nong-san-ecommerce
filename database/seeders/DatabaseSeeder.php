<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $catColumn = 'name';
        if (!Schema::hasColumn('categories', 'name')) {
            $columns = Schema::getColumnListing('categories');
            $catColumn = $columns[1] ?? 'name';
        }

        $catId = DB::table('categories')->insertGetId([
            $catColumn => 'Trái Cây Tươi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $products = [
            ['name' => 'Dâu Tây Giống Nhật', 'price' => 150000, 'description' => 'Dâu tây Đà Lạt ngọt thơm'],
            ['name' => 'Bơ Sáp 034', 'price' => 60000, 'description' => 'Bơ sáp dẻo ngậy Đắc Lắk'],
            ['name' => 'Xoài Cát Hòa Lộc', 'price' => 85000, 'description' => 'Xoài cát Miền Tây ngọt lịm'],
            ['name' => 'Cà Phê Moka Cầu Đất', 'price' => 220000, 'description' => 'Cà phê Moka thơm nức'],
            ['name' => 'Xoài Keo Giòn', 'price' => 35000, 'description' => 'Xoài keo giòn chấm muối ớt'],
        ];

        foreach ($products as $p) {
            Product::create([
                'name' => $p['name'],
                'description' => $p['description'],
                'price' => $p['price'],
                'stock' => 50,
                'category_id' => $catId,
            ]);
        }
    }
}