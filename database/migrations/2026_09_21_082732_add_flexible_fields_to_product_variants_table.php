<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Tạo index hỗ trợ khóa ngoại product_id trước.
         * MySQL cần index này trước khi unique index cũ bị xóa.
         */
        Schema::table('product_variants', function (Blueprint $table): void {
            $table->index(
                ['product_id', 'status'],
                'product_variants_product_status_index'
            );
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropUnique('product_unit_quantity_unique');

            $table->string('name')
                ->nullable()
                ->after('product_id');

            $table->foreignId('unit_id')
                ->nullable()
                ->change();

            $table->decimal('quantity', 10, 2)
                ->nullable()
                ->default(null)
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table): void {
            $table->decimal('quantity', 10, 2)
                ->nullable(false)
                ->default(1)
                ->change();

            $table->foreignId('unit_id')
                ->nullable(false)
                ->change();

            $table->dropColumn('name');

            $table->unique(
                ['product_id', 'unit_id', 'quantity'],
                'product_unit_quantity_unique'
            );
        });

        Schema::table('product_variants', function (Blueprint $table): void {
            $table->dropIndex(
                'product_variants_product_status_index'
            );
        });
    }
};