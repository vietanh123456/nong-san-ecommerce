<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackfillProductBatchQrCodesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_batches_and_qr_only_for_products_without_batches(): void
    {
        Storage::fake('public');
        config([
            'filesystems.qr_disk' => 'public',
            'traceability.public_url' => 'https://nong-san.example',
        ]);

        $seller = User::factory()->create([
            'name' => 'Nông trại Xanh',
            'role' => 'seller',
        ]);
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
            'status' => true,
        ]);
        $missingBatch = $this->productFor($seller, $category, 'Cam Cao Phong');
        $hiddenProduct = $this->productFor(
            $seller,
            $category,
            'Sản phẩm đã ẩn'
        );
        $hiddenProduct->update(['status' => false]);

        $existingBatch = $this->productFor(
            $seller,
            $category,
            'Bưởi Da Xanh'
        );
        ProductBatch::create([
            'product_id' => $existingBatch->id,
            'batch_code' => 'LO-DA-CO',
            'origin' => 'Bến Tre',
            'production_date' => '2026-10-01',
        ]);

        $this->artisan('qr:backfill')
            ->expectsOutputToContain('Tìm thấy 1 sản phẩm chưa có lô hàng.')
            ->expectsOutputToContain('Hoàn tất: 1 đã tạo, 0 bỏ qua, 0 thất bại.')
            ->assertExitCode(Command::SUCCESS);

        $batch = $missingBatch->batches()->firstOrFail();

        $this->assertStringStartsWith('AUTO-', $batch->batch_code);
        $this->assertSame('Hòa Bình', $batch->origin);
        $this->assertSame('Nông trại Xanh', $batch->producer);
        $this->assertSame('public', $batch->qr_disk);
        $this->assertSame(
            "https://nong-san.example/trace/{$batch->batch_code}",
            $batch->qr_code
        );
        Storage::disk('public')->assertExists($batch->qr_path);
        $this->assertSame(1, $existingBatch->batches()->count());
        $this->assertSame(0, $hiddenProduct->batches()->count());

        $this->artisan('qr:backfill')
            ->expectsOutput('Không có sản phẩm nào cần tạo lô và QR.')
            ->assertExitCode(Command::SUCCESS);

        $this->assertSame(2, ProductBatch::count());
    }

    public function test_dry_run_does_not_create_batches_or_qr_files(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => 'seller']);
        $category = Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu',
            'status' => true,
        ]);
        $this->productFor($seller, $category, 'Cà rốt Đà Lạt');

        $this->artisan('qr:backfill --dry-run')
            ->expectsOutputToContain('Tìm thấy 1 sản phẩm chưa có lô hàng.')
            ->expectsOutputToContain(
                'Chỉ xem trước: chưa có dữ liệu nào được thay đổi.'
            )
            ->assertExitCode(Command::SUCCESS);

        $this->assertSame(0, ProductBatch::count());
        Storage::disk('public')->assertDirectoryEmpty('qrcodes');
    }

    private function productFor(
        User $seller,
        Category $category,
        string $name
    ): Product {
        return Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => $name,
            'price' => 10000,
            'stock' => 10,
            'origin' => 'Hòa Bình',
            'status' => true,
        ]);
    }
}
