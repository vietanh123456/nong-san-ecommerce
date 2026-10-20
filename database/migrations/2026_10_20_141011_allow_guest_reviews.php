<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table): void {
            $table->string('guest_name')
                ->nullable()
                ->after('user_id');

            $table->unsignedBigInteger('user_id')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('reviews')
            ->whereNull('user_id')
            ->delete();

        Schema::table('reviews', function (Blueprint $table): void {
            $table->dropColumn('guest_name');

            $table->unsignedBigInteger('user_id')
                ->nullable(false)
                ->change();
        });
    }
};
