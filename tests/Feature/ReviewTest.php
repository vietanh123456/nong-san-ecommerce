<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_submit_a_review(): void
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

        $response->assertRedirect(route('products.show', $product->id));

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 5,
            'comment' => 'Sản phẩm rất tốt.',
            'status' => Review::STATUS_PENDING,
        ]);
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

        $response->assertRedirect(route('products.show', $product->id));
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

    public function test_seller_can_approve_review_of_own_product(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $product = $this->createProduct($seller);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 4,
            'comment' => 'Sản phẩm tốt.',
            'status' => Review::STATUS_PENDING,
        ]);

        $response = $this
            ->actingAs($seller)
            ->patch(route('seller.reviews.moderate', $review), [
                'status' => Review::STATUS_APPROVED,
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => Review::STATUS_APPROVED,
            'moderated_by' => $seller->id,
        ]);

        $this->assertNotNull($review->fresh()->moderated_at);
    }

    public function test_seller_cannot_moderate_review_of_another_sellers_product(): void
    {
        $productSeller = User::factory()->create([
            'role' => 'seller',
        ]);

        $otherSeller = User::factory()->create([
            'role' => 'seller',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $product = $this->createProduct($productSeller);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 3,
            'comment' => 'Đánh giá đang chờ duyệt.',
            'status' => Review::STATUS_PENDING,
        ]);

        $response = $this
            ->actingAs($otherSeller)
            ->patch(route('seller.reviews.moderate', $review), [
                'status' => Review::STATUS_APPROVED,
            ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => Review::STATUS_PENDING,
            'moderated_by' => null,
        ]);
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