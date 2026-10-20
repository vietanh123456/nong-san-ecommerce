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
                'description' => 'Trái cây tươi và đặc sản trái cây vùng miền.',
                'status' => true,
            ],
            [
                'name' => 'Rau củ',
                'slug' => 'rau-cu',
                'description' => 'Rau xanh, củ, quả và nông sản tươi.',
                'status' => true,
            ],
            [
                'name' => 'Gạo và ngũ cốc',
                'slug' => 'gao-va-ngu-coc',
                'description' => 'Gạo, nếp, ngô, yến mạch và các loại ngũ cốc.',
                'status' => true,
            ],
            [
                'name' => 'Cà phê và trà',
                'slug' => 'ca-phe-va-tra',
                'description' => 'Cà phê, trà xanh, trà thảo mộc và trà đặc sản.',
                'status' => true,
            ],
            [
                'name' => 'Mật ong',
                'slug' => 'mat-ong',
                'description' => 'Mật ong tự nhiên và các sản phẩm từ mật ong.',
                'status' => true,
            ],
            [
                'name' => 'Gia vị và đặc sản',
                'slug' => 'gia-vi-va-dac-san',
                'description' => 'Gia vị, nước chấm và đặc sản địa phương.',
                'status' => true,
            ],
            [
                'name' => 'Hạt và quả khô',
                'slug' => 'hat-va-qua-kho',
                'description' => 'Các loại hạt dinh dưỡng và quả sấy khô.',
                'status' => true,
            ],
            [
                'name' => 'Thực phẩm khô',
                'slug' => 'thuc-pham-kho',
                'description' => 'Mì, miến, măng khô và thực phẩm khô đóng gói.',
                'status' => true,
            ],
            [
                'name' => 'Nấm',
                'slug' => 'nam',
                'description' => 'Nấm tươi, nấm khô và các sản phẩm từ nấm.',
                'status' => true,
            ],
            [
                'name' => 'Bánh và đặc sản',
                'slug' => 'banh-va-dac-san',
                'description' => 'Bánh truyền thống và đặc sản các vùng miền.',
                'status' => true,
            ],
            [
                'name' => 'Thủy hải sản',
                'slug' => 'thuy-hai-san',
                'description' => 'Thủy sản, hải sản tươi và sản phẩm chế biến.',
                'status' => true,
            ],
            [
                'name' => 'Thịt và sản phẩm chế biến',
                'slug' => 'thit-va-san-pham-che-bien',
                'description' => 'Thịt, giò chả, xúc xích và thực phẩm chế biến.',
                'status' => true,
            ],
            [
                'name' => 'Đồ uống',
                'slug' => 'do-uong',
                'description' => 'Nước ép, đồ uống thảo mộc và thức uống đóng chai.',
                'status' => true,
            ],
            [
                'name' => 'Sản phẩm hữu cơ',
                'slug' => 'san-pham-huu-co',
                'description' => 'Nông sản và thực phẩm được sản xuất theo hướng hữu cơ.',
                'status' => true,
            ],
            [
                'name' => 'Quà biếu và combo',
                'slug' => 'qua-bieu-va-combo',
                'description' => 'Hộp quà, giỏ quà và combo nông sản.',
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

