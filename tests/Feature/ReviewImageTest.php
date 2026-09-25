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

    public function test_customer_can_upload_review_image(): void
    {
        Storage::fake('public');

        [$customer, $product] = $this->createCustomerAndProduct();

        $response = $this
            ->actingAs($customer)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Sản phẩm rất tốt.',
                'image' => UploadedFile::fake()->create(
                    'review.jpg',
                    100,
                    'image/jpeg'
                ),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $review = Review::query()
            ->where('product_id', $product->id)
            ->where('user_id', $customer->id)
            ->firstOrFail();

        $this->assertSame(5, $review->rating);
        $this->assertSame(
            Review::STATUS_APPROVED,
            $review->status
        );
        $this->assertNotNull($review->image);

        Storage::disk('public')->assertExists(
            $review->image
        );
    }

    public function test_invalid_review_image_is_rejected(): void
    {
        Storage::fake('public');

        [$customer, $product] = $this->createCustomerAndProduct();

        $response = $this
            ->actingAs($customer)
            ->post(route('reviews.store', $product), [
                'rating' => 4,
                'comment' => 'Ảnh không hợp lệ.',
                'image' => UploadedFile::fake()->create(
                    'malware.exe',
                    100,
                    'application/octet-stream'
                ),
            ]);

        $response->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('reviews', [
            'product_id' => $product->id,
            'user_id' => $customer->id,
        ]);
    }

    public function test_customer_can_replace_review_image(): void
    {
        Storage::fake('public');

        [$customer, $product] = $this->createCustomerAndProduct();

        Storage::disk('public')->put(
            'reviews/old.jpg',
            'old image'
        );

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 4,
            'comment' => 'Đánh giá cũ.',
            'image' => 'reviews/old.jpg',
            'status' => Review::STATUS_APPROVED,
            'moderated_by' => null,
            'moderated_at' => null,
        ]);

        $response = $this
            ->actingAs($customer)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Đánh giá đã cập nhật.',
                'image' => UploadedFile::fake()->create(
                    'new.jpg',
                    100,
                    'image/jpeg'
                ),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $review->refresh();

        $this->assertSame(5, $review->rating);
        $this->assertSame(
            'Đánh giá đã cập nhật.',
            $review->comment
        );
        $this->assertNotSame(
            'reviews/old.jpg',
            $review->image
        );

        Storage::disk('public')->assertMissing(
            'reviews/old.jpg'
        );

        Storage::disk('public')->assertExists(
            $review->image
        );

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_updating_review_without_image_keeps_old_image(): void
    {
        Storage::fake('public');

        [$customer, $product] = $this->createCustomerAndProduct();

        Storage::disk('public')->put(
            'reviews/keep.jpg',
            'review image'
        );

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 4,
            'comment' => 'Đánh giá ban đầu.',
            'image' => 'reviews/keep.jpg',
            'status' => Review::STATUS_APPROVED,
            'moderated_by' => null,
            'moderated_at' => null,
        ]);

        $response = $this
            ->actingAs($customer)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'comment' => 'Chỉ sửa nội dung.',
            ]);

        $response->assertRedirect();

        $review->refresh();

        $this->assertSame(
            'reviews/keep.jpg',
            $review->image
        );

        Storage::disk('public')->assertExists(
            'reviews/keep.jpg'
        );

        $this->assertDatabaseCount('reviews', 1);
    }

    private function createCustomerAndProduct(): array
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $category = Category::create([
            'name' => 'Đặc sản ' . uniqid(),
            'slug' => 'dac-san-' . uniqid(),
            'description' => null,
            'status' => true,
        ]);

        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Sản phẩm kiểm thử ảnh review',
            'description' => 'Dùng để kiểm thử.',
            'price' => 100000,
            'stock' => 10,
            'origin' => 'Việt Nam',
            'status' => true,
        ]);

        return [$customer, $product];
    }
}