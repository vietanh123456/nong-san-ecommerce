<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'seller_request_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('seller_request_status')->nullable()->after('role');
            });
        }

        DB::table('users')->where('role', 'seller_pending')->update([
            'role' => 'customer',
            'seller_request_status' => 'pending',
        ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'seller_request_status')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('seller_request_status');
            });
        }
    }
};
