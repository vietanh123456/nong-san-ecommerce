<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VariantImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_upload_variant_image(): void
    {
        Storage::fake('public');

        [$seller, $category] = $this->createSellerAndCategory();

        $response = $this
            ->actingAs($seller)
            ->post(route('seller.products.store'), [
                'category_id' => $category->id,
                'name' => 'Mật ong rừng',
                'description' => 'Mật ong đặc sản.',
                'origin' => 'Tây Bắc',
                'status' => 1,
                'variants' => [
                    [
                        'name' => 'Hũ 300g',
                        'image' => UploadedFile::fake()->create(
                            'hu-300g.jpg',
                            100,
                            'image/jpeg'
                        ),
                        'sku' => 'MATONG-HU300',
                        'price' => 120000,
                        'stock' => 10,
                        'status' => 1,
                    ],
                ],
            ]);

        $response->assertRedirect(
            route('seller.products.index')
        );

        $variant = ProductVariant::query()
            ->where('sku', 'MATONG-HU300')
            ->firstOrFail();

        $this->assertNotNull($variant->image);

        Storage::disk('public')
            ->assertExists($variant->image);
    }

    public function test_invalid_variant_image_is_rejected(): void
    {
        Storage::fake('public');

        [$seller, $category] = $this->createSellerAndCategory();

        $response = $this
            ->actingAs($seller)
            ->post(route('seller.products.store'), [
                'category_id' => $category->id,
                'name' => 'Mật ong lỗi',
                'status' => 1,
                'variants' => [
                    [
                        'name' => 'Hũ thử nghiệm',
                        'image' => UploadedFile::fake()->create(
                            'malware.exe',
                            100,
                            'application/octet-stream'
                        ),
                        'sku' => 'MATONG-INVALID',
                        'price' => 120000,
                        'stock' => 10,
                        'status' => 1,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors(
            'variants.0.image'
        );

        $this->assertDatabaseMissing('products', [
            'name' => 'Mật ong lỗi',
        ]);
    }

    public function test_seller_can_replace_variant_image(): void
    {
        Storage::fake('public');

        [$seller, $category] = $this->createSellerAndCategory();

        [$product, $variant] = $this->createProductAndVariant(
            $seller,
            $category
        );

        Storage::disk('public')->put(
            'product-variants/old.jpg',
            'old image'
        );

        $variant->update([
            'image' => 'product-variants/old.jpg',
        ]);

        $response = $this
            ->actingAs($seller)
            ->put(
                route('seller.products.update', $product),
                [
                    'category_id' => $category->id,
                    'name' => $product->name,
                    'origin' => 'Tây Bắc',
                    'status' => 1,
                    'variants' => [
                        [
                            'id' => $variant->id,
                            'name' => $variant->name,
                            'image' => UploadedFile::fake()->create(
                                'new.jpg',
                                100,
                                'image/jpeg'
                            ),
                            'sku' => $variant->sku,
                            'price' => 120000,
                            'stock' => 10,
                            'status' => 1,
                        ],
                    ],
                ]
            );

        $response->assertRedirect(
            route('seller.products.index')
        );

        $variant->refresh();

        $this->assertNotSame(
            'product-variants/old.jpg',
            $variant->image
        );

        Storage::disk('public')->assertMissing(
            'product-variants/old.jpg'
        );

        Storage::disk('public')->assertExists(
            $variant->image
        );
    }

    public function test_deleting_product_removes_variant_image(): void
    {
        Storage::fake('public');

        [$seller, $category] = $this->createSellerAndCategory();

        [$product, $variant] = $this->createProductAndVariant(
            $seller,
            $category
        );

        Storage::disk('public')->put(
            'product-variants/delete.jpg',
            'image'
        );

        $variant->update([
            'image' => 'product-variants/delete.jpg',
        ]);

        $response = $this
            ->actingAs($seller)
            ->delete(
                route('seller.products.destroy', $product)
            );

        $response->assertRedirect(
            route('seller.products.index')
        );

        Storage::disk('public')->assertMissing(
            'product-variants/delete.jpg'
        );

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    private function createSellerAndCategory(): array
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $category = Category::create([
            'name' => 'Đặc sản',
            'slug' => 'dac-san-' . uniqid(),
            'description' => null,
            'status' => true,
        ]);

        return [$seller, $category];
    }

    private function createProductAndVariant(
        User $seller,
        Category $category
    ): array {
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Mật ong rừng',
            'description' => 'Mật ong đặc sản.',
            'price' => 120000,
            'stock' => 10,
            'origin' => 'Tây Bắc',
            'status' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Hũ 300g',
            'image' => null,
            'unit_id' => null,
            'sku' => 'MATONG-HU300',
            'quantity' => null,
            'price' => 120000,
            'stock' => 10,
            'status' => true,
        ]);

        return [$product, $variant];
    }
}