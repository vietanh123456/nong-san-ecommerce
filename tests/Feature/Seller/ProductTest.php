<?php

namespace Tests\Feature\Seller;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_create_product_with_units(): void
    {
        Storage::fake('public');

        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $category = $this->createCategory();

        $kilogram = $this->createUnit('Kilogram', 'kg');
        $box = $this->createUnit('Thùng', 'thùng');

        $response = $this
            ->actingAs($seller)
            ->post(route('seller.products.store'), [
                'category_id' => $category->id,
                'name' => 'Cam Cao Phong',
                'description' => 'Cam đặc sản Hòa Bình',
                'origin' => 'Cao Phong, Hòa Bình',
                'status' => 1,
                'image' => UploadedFile::fake()->create(
                    'cam-cao-phong.png',
                    100,
                    'image/png'
                ),
                'variants' => [
                    [
                        'name' => 'Túi 1kg',
                        'unit_id' => $kilogram->id,
                        'sku' => 'CAM-1KG',
                        'quantity' => 1,
                        'price' => 45000,
                        'stock' => 100,
                        'status' => 1,
                    ],
                    [
                        'name' => 'Túi 5kg',
                        'unit_id' => $kilogram->id,
                        'sku' => 'CAM-5KG',
                        'quantity' => 5,
                        'price' => 215000,
                        'stock' => 30,
                        'status' => 1,
                    ],
                    [
                        'name' => 'Thùng 10kg',
                        'unit_id' => $box->id,
                        'sku' => 'CAM-THUNG-10KG',
                        'quantity' => 10,
                        'price' => 420000,
                        'stock' => 20,
                        'status' => 1,
                    ],
                ],
            ]);

        $response->assertRedirect(
            route('seller.products.index')
        );

        $this->assertDatabaseHas('products', [
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Cam Cao Phong',
            'price' => 45000,
            'stock' => 150,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'name' => 'Túi 1kg',
            'sku' => 'CAM-1KG',
            'quantity' => 1,
            'price' => 45000,
            'stock' => 100,
        ]);

        $this->assertDatabaseHas('product_variants', [
            'name' => 'Thùng 10kg',
            'sku' => 'CAM-THUNG-10KG',
            'quantity' => 10,
            'price' => 420000,
            'stock' => 20,
        ]);

        $this->assertDatabaseCount('product_variants', 3);

        $product = Product::where('name', 'Cam Cao Phong')
            ->firstOrFail();

        Storage::disk('public')->assertExists($product->image);
    }

    public function test_seller_can_create_flexible_variant_without_unit(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $category = $this->createCategory();

        $response = $this
            ->actingAs($seller)
            ->post(route('seller.products.store'), [
                'category_id' => $category->id,
                'name' => 'Giỏ quà đặc sản',
                'description' => 'Giỏ quà đặc sản vùng miền.',
                'origin' => 'Việt Nam',
                'status' => 1,
                'variants' => [
                    [
                        'name' => 'Combo quà biếu',
                        'sku' => 'COMBO-QUA-BIEU',
                        'price' => 500000,
                        'stock' => 12,
                        'status' => 1,
                    ],
                ],
            ]);

        $response->assertRedirect(
            route('seller.products.index')
        );

        $this->assertDatabaseHas('product_variants', [
            'name' => 'Combo quà biếu',
            'sku' => 'COMBO-QUA-BIEU',
            'unit_id' => null,
            'quantity' => null,
            'price' => 500000,
            'stock' => 12,
        ]);
    }

    public function test_negative_price_and_stock_are_rejected(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $category = $this->createCategory();
        $unit = $this->createUnit('Kilogram', 'kg');

        $response = $this
            ->actingAs($seller)
            ->post(route('seller.products.store'), [
                'category_id' => $category->id,
                'name' => 'Sản phẩm lỗi',
                'status' => 1,
                'variants' => [
                    [
                        'name' => 'Phân loại không hợp lệ',
                        'unit_id' => $unit->id,
                        'sku' => 'INVALID-01',
                        'quantity' => 1,
                        'price' => -1000,
                        'stock' => -5,
                        'status' => 1,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors([
            'variants.0.price',
            'variants.0.stock',
        ]);

        $this->assertDatabaseMissing('products', [
            'name' => 'Sản phẩm lỗi',
        ]);
    }

    public function test_invalid_image_format_is_rejected(): void
    {
        Storage::fake('public');

        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $category = $this->createCategory();
        $unit = $this->createUnit('Kilogram', 'kg');

        $response = $this
            ->actingAs($seller)
            ->post(route('seller.products.store'), [
                'category_id' => $category->id,
                'name' => 'Cam thử nghiệm',
                'status' => 1,
                'image' => UploadedFile::fake()->create(
                    'malware.exe',
                    100,
                    'application/octet-stream'
                ),
                'variants' => [
                    [
                        'name' => 'Hộp thử nghiệm',
                        'unit_id' => $unit->id,
                        'sku' => 'CAM-TEST',
                        'quantity' => 1,
                        'price' => 45000,
                        'stock' => 10,
                        'status' => 1,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('products', [
            'name' => 'Cam thử nghiệm',
        ]);
    }

    public function test_seller_cannot_update_another_sellers_product(): void
    {
        $owner = User::factory()->create([
            'role' => 'seller',
        ]);

        $otherSeller = User::factory()->create([
            'role' => 'seller',
        ]);

        $category = $this->createCategory();
        $unit = $this->createUnit('Kilogram', 'kg');

        $product = Product::create([
            'seller_id' => $owner->id,
            'category_id' => $category->id,
            'name' => 'Sản phẩm của Seller A',
            'description' => null,
            'price' => 50000,
            'stock' => 10,
            'origin' => 'Việt Nam',
            'status' => true,
        ]);

        $response = $this
            ->actingAs($otherSeller)
            ->put(route('seller.products.update', $product), [
                'category_id' => $category->id,
                'name' => 'Tên bị thay đổi',
                'origin' => 'Việt Nam',
                'status' => 1,
                'variants' => [
                    [
                        'name' => 'Loại tiêu chuẩn',
                        'unit_id' => $unit->id,
                        'sku' => 'OTHER-SELLER-01',
                        'quantity' => 1,
                        'price' => 50000,
                        'stock' => 10,
                        'status' => 1,
                    ],
                ],
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Sản phẩm của Seller A',
        ]);
    }

    private function createCategory(): Category
    {
        return Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
            'description' => null,
            'status' => true,
        ]);
    }

    private function createUnit(
        string $name,
        string $symbol
    ): Unit {
        return Unit::create([
            'name' => $name,
            'symbol' => $symbol,
            'description' => null,
            'status' => true,
        ]);
    }
}