<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_rejects_inactive_products_and_quantities_over_stock(): void
    {
        $category = $this->createCategory();
        $inactiveProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm đã ẩn',
            'price' => 10000,
            'stock' => 5,
            'status' => false,
        ]);
        $activeProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm còn bán',
            'price' => 10000,
            'stock' => 2,
            'status' => true,
        ]);

        $this->from(route('home'))
            ->post(route('cart.add', $inactiveProduct), ['quantity' => 1])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('quantity')
            ->assertSessionMissing('cart');

        $this->from(route('home'))
            ->post(route('cart.add', $activeProduct), ['quantity' => 3])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('quantity')
            ->assertSessionMissing('cart');

        $this->from(route('home'))
            ->post(route('cart.add', $activeProduct), ['quantity' => '1.5'])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('quantity');
    }

    public function test_cart_and_checkout_totals_use_the_current_database_price(): void
    {
        $category = $this->createCategory();
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm cập nhật giá',
            'price' => 42000,
            'stock' => 5,
            'status' => true,
        ]);

        $cart = [
            $product->id => [
                'id' => $product->id,
                'name' => $product->name,
                'quantity' => 2,
                'price' => 1,
                'image' => '',
            ],
        ];

        $cartResponse = $this->withSession(['cart' => $cart])
            ->get(route('cart.index'));
        $cartResponse
            ->assertOk()
            ->assertSee('42.000đ')
            ->assertSee('84.000đ')
            ->assertDontSee('2đ');
        $this->assertSame(42000, (int) session('cart')[$product->id]['price']);

        $checkout = $this->withSession(['cart' => $cart])
            ->get(route('checkout.index'));
        $checkout->assertOk();
        $this->assertSame(84000.0, $checkout->viewData('subtotal'));
        $this->assertSame(42000, (int) $checkout->viewData('cart')[$product->id]['price']);
    }

    public function test_checkout_blocks_products_that_are_no_longer_available(): void
    {
        $category = $this->createCategory();
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm đã ngừng bán',
            'price' => 42000,
            'stock' => 5,
            'status' => false,
        ]);

        $this->withSession([
            'cart' => [
                $product->id => [
                    'id' => $product->id,
                    'quantity' => 1,
                    'price' => 42000,
                ],
            ],
        ])
            ->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors('cart');
    }

    public function test_cart_badge_tracks_quantity_after_add_update_and_remove(): void
    {
        $category = $this->createCategory();
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm đếm giỏ',
            'price' => 15000,
            'stock' => 3,
            'status' => true,
        ]);

        $this->actingAs(User::factory()->create());

        $this->post(route('cart.add', $product), ['quantity' => 2])
            ->assertRedirect();
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertViewHas('cartCount', 2);

        $this->patch(route('cart.update', $product), ['action' => 'increase'])
            ->assertRedirect();
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertViewHas('cartCount', 3);

        $this->delete(route('cart.remove', $product->id))
            ->assertRedirect();
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertViewHas('cartCount', 0);
    }

    private function createCategory(): Category
    {
        return Category::create([
            'name' => 'Kiểm thử giỏ hàng',
            'slug' => 'kiem-thu-gio-hang-'.uniqid(),
            'status' => true,
        ]);
    }
}
