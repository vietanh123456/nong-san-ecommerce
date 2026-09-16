<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Trái cây',
                'slug' => 'trai-cay',
                'description' => 'Các loại trái cây và đặc sản vùng miền',
                'status' => true,
            ],
            [
                'name' => 'Rau củ',
                'slug' => 'rau-cu',
                'description' => 'Các loại rau củ nông sản',
                'status' => true,
            ],
            [
                'name' => 'Gạo và ngũ cốc',
                'slug' => 'gao-va-ngu-coc',
                'description' => 'Gạo, ngũ cốc và các sản phẩm liên quan',
                'status' => true,
            ],
            [
                'name' => 'Mật ong',
                'slug' => 'mat-ong',
                'description' => 'Các sản phẩm mật ong tự nhiên',
                'status' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}