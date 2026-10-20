<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSellerSeeder extends Seeder
{
    public function run(): void
    {
        $shops = [
            [
                'name' => 'Nông Trại Xanh Đà Lạt',
                'email' => 'nongtraixanh@example.com',
                'phone' => '0901000001',
                'password' => 'Shop@12345',
            ],
            [
                'name' => 'Đặc Sản Việt',
                'email' => 'dacsanviet@example.com',
                'phone' => '0901000002',
                'password' => 'Shop@12345',
            ],
            [
                'name' => 'Nông Sản Miền Tây',
                'email' => 'nongsanmientay@example.com',
                'phone' => '0901000003',
                'password' => 'Shop@12345',
            ],
            [
                'name' => 'Nông Sản An Nhiên',
                'email' => 'seller@example.com',
                'phone' => '0901000004',
                'password' => 'Test@12345',
            ],
        ];

        foreach ($shops as $shop) {
            User::updateOrCreate(
                ['email' => $shop['email']],
                [
                    'name' => $shop['name'],
                    'phone' => $shop['phone'],
                    'password' => Hash::make($shop['password']),
                    'role' => 'seller',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
