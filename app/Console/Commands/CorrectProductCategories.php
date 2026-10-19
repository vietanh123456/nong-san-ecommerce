<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class CorrectProductCategories extends Command
{
    protected $signature = 'products:correct-categories
        {--dry-run : Show proposed category updates without changing data}
        {--apply : Apply the updates and save a rollback CSV}';

    protected $description = 'Correct unambiguous seeded product category assignments';

    public function handle(): int
    {
        if ($this->option('dry-run') && $this->option('apply')) {
            $this->error('Choose either --dry-run or --apply, not both.');

            return self::INVALID;
        }

        $categories = Category::query()->get(['id', 'name', 'slug']);
        $categoryIds = $categories->pluck('id', 'slug');
        $categoriesById = $categories->keyBy('id');
        $assignments = [
            'Rau Cải Ngọt Đà Lạt' => 'rau-cu',
            'Cà Rốt Đà Lạt' => 'rau-cu',
            'Khoai Tây Đà Lạt' => 'rau-cu',
            'Bắp Cải Tươi' => 'rau-cu',
            'Súp Lơ Xanh' => 'rau-cu',
            'Cà Rốt Hữu Cơ' => 'rau-cu',
            'Cà Đĩa Tươi' => 'rau-cu',
            'Cà Choa Sạch' => 'rau-cu',
            'Bầu Sao Tươi Sạch' => 'rau-cu',
            'Bí Đỏ Hồ Lô' => 'rau-cu',
            'Gạo ST25 Ông Thọ' => 'gao-va-ngu-coc',
            'Gạo Tám Xoan Bắc Hương' => 'gao-va-ngu-coc',
            'Đậu Đen Xanh Lòng' => 'gao-va-ngu-coc',
            'Đậu Xanh Bóc Vỏ' => 'gao-va-ngu-coc',
            'Hạt Điều Rang Salt' => 'hat-va-qua-kho',
            'Hạt Macca Gia Lai' => 'hat-va-qua-kho',
            'Cà Phê Moka Cầu Đất' => 'ca-phe-va-tra',
            'Trà Oolong Lâm Đồng' => 'ca-phe-va-tra',
            'Mật Ong Hoa Cà Phê' => 'mat-ong',
            'Nấm Hương Khô Tây Bắc' => 'nam',
        ];
        $plannedUpdates = [];

        foreach ($assignments as $productName => $categorySlug) {
            $products = Product::query()
                ->with('category:id,name,slug')
                ->where('name', $productName)
                ->get();

            if ($products->isEmpty()) {
                continue;
            }

            $categoryId = $categoryIds->get($categorySlug);

            if (! $categoryId) {
                $this->error("Danh mục '{$categorySlug}' chưa tồn tại.");

                return self::FAILURE;
            }

            foreach ($products as $product) {
                if ($product->category_id === $categoryId) {
                    continue;
                }

                $plannedUpdates[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'old_category_id' => $product->category_id,
                    'old_category' => $product->category?->name ?? '',
                    'old_category_slug' => $product->category?->slug ?? '',
                    'new_category_id' => $categoryId,
                    'new_category' => $categoriesById->get($categoryId)->name,
                    'new_category_slug' => $categorySlug,
                ];
            }
        }

        if ($plannedUpdates === []) {
            $this->info('Không có sản phẩm chắc chắn nào cần cập nhật danh mục.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Sản phẩm', 'Danh mục cũ', 'Danh mục mới'],
            array_map(
                fn (array $update): array => [
                    $update['id'],
                    $update['name'],
                    $update['old_category'],
                    $update['new_category'],
                ],
                $plannedUpdates
            )
        );

        if (! $this->option('apply')) {
            $this->comment('Chỉ xem trước; không có dữ liệu nào bị thay đổi. Dùng --apply để cập nhật.');

            return self::SUCCESS;
        }

        $backupPath = 'product-category-corrections/category-corrections-'.now()->format('Ymd-His').'-'.str()->random(8).'.csv';
        $backupDisk = Storage::disk('local');

        if (! $backupDisk->put($backupPath, $this->toCsv($plannedUpdates))) {
            $this->error('Không thể lưu bản ghi hoàn tác; dữ liệu chưa được thay đổi.');

            return self::FAILURE;
        }
        $backupLocation = $backupDisk->path($backupPath);

        try {
            DB::transaction(function () use ($plannedUpdates): void {
                foreach ($plannedUpdates as $update) {
                    $updated = Product::query()
                        ->whereKey($update['id'])
                        ->where('category_id', $update['old_category_id'])
                        ->update(['category_id' => $update['new_category_id']]);

                    if ($updated !== 1) {
                        throw new RuntimeException(
                            "Product #{$update['id']} changed while applying category corrections."
                        );
                    }
                }
            });
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());
            $this->line("Bản ghi đối chiếu vẫn được giữ tại: {$backupLocation}");

            return self::FAILURE;
        }

        $this->info('Đã cập nhật '.count($plannedUpdates).' sản phẩm.');
        $this->line("Bản ghi danh mục cũ/mới: {$backupLocation}");

        return self::SUCCESS;
    }

    private function toCsv(array $updates): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            throw new RuntimeException('Unable to create category correction backup stream.');
        }

        fputcsv($stream, [
            'product_id',
            'product_name',
            'old_category_id',
            'old_category',
            'old_category_slug',
            'new_category_id',
            'new_category',
            'new_category_slug',
        ]);

        foreach ($updates as $update) {
            fputcsv($stream, [
                $update['id'],
                $update['name'],
                $update['old_category_id'],
                $update['old_category'],
                $update['old_category_slug'],
                $update['new_category_id'],
                $update['new_category'],
                $update['new_category_slug'],
            ]);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        if ($csv === false) {
            throw new RuntimeException('Unable to read category correction backup stream.');
        }

        return $csv;
    }
}
