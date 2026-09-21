<?php

namespace App\Console\Commands;

use App\Models\Certificate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SecureCertificateFiles extends Command
{
    protected $signature = 'traceability:secure-certificates';

    protected $description = 'Move legacy certificate files from public storage to private storage';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $moved = 0;
        $skipped = 0;

        Certificate::query()->select(['id', 'file_path'])->chunkById(
            100,
            function ($certificates) use ($public, $private, &$moved, &$skipped): void {
                foreach ($certificates as $certificate) {
                    if ($private->exists($certificate->file_path)) {
                        $skipped++;

                        continue;
                    }

                    if (! $public->exists($certificate->file_path)) {
                        $skipped++;

                        continue;
                    }

                    $stream = $public->readStream($certificate->file_path);

                    if ($stream === false) {
                        $this->warn("Không thể đọc file chứng nhận #{$certificate->id}.");
                        $skipped++;

                        continue;
                    }

                    $private->writeStream($certificate->file_path, $stream);
                    fclose($stream);

                    if (! $private->exists($certificate->file_path)) {
                        $this->error("Không thể lưu file chứng nhận #{$certificate->id} vào private storage.");
                        $skipped++;

                        continue;
                    }

                    $public->delete($certificate->file_path);
                    $moved++;
                }
            }
        );

        $this->info("Đã chuyển {$moved} file; bỏ qua {$skipped} file.");

        return self::SUCCESS;
    }
}
