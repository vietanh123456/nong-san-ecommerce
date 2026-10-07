<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->text('search_name')->nullable();
            $table->text('search_description')->nullable();
        });

        DB::table('products')
            ->select(['id', 'name', 'description'])
            ->orderBy('id')
            ->chunkById(500, function ($products): void {
                foreach ($products as $product) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'search_name' => mb_strtolower(Str::ascii(trim($product->name))),
                            'search_description' => mb_strtolower(
                                Str::ascii(trim($product->description ?? ''))
                            ),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['search_name', 'search_description']);
        });
    }
};
