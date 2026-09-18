<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Kiểm tra cột tên của bảng categories (xem là name, title, hay name_category)
        $columns = Schema::getColumnListing('categories');
        $nameColumn = in_array('name', $columns) ? 'name' : (in_array('title', $columns) ? 'title' : $columns[1] ?? 'name');

        // 2. Thêm hoặc lấy ID danh mục
        $category = DB::table('categories')->first();
        if ($category) {
            $categoryId = $category->id;
        } else {
            $categoryId = DB::table('categories')->insertGetId([
                $nameColumn => 'Trái Cây',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 3. Thêm sản phẩm Bơ Sáp
        DB::table('products')->insert([
            'name' => 'Bơ Sáp Đắk Lắk 034',
            'description' => 'Bơ sáp dẻo ngon, chuẩn đặc sản Đắk Lắk',
            'price' => 45000,
            'stock' => 50,
            'category_id' => $categoryId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}