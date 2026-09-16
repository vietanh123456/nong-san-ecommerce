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
                'name' => 'Kilogram',
                'symbol' => 'kg',
                'description' => 'Đơn vị tính theo kilogram',
                'status' => true,
            ],
            [
                'name' => 'Thùng',
                'symbol' => 'thùng',
                'description' => 'Đơn vị đóng gói theo thùng',
                'status' => true,
            ],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['name' => $unit['name']],
                $unit
            );
        }
    }
}