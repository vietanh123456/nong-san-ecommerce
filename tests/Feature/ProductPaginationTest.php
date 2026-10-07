<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_search_matches_without_vietnamese_diacritics(): void
    {
        $category = Category::create([
            'name' => 'Hạt',
            'slug' => 'hat',
            'description' => 'Danh mục kiểm thử.',
            'status' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Hạt Điều Rang Salt',
            'description' => 'Hạt điều rang.',
            'price' => 220000,
            'stock' => 10,
            'status' => true,
        ]);

        foreach ([
            route('products.index', ['search' => 'Hat Dieu']),
            route('products.index', ['search' => 'HẠT ĐIỀU']),
            route('products.index', ['search' => 'hẠt đIềU']),
            route('home', ['search' => 'hat dieu']),
            route('home', ['search' => 'HẠT ĐIỀU']),
        ] as $url) {
            $response = $this->get($url);

            $response
                ->assertOk()
                ->assertSee($product->name);

            $this->assertSame(
                [$product->id],
                $response->viewData('products')->modelKeys()
            );
        }
    }

    public function test_hidden_products_are_excluded_from_home_and_search_results(): void
    {
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
            'description' => 'Danh mục kiểm thử.',
            'status' => true,
        ]);

        $visibleProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Sầu Riêng Vườn Nhà',
            'description' => 'Sầu riêng chín tự nhiên.',
            'price' => 85000,
            'stock' => 10,
            'status' => true,
        ]);

        $hiddenProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Sầu Riêng Đã Ẩn',
            'description' => 'Sản phẩm bị ẩn.',
            'price' => 75000,
            'stock' => 10,
            'status' => false,
        ]);

        foreach ([
            route('home'),
            route('home', ['search' => 'SẦU RIÊNG']),
            route('products.index', ['search' => 'sầu riêng']),
        ] as $url) {
            $response = $this->get($url);

            $response
                ->assertOk()
                ->assertSee($visibleProduct->name)
                ->assertDontSee($hiddenProduct->name);

            $this->assertSame(
                [$visibleProduct->id],
                $response->viewData('products')->modelKeys()
            );
        }
    }

    public function test_search_works_on_home_for_buyer_seller_and_admin_accounts(): void
    {
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
            'description' => 'Danh mục kiểm thử.',
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sầu Riêng Chín Cây',
            'description' => 'Thu hoạch tại vườn.',
            'price' => 95000,
            'stock' => 10,
            'status' => true,
        ]);

        foreach (['buyer', 'seller', 'admin'] as $role) {
            $account = User::factory()->create(['role' => $role]);

            $this->actingAs($account)
                ->get(route('home', ['search' => 'SẦU RIÊNG']))
                ->assertOk()
                ->assertSee($product->name);
        }
    }

    public function test_seeded_products_have_search_data_when_model_events_are_disabled(): void
    {
        $this->seed();

        $product = Product::query()
            ->where('name', 'Hạt Điều Rang Salt')
            ->firstOrFail();

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'search_name' => 'hat dieu rang salt',
        ]);

        $this->get(route('home', ['search' => 'HẠT ĐIỀU']))
            ->assertOk()
            ->assertSee('Hạt Điều Rang Salt');
    }

    public function test_product_listing_shows_eight_items_and_preserves_filters_in_pagination(): void
    {
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
            'description' => 'Danh mục kiểm thử.',
            'status' => true,
        ]);

        foreach (range(1, 9) as $number) {
            Product::create([
                'category_id' => $category->id,
                'name' => "Cam Cao Phong {$number}",
                'description' => 'Cam tươi.',
                'price' => 45000,
                'stock' => 10,
                'status' => true,
            ]);
        }

        $response = $this->get(route('products.index', [
            'search' => 'cam',
            'category_id' => $category->id,
        ]));

        $response
            ->assertOk()
            ->assertSee('pagination', false)
            ->assertSee('page-item', false)
            ->assertDontSee('Showing 1 to', false);

        $products = $response->viewData('products');

        $this->assertCount(8, $products->items());
        $this->assertSame(9, $products->total());
        $this->assertStringContainsString('page=2', $response->getContent());
        $this->assertStringContainsString('search=cam', $response->getContent());
        $this->assertStringContainsString(
            'category_id='.$category->id,
            $response->getContent()
        );

        $secondPage = $this->get(route('products.index', [
            'search' => 'cam',
            'category_id' => $category->id,
            'page' => 2,
        ]));

        $secondPage->assertOk();
        $this->assertCount(1, $secondPage->viewData('products')->items());
    }

    public function test_product_filters_include_category_descendants_price_range_and_average_rating(): void
    {
        $parentCategory = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay-bo-loc',
            'status' => true,
        ]);
        $childCategory = Category::create([
            'name' => 'Cam',
            'slug' => 'cam-bo-loc',
            'parent_id' => $parentCategory->id,
            'status' => true,
        ]);
        $otherCategory = Category::create([
            'name' => 'Rau củ',
            'slug' => 'rau-cu-bo-loc',
            'status' => true,
        ]);

        $matchingProduct = Product::create([
            'category_id' => $childCategory->id,
            'name' => 'Cam ngọt',
            'price' => 60000,
            'stock' => 10,
            'status' => true,
        ]);
        $lowRatedProduct = Product::create([
            'category_id' => $childCategory->id,
            'name' => 'Cam chua',
            'price' => 70000,
            'stock' => 10,
            'status' => true,
        ]);
        $outOfRangeProduct = Product::create([
            'category_id' => $childCategory->id,
            'name' => 'Cam đắt',
            'price' => 120000,
            'stock' => 10,
            'status' => true,
        ]);
        $wrongCategoryProduct = Product::create([
            'category_id' => $otherCategory->id,
            'name' => 'Rau sạch',
            'price' => 60000,
            'stock' => 10,
            'status' => true,
        ]);
        $reviewer = User::factory()->create();

        foreach ([$matchingProduct, $outOfRangeProduct] as $product) {
            foreach ([4, 5] as $rating) {
                Review::create([
                    'product_id' => $product->id,
                    'user_id' => $reviewer->id,
                    'rating' => $rating,
                    'status' => Review::STATUS_APPROVED,
                ]);
            }
        }

        foreach ([3, 4] as $rating) {
            Review::create([
                'product_id' => $lowRatedProduct->id,
                'user_id' => $reviewer->id,
                'rating' => $rating,
                'status' => Review::STATUS_APPROVED,
            ]);
        }

        $response = $this->get(route('products.index', [
            'categories' => [$parentCategory->id],
            'min_price' => 50000,
            'max_price' => 100000,
            'rating' => 4,
        ]));

        $response
            ->assertOk()
            ->assertSee($matchingProduct->name)
            ->assertDontSee($lowRatedProduct->name)
            ->assertDontSee($outOfRangeProduct->name)
            ->assertDontSee($wrongCategoryProduct->name)
            ->assertSee('name="categories[]"', false)
            ->assertSee($childCategory->name);

        $this->assertSame(
            [$matchingProduct->id],
            $response->viewData('products')->modelKeys()
        );
    }

    public function test_home_product_listing_shows_eight_items_and_preserves_search(): void
    {
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
            'description' => 'Danh mục kiểm thử.',
            'status' => true,
        ]);

        foreach (range(1, 9) as $number) {
            Product::create([
                'category_id' => $category->id,
                'name' => "Cam Cao Phong {$number}",
                'description' => 'Cam tươi.',
                'price' => 45000,
                'stock' => 10,
                'status' => true,
            ]);
        }

        $response = $this->get(route('home', ['search' => 'cam']));

        $response
            ->assertOk()
            ->assertSee('pagination', false)
            ->assertSee('page-item', false)
            ->assertSee('search=cam', false)
            ->assertSee('page=2', false)
            ->assertDontSee('Showing 1 to', false);

        $this->assertCount(8, $response->viewData('products')->items());
        $this->assertSame(9, $response->viewData('products')->total());

        $secondPage = $this->get(route('home', [
            'search' => 'cam',
            'page' => 2,
        ]));

        $secondPage->assertOk();
        $this->assertCount(1, $secondPage->viewData('products')->items());
    }
}
