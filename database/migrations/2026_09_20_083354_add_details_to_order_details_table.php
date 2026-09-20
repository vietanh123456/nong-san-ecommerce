<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('order_details', function (Blueprint $table) {
        $table->foreignId('order_id')
            ->after('id')
            ->constrained('orders')
            ->cascadeOnDelete();

        $table->foreignId('product_id')
            ->after('order_id')
            ->constrained('products');

        $table->integer('quantity');

        $table->decimal('price', 12, 2);

        $table->decimal('subtotal', 12, 2);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('order_details', function (Blueprint $table) {
        $table->dropForeign(['product_id']);
        $table->dropForeign(['order_id']);

        $table->dropColumn([
            'order_id',
            'product_id',
            'quantity',
            'price',
            'subtotal',
        ]);
    });
}
};
