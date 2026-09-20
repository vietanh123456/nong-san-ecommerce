<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();

            $table->enum('type', [
                'percent',
                'fixed'
            ]);

            $table->decimal('value', 12, 2);

            $table->decimal('min_order', 12, 2)
                ->default(0);

            $table->decimal('max_discount', 12, 2)
                ->nullable();

            $table->unsignedInteger('usage_limit')
                ->nullable();

            $table->unsignedInteger('used_count')
                ->default(0);

            $table->dateTime('start_date')
                ->nullable();

            $table->dateTime('end_date')
                ->nullable();

            $table->boolean('status')
                ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};