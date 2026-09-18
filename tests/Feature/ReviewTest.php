<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_a_public_review(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $product = $this->createProduct($seller);

        $response = $this
            ->actingAs($customer)
            ->from(route('products.show', $product->id))
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Sản phẩm rất tốt.',
            ]);

        $response->assertRedirect(
            route('products.show', $product->id)
        );

        $response->assertSessionHas(
            'success',
            'Đánh giá của bạn đã được đăng công khai.'
        );

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'comment' => 'Sản phẩm rất tốt.',
            'status' => Review::STATUS_APPROVED,
            'moderated_by' => null,
        ]);
    }

    public function test_review_is_visible_immediately_after_submission(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $customer = User::factory()->create([
            'name' => 'Khách hàng kiểm thử',
            'role' => 'customer',
        ]);

        $product = $this->createProduct($seller);

        $this
            ->actingAs($customer)
            ->post(route('reviews.store', $product), [
                'rating' => 4,
                'comment' => 'Đánh giá được đăng ngay.',
            ])
            ->assertRedirect();

        $response = $this->get(
            route('products.show', $product->id)
        );

        $response->assertOk();
        $response->assertSee('Khách hàng kiểm thử');
        $response->assertSee('Đánh giá được đăng ngay.');
        $response->assertSee('4.0/5');
    }

    public function test_rating_must_be_between_one_and_five(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $product = $this->createProduct($seller);

        $response = $this
            ->actingAs($customer)
            ->from(route('products.show', $product->id))
            ->post(route('reviews.store', $product), [
                'rating' => 6,
                'comment' => 'Số sao không hợp lệ.',
            ]);

        $response->assertRedirect(
            route('products.show', $product->id)
        );

        $response->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_seller_cannot_submit_a_customer_review(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $product = $this->createProduct($seller);

        $response = $this
            ->actingAs($seller)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Đánh giá của người bán.',
            ]);

        $response->assertForbidden();

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_customer_updates_existing_review_instead_of_creating_duplicate(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $product = $this->createProduct($seller);

        Review::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 3,
            'comment' => 'Đánh giá ban đầu.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $response = $this
            ->actingAs($customer)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Đánh giá đã cập nhật.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseCount('reviews', 1);

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'comment' => 'Đánh giá đã cập nhật.',
            'status' => Review::STATUS_APPROVED,
        ]);
    }

    public function test_seller_review_moderation_route_does_not_exist(): void
    {
        $this->assertFalse(
            Route::has('seller.reviews.moderate')
        );
    }

    private function createProduct(User $seller): Product
    {
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay-' . uniqid(),
            'description' => 'Danh mục dùng cho kiểm thử.',
            'status' => true,
        ]);

        return Product::create([
            'seller_id' => $seller->id,
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