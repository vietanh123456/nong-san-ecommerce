<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $products = [
            'Xoài Cát Hòa Lộc', 'Bơ Sáp Đắk Lắk', 'Cà Rốt Đà Lạt', 'Táo Bàng La',
            'Sầu Riêng Ri6', 'Dưa Hấu Long An', 'Cam Sành Vĩnh Long', 'Thanh Long Bình Thuận',
            'Vải Thiều Bắc Giang', 'Nhãn Lồng Hưng Yên', 'Bưởi Da Xanh', 'Măng Cụt Bến Tre'
        ];

        return [
            'name' => $this->faker->randomElement($products) . ' ' . $this->faker->numberBetween(1, 100),
            'description' => 'Sản phẩm nông sản đạt chuẩn VietGAP tươi ngon chất lượng cao.',
            'price' => $this->faker->numberBetween(20, 200) * 1000,
            'stock' => $this->faker->numberBetween(10, 100),
        ];
    }
}