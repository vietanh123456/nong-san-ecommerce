<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            [
                'name' => 'Gram',
                'symbol' => 'g',
                'description' => 'Đơn vị khối lượng gram',
                'status' => true,
            ],
            [
                'name' => 'Kilogram',
                'symbol' => 'kg',
                'description' => 'Đơn vị khối lượng kilogram',
                'status' => true,
            ],
            [
                'name' => 'Mililit',
                'symbol' => 'ml',
                'description' => 'Đơn vị thể tích mililit',
                'status' => true,
            ],
            [
                'name' => 'Lít',
                'symbol' => 'l',
                'description' => 'Đơn vị thể tích lít',
                'status' => true,
            ],
            [
                'name' => 'Chai',
                'symbol' => 'chai',
                'description' => 'Sản phẩm đóng theo chai',
                'status' => true,
            ],
            [
                'name' => 'Hũ',
                'symbol' => 'hũ',
                'description' => 'Sản phẩm đóng theo hũ',
                'status' => true,
            ],
            [
                'name' => 'Lọ',
                'symbol' => 'lọ',
                'description' => 'Sản phẩm đóng theo lọ',
                'status' => true,
            ],
            [
                'name' => 'Hộp',
                'symbol' => 'hộp',
                'description' => 'Sản phẩm đóng theo hộp',
                'status' => true,
            ],
            [
                'name' => 'Gói',
                'symbol' => 'gói',
                'description' => 'Sản phẩm đóng theo gói',
                'status' => true,
            ],
            [
                'name' => 'Túi',
                'symbol' => 'túi',
                'description' => 'Sản phẩm đóng theo túi',
                'status' => true,
            ],
            [
                'name' => 'Bó',
                'symbol' => 'bó',
                'description' => 'Sản phẩm bán theo bó',
                'status' => true,
            ],
            [
                'name' => 'Quả',
                'symbol' => 'quả',
                'description' => 'Nông sản bán theo quả',
                'status' => true,
            ],
            [
                'name' => 'Trái',
                'symbol' => 'trái',
                'description' => 'Nông sản bán theo trái',
                'status' => true,
            ],
            [
                'name' => 'Chiếc',
                'symbol' => 'chiếc',
                'description' => 'Sản phẩm bán theo chiếc',
                'status' => true,
            ],
            [
                'name' => 'Vỉ',
                'symbol' => 'vỉ',
                'description' => 'Sản phẩm đóng theo vỉ',
                'status' => true,
            ],
            [
                'name' => 'Chục',
                'symbol' => 'chục',
                'description' => 'Sản phẩm bán theo chục',
                'status' => true,
            ],
            [
                'name' => 'Bao',
                'symbol' => 'bao',
                'description' => 'Sản phẩm đóng theo bao',
                'status' => true,
            ],
            [
                'name' => 'Thùng',
                'symbol' => 'thùng',
                'description' => 'Sản phẩm đóng theo thùng',
                'status' => true,
            ],
            [
                'name' => 'Combo',
                'symbol' => 'combo',
                'description' => 'Bộ sản phẩm hoặc giỏ quà',
                'status' => true,
            ],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['symbol' => $unit['symbol']],
                $unit
            );
        }
    }
}