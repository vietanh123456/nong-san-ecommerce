<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartVariantTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_add_selected_variant_with_quantity(): void
    {
        [$product, $variant] = $this->createProductWithVariant(
            name: 'Hũ 300g',
            sku: 'MATONG-HU300',
            price: 120000,
            stock: 10
        );

        $variant->update([
            'image' => 'product-variants/hu-300g.jpg',
        ]);

        $response = $this->post(
            route('cart.add', $product),
            [
                'variant_id' => $variant->id,
                'quantity' => 2,
            ]
        );

        $response->assertRedirect();

        $response->assertSessionHas(
            "cart.{$variant->id}.variant_id",
            $variant->id
        );

        $response->assertSessionHas(
            "cart.{$variant->id}.variant_name",
            'Hũ 300g'
        );

        $response->assertSessionHas(
            "cart.{$variant->id}.quantity",
            2
        );

        $response->assertSessionHas(
            "cart.{$variant->id}.price",
            120000.0
        );

        $response->assertSessionHas(
            "cart.{$variant->id}.image",
            'product-variants/hu-300g.jpg'
        );
    }

    public function test_different_variants_are_separate_cart_items(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $category = $this->createCategory();

        $product = $this->createProduct(
            $seller,
            $category,
            'Bánh pía'
        );

        $smallVariant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Hộp 4 bánh',
            'image' => null,
            'unit_id' => null,
            'sku' => 'BANHPIA-HOP4',
            'quantity' => null,
            'price' => 100000,
            'stock' => 20,
            'status' => true,
        ]);

        $largeVariant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Hộp 8 bánh',
            'image' => null,
            'unit_id' => null,
            'sku' => 'BANHPIA-HOP8',
            'quantity' => null,
            'price' => 190000,
            'stock' => 15,
            'status' => true,
        ]);

        $this->post(route('cart.add', $product), [
            'variant_id' => $smallVariant->id,
            'quantity' => 1,
        ])->assertRedirect();

        $response = $this->post(
            route('cart.add', $product),
            [
                'variant_id' => $largeVariant->id,
                'quantity' => 2,
            ]
        );

        $response->assertRedirect();

        $cart = session('cart');

        $this->assertCount(2, $cart);

        $this->assertSame(
            1,
            $cart[(string) $smallVariant->id]['quantity']
        );

        $this->assertSame(
            2,
            $cart[(string) $largeVariant->id]['quantity']
        );
    }

    public function test_variant_must_belong_to_selected_product(): void
    {
        [$firstProduct] = $this->createProductWithVariant(
            name: 'Chai 500ml',
            sku: 'MATONG-500ML',
            price: 150000,
            stock: 10
        );

        [, $otherVariant] = $this->createProductWithVariant(
            name: 'Hộp 4 bánh',
            sku: 'BANH-HOP4',
            price: 100000,
            stock: 20
        );

        $response = $this->from(
            route('products.show', $firstProduct->id)
        )->post(
            route('cart.add', $firstProduct),
            [
                'variant_id' => $otherVariant->id,
                'quantity' => 1,
            ]
        );

        $response->assertRedirect(
            route('products.show', $firstProduct->id)
        );

        $response->assertSessionHasErrors('variant_id');

        $this->assertEmpty(session('cart', []));
    }

    public function test_quantity_cannot_exceed_variant_stock(): void
    {
        [$product, $variant] = $this->createProductWithVariant(
            name: 'Combo quà biếu',
            sku: 'COMBO-QUA',
            price: 500000,
            stock: 3
        );

        $response = $this->from(
            route('products.show', $product->id)
        )->post(
            route('cart.add', $product),
            [
                'variant_id' => $variant->id,
                'quantity' => 4,
            ]
        );

        $response->assertRedirect(
            route('products.show', $product->id)
        );

        $response->assertSessionHasErrors('quantity');

        $this->assertEmpty(session('cart', []));
    }

    public function test_customer_can_increase_and_decrease_variant_quantity(): void
    {
        [$product, $variant] = $this->createProductWithVariant(
            name: 'Gói 200g',
            sku: 'TRA-GOI200',
            price: 80000,
            stock: 5
        );

        $this->post(route('cart.add', $product), [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])->assertRedirect();

        $this->patch(route('cart.update', $variant->id), [
            'action' => 'increase',
        ])->assertRedirect();

        $this->assertSame(
            3,
            session("cart.{$variant->id}.quantity")
        );

        $this->patch(route('cart.update', $variant->id), [
            'action' => 'decrease',
        ])->assertRedirect();

        $this->assertSame(
            2,
            session("cart.{$variant->id}.quantity")
        );
    }

    private function createProductWithVariant(
        string $name,
        string $sku,
        int $price,
        int $stock
    ): array {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $category = $this->createCategory();

        $product = $this->createProduct(
            $seller,
            $category,
            'Sản phẩm kiểm thử ' . $sku
        );

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => $name,
            'image' => null,
            'unit_id' => null,
            'sku' => $sku,
            'quantity' => null,
            'price' => $price,
            'stock' => $stock,
            'status' => true,
        ]);

        return [$product, $variant];
    }

    private function createProduct(
        User $seller,
        Category $category,
        string $name
    ): Product {
        return Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => $name,
            'description' => 'Sản phẩm dùng để kiểm thử.',
            'price' => 0,
            'stock' => 0,
            'origin' => 'Việt Nam',
            'status' => true,
        ]);
    }

    private function createCategory(): Category
    {
        return Category::create([
            'name' => 'Đặc sản ' . uniqid(),
            'slug' => 'dac-san-' . uniqid(),
            'description' => null,
            'status' => true,
        ]);
    }
}