<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'phone' => '0987654321',
                'password' => 'password',
                'role' => 'seller',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Quản trị viên',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        $this->call([
            CategorySeeder::class,
            UnitSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
