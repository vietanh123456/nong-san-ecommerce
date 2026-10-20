<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_upload_review_image(): void
    {
        Storage::fake('supabase');

        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $product = $this->createProduct($seller);

        $this
            ->actingAs($buyer)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Đánh giá có ảnh.',
                'image' => $this->fakeImage(),
            ])
            ->assertRedirect();

        $review = Review::query()
            ->where('user_id', $buyer->id)
            ->firstOrFail();

        $this->assertNotNull($review->image);

        Storage::disk('supabase')
            ->assertExists($review->image);
    }

    public function test_seller_can_upload_review_image(): void
    {
        Storage::fake('supabase');

        $productOwner = User::factory()->create([
            'role' => 'seller',
        ]);

        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $product = $this->createProduct($productOwner);

        $this
            ->actingAs($seller)
            ->post(route('reviews.store', $product), [
                'rating' => 4,
                'image' => $this->fakeImage(),
            ])
            ->assertRedirect();

        $review = Review::query()
            ->where('user_id', $seller->id)
            ->firstOrFail();

        Storage::disk('supabase')
            ->assertExists($review->image);
    }

    public function test_guest_can_upload_review_image(): void
    {
        Storage::fake('supabase');

        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $product = $this->createProduct($seller);

        $this
            ->post(route('reviews.store', $product), [
                'guest_name' => 'Khách có ảnh',
                'rating' => 5,
                'image' => $this->fakeImage(),
            ])
            ->assertRedirect();

        $review = Review::query()
            ->whereNull('user_id')
            ->firstOrFail();

        Storage::disk('supabase')
            ->assertExists($review->image);
    }

    public function test_invalid_review_image_is_rejected(): void
    {
        Storage::fake('supabase');

        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $product = $this->createProduct($seller);

        $this
            ->actingAs($buyer)
            ->from(route('products.show', $product->id))
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'image' => UploadedFile::fake()->create(
                    'malware.exe',
                    100,
                    'application/octet-stream'
                ),
            ])
            ->assertSessionHasErrors('image');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_updating_without_image_keeps_old_image(): void
    {
        Storage::fake('supabase');

        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $product = $this->createProduct($seller);

        Storage::disk('supabase')->put(
            'reviews/old-review.jpg',
            'old-image'
        );

        Review::create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'rating' => 3,
            'comment' => 'Đánh giá cũ.',
            'image' => 'reviews/old-review.jpg',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this
            ->actingAs($buyer)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Đánh giá mới.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'image' => 'reviews/old-review.jpg',
        ]);

        Storage::disk('supabase')
            ->assertExists('reviews/old-review.jpg');
    }

    public function test_replacing_image_deletes_old_image(): void
    {
        Storage::fake('supabase');

        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $product = $this->createProduct($seller);

        Storage::disk('supabase')->put(
            'reviews/old-review.jpg',
            'old-image'
        );

        Review::create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'rating' => 3,
            'image' => 'reviews/old-review.jpg',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this
            ->actingAs($buyer)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'image' => $this->fakeImage(),
            ])
            ->assertRedirect();

        $review = Review::query()
            ->where('user_id', $buyer->id)
            ->firstOrFail();

        $this->assertNotSame(
            'reviews/old-review.jpg',
            $review->image
        );

        Storage::disk('supabase')
            ->assertMissing('reviews/old-review.jpg');

        Storage::disk('supabase')
            ->assertExists($review->image);
    }

    public function test_deleting_review_deletes_its_image(): void
    {
        Storage::fake('supabase');

        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $product = $this->createProduct($seller);

        Storage::disk('supabase')->put(
            'reviews/delete-review.jpg',
            'review-image'
        );

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'rating' => 5,
            'image' => 'reviews/delete-review.jpg',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this
            ->actingAs($buyer)
            ->delete(route('reviews.destroy', $review))
            ->assertRedirect();

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);

        Storage::disk('supabase')
            ->assertMissing('reviews/delete-review.jpg');
    }

    private function fakeImage(): UploadedFile
    {
        $png = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        );

        if ($png === false) {
            throw new \RuntimeException(
                'Không thể tạo ảnh PNG kiểm thử.'
            );
        }

        return UploadedFile::fake()->createWithContent(
            'review.png',
            $png
        );
    }
    private function createProduct(User $seller): Product
    {
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay-'.uniqid(),
            'description' => 'Danh mục kiểm thử.',
            'status' => true,
        ]);

        return Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Sản phẩm kiểm thử ảnh review',
            'description' => 'Mô tả kiểm thử.',
            'price' => 50000,
            'stock' => 100,
            'origin' => 'Việt Nam',
            'status' => true,
        ]);
    }
}

