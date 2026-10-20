<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_variants', 'name')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->string('name')
                    ->nullable()
                    ->after('product_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_variants', 'name')) {
            Schema::table('product_variants', function (Blueprint $table): void {
                $table->dropColumn('name');
            });
        }
    }
};
