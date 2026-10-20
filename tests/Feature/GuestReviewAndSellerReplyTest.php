<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestReviewAndSellerReplyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_review_without_name_or_comment(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $product = $this->createProduct($seller);

        $response = $this
            ->from(route('products.show', $product->id))
            ->post(route('reviews.store', $product), [
                'rating' => 5,
            ]);

        $response->assertRedirect(
            route('products.show', $product->id)
        );

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => null,
            'guest_name' => null,
            'rating' => 5,
            'comment' => null,
            'status' => Review::STATUS_APPROVED,
        ]);
    }

    public function test_guest_name_and_review_are_visible_publicly(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $product = $this->createProduct($seller);

        $this
            ->post(route('reviews.store', $product), [
                'guest_name' => 'Khách thử nghiệm',
                'rating' => 4,
                'comment' => 'Sản phẩm khá tốt.',
            ])
            ->assertRedirect();

        $response = $this->get(
            route('products.show', $product->id)
        );

        $response->assertOk();
        $response->assertSee('Khách thử nghiệm');
        $response->assertSee('Sản phẩm khá tốt.');
    }

    public function test_product_owner_can_create_update_and_delete_reply(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $product = $this->createProduct($seller);
        $review = $this->createReview($product, $buyer);

        $this
            ->actingAs($seller)
            ->patch(route('reviews.reply', $review), [
                'seller_reply' => 'Cảm ơn bạn đã đánh giá.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'seller_reply' => 'Cảm ơn bạn đã đánh giá.',
        ]);

        $this
            ->actingAs($seller)
            ->patch(route('reviews.reply', $review), [
                'seller_reply' => 'Cảm ơn bạn đã tin tưởng sản phẩm.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'seller_reply' => 'Cảm ơn bạn đã tin tưởng sản phẩm.',
        ]);

        $this
            ->actingAs($seller)
            ->delete(route('reviews.reply.destroy', $review))
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'seller_reply' => null,
            'seller_replied_at' => null,
        ]);
    }

    public function test_another_seller_cannot_reply_to_review(): void
    {
        $owner = User::factory()->create([
            'role' => 'seller',
        ]);

        $anotherSeller = User::factory()->create([
            'role' => 'seller',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $product = $this->createProduct($owner);
        $review = $this->createReview($product, $buyer);

        $this
            ->actingAs($anotherSeller)
            ->patch(route('reviews.reply', $review), [
                'seller_reply' => 'Phản hồi không hợp lệ.',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'seller_reply' => null,
        ]);
    }

    public function test_buyer_cannot_reply_to_review(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $product = $this->createProduct($seller);
        $review = $this->createReview($product, $buyer);

        $this
            ->actingAs($buyer)
            ->patch(route('reviews.reply', $review), [
                'seller_reply' => 'Người mua không được phản hồi.',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'seller_reply' => null,
        ]);
    }

    public function test_guest_must_login_before_replying(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $product = $this->createProduct($seller);
        $review = $this->createReview($product, $buyer);

        $this
            ->patch(route('reviews.reply', $review), [
                'seller_reply' => 'Phản hồi của khách.',
            ])
            ->assertRedirect(route('login'));
    }

    public function test_buyer_can_delete_own_review(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $product = $this->createProduct($seller);
        $review = $this->createReview($product, $buyer);

        $this
            ->actingAs($buyer)
            ->delete(route('reviews.destroy', $review))
            ->assertRedirect();

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_seller_can_delete_own_review(): void
    {
        $productOwner = User::factory()->create([
            'role' => 'seller',
        ]);

        $reviewAuthor = User::factory()->create([
            'role' => 'seller',
        ]);

        $product = $this->createProduct($productOwner);
        $review = $this->createReview($product, $reviewAuthor);

        $this
            ->actingAs($reviewAuthor)
            ->delete(route('reviews.destroy', $review))
            ->assertRedirect();

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_review(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $reviewAuthor = User::factory()->create([
            'role' => 'buyer',
        ]);

        $anotherBuyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $product = $this->createProduct($seller);
        $review = $this->createReview($product, $reviewAuthor);

        $this
            ->actingAs($anotherBuyer)
            ->delete(route('reviews.destroy', $review))
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }

    public function test_guest_review_cannot_be_deleted_by_logged_in_user(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $product = $this->createProduct($seller);

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => null,
            'guest_name' => 'Khách vãng lai',
            'rating' => 4,
            'comment' => 'Đánh giá của khách.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this
            ->actingAs($seller)
            ->delete(route('reviews.destroy', $review))
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
    private function createReview(
        Product $product,
        User $buyer
    ): Review {
        return Review::create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'rating' => 5,
            'comment' => 'Đánh giá dùng cho kiểm thử.',
            'status' => Review::STATUS_APPROVED,
        ]);
    }

    private function createProduct(User $seller): Product
    {
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay-'.uniqid(),
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

