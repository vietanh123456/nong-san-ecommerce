<?php

namespace App\Console\Commands;

use App\Models\ProductBatch;
use App\Services\QrCodeStorageService;
use Illuminate\Console\Command;

class RegenerateBatchQrCodes extends Command
{
    protected $signature = 'qr:regenerate
        {--all : Tạo lại QR cho tất cả lô hàng}
        {--batch= : Chỉ tạo lại QR cho một mã lô}';

    protected $description = 'Tạo lại QR với URL truy xuất công khai hiện tại';

    public function handle(QrCodeStorageService $qrCodeStorage): int
    {
        $batchCode = $this->option('batch');

        if (! $this->option('all') && ! $batchCode) {
            $this->error('Dùng --all hoặc --batch=MÃ_LÔ.');

            return self::FAILURE;
        }

        $query = ProductBatch::query()
            ->when(
                $batchCode,
                fn ($query) => $query->where('batch_code', $batchCode)
            );

        $count = 0;

        foreach ($query->cursor() as $batch) {
            $qrCodeStorage->store($batch);
            $this->line('Đã tạo lại QR: '.$batch->batch_code);
            $count++;
        }

        if ($count === 0) {
            $this->warn('Không tìm thấy lô hàng phù hợp.');

            return self::FAILURE;
        }

        $this->info("Hoàn tất: {$count} mã QR.");

        return self::SUCCESS;
    }
}
