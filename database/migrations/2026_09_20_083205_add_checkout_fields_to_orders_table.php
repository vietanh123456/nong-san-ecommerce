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
    Schema::table('orders', function (Blueprint $table) {
        $table->foreignId('user_id')
            ->after('id')
            ->constrained('users')
            ->cascadeOnDelete();

        $table->foreignId('address_id')
            ->nullable()
            ->after('user_id')
            ->constrained('addresses')
            ->nullOnDelete();

        $table->decimal('subtotal', 12, 2)
            ->default(0);

        $table->decimal('shipping_fee', 12, 2)
            ->default(0);

        $table->decimal('discount', 12, 2)
            ->default(0);

        $table->decimal('total', 12, 2)
            ->default(0);

        $table->string('payment_method')
            ->default('cod');

        $table->string('payment_status')
            ->default('unpaid');

        $table->string('status')
            ->default('pending');

        $table->string('note')
            ->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropForeign(['address_id']);
        $table->dropForeign(['user_id']);

        $table->dropColumn([
            'user_id',
            'address_id',
            'subtotal',
            'shipping_fee',
            'discount',
            'total',
            'payment_method',
            'payment_status',
            'status',
            'note',
        ]);
    });
}
};
