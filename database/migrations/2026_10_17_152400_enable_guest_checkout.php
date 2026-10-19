<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->json('customer_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('orders')->whereNull('user_id')->exists()) {
            throw new RuntimeException(
                'Không thể rollback khi còn đơn không có tài khoản.'
            );
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->dropColumn('customer_snapshot');
        });
    }
};