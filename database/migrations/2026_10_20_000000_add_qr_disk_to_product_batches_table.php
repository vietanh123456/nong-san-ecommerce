<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table): void {
            $table->string('qr_disk')
                ->default('public')
                ->after('qr_path');
        });
    }

    public function down(): void
    {
        Schema::table('product_batches', function (Blueprint $table): void {
            $table->dropColumn('qr_disk');
        });
    }
};
