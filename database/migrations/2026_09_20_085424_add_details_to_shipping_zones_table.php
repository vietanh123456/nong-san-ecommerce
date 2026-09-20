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
    Schema::table('shipping_zones', function (Blueprint $table) {
        $table->string('name')->after('id');

        $table->string('province')
            ->nullable()
            ->after('name');

        $table->boolean('status')
            ->default(true)
            ->after('province');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('shipping_zones', function (Blueprint $table) {
        $table->dropColumn([
            'name',
            'province',
            'status',
        ]);
    });
}
};
