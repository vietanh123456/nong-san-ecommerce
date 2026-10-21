<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Services\QrCodeStorageService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Throwable;

class BackfillProductBatchQrCodes extends Command
{
    protected $signature = 'qr:backfill
        {--product= : Chỉ xử lý một ID sản phẩm}
        {--dry-run : Chỉ đếm sản phẩm thiếu lô, không thay đổi dữ liệu}';

    protected $description = 'Tạo lô và QR cho các sản phẩm chưa có lô hàng';

    public function handle(QrCodeStorageService $qrCodeStorage): int
    {
        $productIds = Product::query()
            ->where('status', true)
            ->doesntHave('batches')
            ->when(
                $this->option('product'),
                fn (Builder $query, string $productId): Builder => $query
                    ->whereKey($productId)
            )
            ->orderBy('id')
            ->pluck('id');

        if ($productIds->isEmpty()) {
            $this->info('Không có sản phẩm nào cần tạo lô và QR.');

            return self::SUCCESS;
        }

        $this->info(
            "Tìm thấy {$productIds->count()} sản phẩm chưa có lô hàng."
        );

        if ($this->option('dry-run')) {
            $this->warn('Chỉ xem trước: chưa có dữ liệu nào được thay đổi.');

            return self::SUCCESS;
        }

        $created = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($productIds as $productId) {
            $product = Product::query()
                ->where('status', true)
                ->with('seller:id,name')
                ->find($productId);

            if ($product === null || $product->batches()->exists()) {
                $skipped++;

                continue;
            }

            $batch = null;

            try {
                $batch = $product->batches()->create([
                    'batch_code' => 'AUTO-'.Str::upper((string) Str::ulid()),
                    'origin' => $product->origin ?: 'Đang cập nhật',
                    'producer' => $product->seller?->name,
                    'production_date' => today(),
                ]);

                $qrCodeStorage->store($batch);
                $created++;

                $this->line(
                    "Đã tạo QR cho sản phẩm #{$product->id}: "
                    .$batch->batch_code
                );
            } catch (Throwable $exception) {
                if ($batch instanceof ProductBatch && $batch->exists) {
                    $batch->delete();
                }

                $failed++;
                $this->error(
                    "Sản phẩm #{$product->id} thất bại: "
                    .$exception->getMessage()
                );
            }
        }

        $this->newLine();
        $this->info(
            "Hoàn tất: {$created} đã tạo, {$skipped} bỏ qua, "
            ."{$failed} thất bại."
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
