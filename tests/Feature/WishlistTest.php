<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_authenticated_roles_can_add_and_remove_favorites(): void
    {
        $product = $this->createProduct();

        foreach (['buyer', 'seller', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this
                ->actingAs($user)
                ->post(route('wishlist.toggle', $product))
                ->assertRedirect();

            $this->assertDatabaseHas('wishlists', [
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);

            $this
                ->post(route('wishlist.toggle', $product))
                ->assertRedirect();

            $this->assertDatabaseMissing('wishlists', [
                'user_id' => $user->id,
                'product_id' => $product->id,
            ]);
        }
    }

    public function test_guest_is_redirected_to_login_when_submitting_a_favorite(): void
    {
        $product = $this->createProduct();

        $this
            ->post(route('wishlist.toggle', $product))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_guest_sees_login_link_on_product_favorites(): void
    {
        $product = $this->createProduct();

        $this
            ->get(route('home'))
            ->assertOk()
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('aria-label="Đăng nhập để sử dụng yêu thích"', false);

        $this
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Đăng nhập để thêm vào yêu thích')
            ->assertSee('href="'.route('login').'"', false);
    }

    public function test_authenticated_users_see_favorite_controls_regardless_of_role(): void
    {
        $product = $this->createProduct();

        foreach (['buyer', 'seller', 'admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this
                ->actingAs($user)
                ->get(route('products.show', $product))
                ->assertOk()
                ->assertSee('Thêm hoặc xóa khỏi yêu thích');
        }
    }

    public function test_authenticated_users_see_the_standard_navigation_and_role_specific_actions(): void
    {
        $product = $this->createProduct();

        foreach (['buyer', 'user', 'seller', 'admin', 'super_admin'] as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this
                ->actingAs($user)
                ->post(route('wishlist.toggle', $product))
                ->assertRedirect();

            foreach ([route('home'), route('products.index')] as $page) {
                $response = $this->get($page);

                $response
                    ->assertOk()
                    ->assertSee('href="'.route('products.index').'"', false)
                    ->assertSee('href="'.route('wishlist.index').'"', false)
                    ->assertSee('href="'.route('cart.index').'"', false)
                    ->assertSee('href="'.route('profile').'"', false)
                    ->assertSee('action="'.route('logout').'"', false)
                    ->assertSee(
                        'class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"',
                        false
                    )
                    ->assertSeeText('1');

                if (in_array($role, ['admin', 'super_admin'], true)) {
                    $response
                        ->assertSee('Admin Panel')
                        ->assertSee('Duyệt người bán')
                        ->assertDontSee('Trở thành người bán');
                } elseif (in_array($role, ['user', 'buyer'], true)) {
                    $response
                        ->assertSee('Trở thành người bán')
                        ->assertDontSee('Admin Panel')
                        ->assertDontSee('Duyệt người bán');
                } else {
                    $response
                        ->assertDontSee('Trở thành người bán')
                        ->assertDontSee('Admin Panel')
                        ->assertDontSee('Duyệt người bán');
                }
            }
        }
    }

    private function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay-'.uniqid(),
            'description' => 'Danh mục dùng cho kiểm thử.',
            'status' => true,
        ]);

        return Product::create([
            'category_id' => $category->id,
            'name' => 'Cam Cao Phong',
            'description' => 'Cam tươi dùng cho kiểm thử.',
            'price' => 45000,
            'stock' => 100,
            'origin' => 'Hòa Bình',
            'status' => true,
        ]);
    }
}
