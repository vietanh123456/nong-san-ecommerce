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
                'user',
                'seller',
                'admin',
                'super_admin',
            ])->default('buyer')->change();
        });
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'user')
            ->update(['role' => 'buyer']);

        DB::table('users')
            ->where('role', 'super_admin')
            ->update(['role' => 'admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', [
                'buyer',
                'seller',
                'admin',
            ])->default('buyer')->change();
        });
    }
};
