<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->unique()->after('id');
            $table->string('slug')->unique()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->boolean('status')->default(true)->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'slug',
                'description',
                'status',
            ]);
        });
    }
};