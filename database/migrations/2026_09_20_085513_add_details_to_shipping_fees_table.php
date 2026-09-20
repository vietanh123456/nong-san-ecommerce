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
    Schema::table('shipping_fees', function (Blueprint $table) {
        $table->foreignId('shipping_zone_id')
            ->after('id')
            ->constrained('shipping_zones')
            ->cascadeOnDelete();

        $table->decimal('fee', 12, 2)
            ->default(0)
            ->after('shipping_zone_id');

        $table->boolean('status')
            ->default(true)
            ->after('fee');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('shipping_fees', function (Blueprint $table) {
        $table->dropForeign(['shipping_zone_id']);

        $table->dropColumn([
            'shipping_zone_id',
            'fee',
            'status',
        ]);
    });
}
};
