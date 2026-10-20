<?php

namespace App\Services;

use App\Models\ProductBatch;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class QrCodeStorageService
{
    public function store(ProductBatch $batch): void
    {
        $disk = (string) config('filesystems.qr_disk', 'public');
        $path = 'qrcodes/'.$batch->batch_code.'.svg';
        $traceUrl = $this->traceUrl($batch);
        $svg = (new SvgWriter)
            ->write(new QrCode(data: $traceUrl))
            ->getString();

        $stored = false;

        try {
            if ($disk === 'supabase') {
                $this->uploadToSupabase($path, $svg);
                $stored = true;
            } else {
                $stored = Storage::disk($disk)->put($path, $svg, [
                    'ContentType' => 'image/svg+xml',
                    'CacheControl' => 'public, max-age=86400',
                ]);
            }

            if (! $stored) {
                throw new RuntimeException('Không thể lưu mã QR.');
            }

            $batch->update([
                'qr_code' => $traceUrl,
                'qr_path' => $path,
                'qr_disk' => $disk,
            ]);
        } catch (Throwable $exception) {
            if ($stored) {
                $this->deletePath($disk, $path);
            }

            throw $exception;
        }
    }

    public function delete(ProductBatch $batch): void
    {
        if (! $batch->qr_path) {
            return;
        }

        $this->deletePath(
            $batch->qr_disk ?: 'public',
            $batch->qr_path
        );
    }

    public function replace(
        ProductBatch $batch,
        ?string $oldPath,
        ?string $oldDisk
    ): void {
        $this->store($batch);

        if (
            $oldPath
            && ($oldPath !== $batch->qr_path || $oldDisk !== $batch->qr_disk)
        ) {
            $this->deletePath($oldDisk ?: 'public', $oldPath);
        }
    }

    public function url(ProductBatch $batch): string
    {
        if (($batch->qr_disk ?: 'public') !== 'supabase') {
            return Storage::disk($batch->qr_disk ?: 'public')
                ->url($batch->qr_path);
        }

        return $this->supabaseObjectUrl($batch->qr_path, true);
    }

    private function uploadToSupabase(string $path, string $contents): void
    {
        $this->supabaseRequest()
            ->withHeaders([
                'cache-control' => 'public, max-age=86400',
                'x-upsert' => 'true',
            ])
            ->withBody($contents, 'image/svg+xml')
            ->post($this->supabaseObjectUrl($path))
            ->throw();
    }

    private function deletePath(string $disk, string $path): void
    {
        if ($disk === 'supabase') {
            $this->supabaseRequest()
                ->delete($this->supabaseObjectUrl($path))
                ->throw();

            return;
        }

        Storage::disk($disk)->delete($path);
    }

    private function supabaseRequest(): PendingRequest
    {
        $secretKey = (string) config('services.supabase.secret_key');

        if ($secretKey === '') {
            throw new RuntimeException('SUPABASE_SECRET_KEY chưa được cấu hình.');
        }

        return Http::withHeaders([
            'apikey' => $secretKey,
        ])->timeout(15)->retry(2, 200);
    }

    private function traceUrl(ProductBatch $batch): string
    {
        $publicUrl = rtrim(
            (string) config('traceability.public_url'),
            '/'
        );

        if ($publicUrl === '') {
            throw new RuntimeException('TRACE_PUBLIC_URL chưa được cấu hình.');
        }

        return $publicUrl.route(
            'trace.show',
            $batch->batch_code,
            false
        );
    }

    private function supabaseObjectUrl(
        string $path,
        bool $public = false
    ): string {
        $baseUrl = rtrim((string) config('services.supabase.url'), '/');
        $bucket = (string) config('services.supabase.bucket');

        if ($baseUrl === '' || $bucket === '') {
            throw new RuntimeException(
                'SUPABASE_URL hoặc SUPABASE_BUCKET chưa được cấu hình.'
            );
        }

        $objectPath = implode('/', array_map(
            'rawurlencode',
            explode('/', trim($path, '/'))
        ));
        $visibility = $public ? '/public' : '';

        return $baseUrl
            .'/storage/v1/object'
            .$visibility
            .'/'.rawurlencode($bucket)
            .'/'.$objectPath;
    }
}
