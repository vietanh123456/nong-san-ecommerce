<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CorrectProductCategoriesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_correction_dry_run_does_not_modify_products(): void
    {
        $fruit = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
            'status' => true,
        ]);
        Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu',
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $fruit->id,
            'name' => 'Rau Cải Ngọt Đà Lạt',
            'price' => 10000,
            'stock' => 5,
            'status' => false,
        ]);

        $this->artisan('products:correct-categories --dry-run')
            ->expectsTable(
                ['ID', 'Sản phẩm', 'Danh mục cũ', 'Danh mục mới'],
                [[$product->id, $product->name, 'Trái cây', 'Rau củ']]
            )
            ->expectsOutputToContain('Chỉ xem trước')
            ->assertExitCode(Command::SUCCESS);

        $this->assertSame($fruit->id, $product->fresh()->category_id);
    }

    public function test_category_correction_apply_saves_old_and_new_category_ids(): void
    {
        Storage::fake('local');

        $fruit = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
            'status' => true,
        ]);
        $vegetables = Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu',
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $fruit->id,
            'name' => 'Rau Cải Ngọt Đà Lạt',
            'price' => 10000,
            'stock' => 5,
            'status' => false,
        ]);

        $this->artisan('products:correct-categories --apply')
            ->expectsOutputToContain('Đã cập nhật 1 sản phẩm.')
            ->assertExitCode(Command::SUCCESS);

        $this->assertSame($vegetables->id, $product->fresh()->category_id);
        $backupFiles = Storage::disk('local')->allFiles('product-category-corrections');
        $this->assertCount(1, $backupFiles);
        $backup = Storage::disk('local')->get($backupFiles[0]);
        $backupRows = array_map(
            'str_getcsv',
            preg_split('/\r\n|\n|\r/', trim($backup))
        );
        $this->assertSame(
            ['product_id', 'product_name', 'old_category_id', 'old_category', 'old_category_slug', 'new_category_id', 'new_category', 'new_category_slug'],
            $backupRows[0]
        );
        $this->assertSame(
            [(string) $product->id, $product->name, (string) $fruit->id, 'Trái cây', 'trai-cay', (string) $vegetables->id, 'Rau củ', 'rau-cu'],
            $backupRows[1]
        );
        $this->assertSame(10000, (int) $product->fresh()->price);
        $this->assertSame(5, $product->fresh()->stock);
    }
}
