<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('unit_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('sku')->unique();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('stock')->default(0);
            $table->boolean('status')->default(true);

            $table->timestamps();

            $table->unique(
                ['product_id', 'unit_id', 'quantity'],
                'product_unit_quantity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};