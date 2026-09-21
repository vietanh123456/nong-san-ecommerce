<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_batches', function (Blueprint $table) {
            $table->date('harvest_date')->nullable()->after('production_date');
            $table->date('packaged_date')->nullable()->after('harvest_date');
            $table->string('qr_path')->nullable()->after('qr_code');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('product_batch_id')->after('id')
                ->constrained()->cascadeOnDelete();
            $table->string('name')->after('product_batch_id');
            $table->string('file_path')->after('name');
            $table->string('status')->default('pending')->after('file_path');
            $table->text('admin_note')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->after('admin_note')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['product_batch_id']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'product_batch_id', 'name', 'file_path', 'status',
                'admin_note', 'approved_by', 'approved_at',
            ]);
        });

        Schema::table('product_batches', function (Blueprint $table) {
            $table->dropColumn(['harvest_date', 'packaged_date', 'qr_path']);
        });
    }
};
