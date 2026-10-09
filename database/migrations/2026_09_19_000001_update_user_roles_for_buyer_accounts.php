<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'buyer',
                'seller',
                'admin',
                'customer',
            ])->default('buyer')->change();
        });

        DB::table('users')
            ->where('role', 'customer')
            ->update(['role' => 'buyer']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'buyer',
                'seller',
                'admin',
            ])->default('buyer')->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'buyer',
                'customer',
                'seller',
                'admin',
            ])->default('buyer')->change();
        });

        DB::table('users')
            ->where('role', 'buyer')
            ->update(['role' => 'customer']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'customer',
                'seller',
                'admin',
            ])->default('customer')->change();
        });
    }
};
