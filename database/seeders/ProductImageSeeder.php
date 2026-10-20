<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $imageDirectory = base_path('product-images');

        if (!is_dir($imageDirectory)) {
            throw new RuntimeException(
                "Không tìm thấy thư mục ảnh: {$imageDirectory}"
            );
        }

        $imageFiles = collect(
            glob($imageDirectory.DIRECTORY_SEPARATOR.'*.webp') ?: []
        )->mapWithKeys(function (string $path): array {
            $stem = Str::upper(
                pathinfo($path, PATHINFO_FILENAME)
            );

            return [$stem => $path];
        });

        if ($imageFiles->isEmpty()) {
            throw new RuntimeException(
                'Không tìm thấy ảnh WEBP trong thư mục product-images.'
            );
        }

        /*
         * Những sản phẩm cũ chưa có SKU được ghép bằng tên sản phẩm.
         */
        $manualImages = [
            'Cam Cao Phong' => 'PRODUCT-11',
            'Cà phê Buôn Ma Thuột' => 'PRODUCT-14',
            'Mật ong bạc hà Hà Giang' => 'PRODUCT-15',
            'Nước mắm Phú Quốc' => 'PRODUCT-16',
        ];

        /*
         * Tên ảnh này có thêm chữ L so với SKU trong cơ sở dữ liệu.
         */
        $stemAliases = [
            'DSV-NUOCMAML' => 'DSV-NUOCMAM',
        ];

        $products = Product::query()
            ->with('variants')
            ->orderBy('id')
            ->get();

        $assignments = collect();
        $missingProducts = collect();

        foreach ($products as $product) {
            $matchedStem = $manualImages[$product->name] ?? null;

            if ($matchedStem === null) {
                $sku = Str::upper(
                    (string) $product->variants->first()?->sku
                );

                if ($sku !== '') {
                    $matchedStem = $imageFiles
                        ->keys()
                        ->first(function (string $stem) use (
                            $sku,
                            $stemAliases
                        ): bool {
                            $comparisonStem = $stemAliases[$stem]
                                ?? $stem;

                            return $sku === $comparisonStem
                                || Str::startsWith(
                                    $sku,
                                    $comparisonStem.'-'
                                );
                        });
                }
            }

            if (
                $matchedStem === null
                || !$imageFiles->has($matchedStem)
            ) {
                $missingProducts->push(
                    "{$product->id}: {$product->name}"
                );

                continue;
            }

            $assignments->put($product->id, [
                'product' => $product,
                'stem' => $matchedStem,
                'source' => $imageFiles->get($matchedStem),
            ]);
        }

        if ($missingProducts->isNotEmpty()) {
            throw new RuntimeException(
                'Chưa tìm thấy ảnh cho sản phẩm: '
                .$missingProducts->implode('; ')
            );
        }

        if ($assignments->count() !== $products->count()) {
            throw new RuntimeException(
                'Số ảnh đã ghép không bằng số sản phẩm.'
            );
        }

        $duplicateStems = $assignments
            ->pluck('stem')
            ->duplicates();

        if ($duplicateStems->isNotEmpty()) {
            throw new RuntimeException(
                'Ảnh bị ghép trùng: '
                .$duplicateStems->implode(', ')
            );
        }

        $unusedImages = $imageFiles
            ->keys()
            ->diff($assignments->pluck('stem'));

        if ($unusedImages->isNotEmpty()) {
            throw new RuntimeException(
                'Ảnh chưa được sử dụng: '
                .$unusedImages->implode(', ')
            );
        }

        $disk = Storage::disk('supabase');
        $uploadedCount = 0;

        foreach ($assignments as $assignment) {
            /** @var Product $product */
            $product = $assignment['product'];
            $source = $assignment['source'];

            $remotePath = 'products/catalog/'
                .Str::lower($assignment['stem'])
                .'.webp';

            $stream = fopen($source, 'rb');

            if ($stream === false) {
                throw new RuntimeException(
                    "Không thể đọc ảnh: {$source}"
                );
            }

            try {
                $uploaded = $disk->put(
                    $remotePath,
                    $stream,
                    [
                        'visibility' => 'public',
                        'ContentType' => 'image/webp',
                        'CacheControl' => 'public, max-age=31536000',
                    ]
                );
            } finally {
                fclose($stream);
            }

            if (!$uploaded) {
                throw new RuntimeException(
                    "Không thể tải ảnh cho sản phẩm #{$product->id}"
                );
            }

            $oldImage = $product->image;

            $product->update([
                'image' => $remotePath,
            ]);

            if (
                $oldImage
                && $oldImage !== $remotePath
                && (
                    Str::startsWith($oldImage, 'products/demo/')
                    || Str::startsWith(
                        $oldImage,
                        'products/catalog/'
                    )
                )
            ) {
                $disk->delete($oldImage);
            }

            $uploadedCount++;
        }

        $this->command?->info(
            "Đã tải {$uploadedCount} ảnh thật lên Supabase."
        );
    }
}