<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

        $blackBeans = Product::query()
            ->where('name', 'Đậu Đen Xanh Lòng')
            ->firstOrFail();

        $this->assertSame(
            'gao-va-ngu-coc',
            $blackBeans->category->slug
        );

        $this->assertSame(
            'ca-phe-va-tra',
            Product::query()
                ->where('name', 'Cà Phê Moka Cầu Đất')
                ->firstOrFail()
                ->category
                ->slug
        );
        $this->assertSame(
            'nam',
            Product::query()
                ->where('name', 'Nấm Hương Khô Tây Bắc')
                ->firstOrFail()
                ->category
                ->slug
        );

        $grains = Category::query()->where('slug', 'gao-va-ngu-coc')->firstOrFail();
        $grainListing = $this->get(route('products.index', [
            'category_id' => $grains->id,
        ]));
        $grainListing->assertOk()
            ->assertSee('Gạo Tám Xoan Bắc Hương')
            ->assertSee('Đậu Đen Xanh Lòng')
            ->assertDontSee('Cam Sành Hà Giang');

        $this->get(route('products.show', $blackBeans))
            ->assertOk()
            ->assertSee('Gạo và ngũ cốc');

        $this->get(route('home', ['search' => 'HẠT ĐIỀU']))
            ->assertOk()
            ->assertSee('Hạt Điều Rang Salt');
    }

    public function test_product_listings_use_product_images_or_a_neutral_fallback(): void
    {
        $category = Category::create([
            'name' => 'Nông sản',
            'slug' => 'nong-san-anh',
            'status' => true,
        ]);
        $productWithoutImage = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm chưa có ảnh',
            'price' => 25000,
            'stock' => 10,
            'status' => true,
        ]);
        $productWithImage = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm có ảnh',
            'price' => 30000,
            'stock' => 10,
            'image' => 'products/sample.jpg',
            'status' => true,
        ]);

        foreach ([route('home'), route('products.index')] as $route) {
            $response = $this->get($route);

            $response
                ->assertOk()
                ->assertSee('Chưa có ảnh cho '.$productWithoutImage->name)
                ->assertSee('/storage/products/sample.jpg', false)
                ->assertSee('this.hidden = true; this.nextElementSibling.hidden = false;', false)
                ->assertDontSee('🥑');
        }
    }

    public function test_product_listing_shows_twelve_items_and_preserves_filters_in_pagination(): void
    {
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
            'description' => 'Danh mục kiểm thử.',
            'status' => true,
        ]);

        foreach (range(1, 13) as $number) {
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

        $this->assertCount(12, $products->items());
        $this->assertSame(13, $products->total());
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

    public function test_product_search_preserves_filters_without_nested_forms(): void
    {
        $category = Category::create([
            'name' => 'Nông sản',
            'slug' => 'nong-san-search-preserve',
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sản phẩm giá thấp',
            'description' => 'Hàng phù hợp tìm kiếm.',
            'price' => 0,
            'stock' => 10,
            'status' => true,
        ]);

        $response = $this->get(route('products.index', [
            'search' => 'giá thấp',
            'category_id' => $category->id,
            'min_price' => 0,
            'max_price' => 0,
            'rating' => 1,
            'sort' => 'price_asc',
            'page' => 3,
        ]));

        $response->assertOk()
            ->assertSee('0 sản phẩm', false)
            ->assertSee('id="product-search"', false)
            ->assertSee('name="min_price" value="0"', false)
            ->assertSee('name="max_price" value="0"', false)
            ->assertSee('name="rating" value="1"', false)
            ->assertSee('name="category_id" value="'.$category->id.'"', false)
            ->assertDontSee('name="page"', false);
        $this->assertStringNotContainsString(
            $product->name,
            $response->getContent()
        );

        preg_match_all('/<form\b[^>]*>|<\/form>/i', $response->getContent(), $formTags);
        $openForms = 0;
        $hasNestedForms = false;

        foreach ($formTags[0] as $formTag) {
            if (str_starts_with(strtolower($formTag), '</form')) {
                $openForms--;
            } else {
                $hasNestedForms = $hasNestedForms || $openForms > 0;
                $openForms++;
            }
        }

        $this->assertFalse($hasNestedForms);

        $matchedResponse = $this->get(route('products.index', [
            'search' => 'giá thấp',
            'category_id' => $category->id,
            'min_price' => 0,
            'max_price' => 0,
        ]));
        $matchedResponse->assertOk()
            ->assertSee('1 sản phẩm', false)
            ->assertSee('name="min_price" value="0"', false)
            ->assertSee('name="max_price" value="0"', false);
        $this->assertSame(
            [$product->id],
            $matchedResponse->viewData('products')->modelKeys()
        );
    }

    public function test_product_price_filters_accept_zero_and_single_bounds_and_reject_invalid_ranges(): void
    {
        $category = Category::create([
            'name' => 'Nông sản giá',
            'slug' => 'nong-san-gia',
            'status' => true,
        ]);
        $freeProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Giá bằng không',
            'price' => 0,
            'stock' => 10,
            'status' => true,
        ]);
        $midPriceProduct = Product::create([
            'category_id' => $category->id,
            'name' => 'Giá trung bình',
            'price' => 50000,
            'stock' => 10,
            'status' => true,
        ]);

        $zeroPrice = $this->get(route('products.index', ['max_price' => 0]));
        $this->assertSame([$freeProduct->id], $zeroPrice->viewData('products')->modelKeys());

        $minimumOnly = $this->get(route('products.index', ['min_price' => 50000]));
        $this->assertSame([$midPriceProduct->id], $minimumOnly->viewData('products')->modelKeys());

        $maximumOnly = $this->get(route('products.index', ['max_price' => 0]));
        $this->assertSame([$freeProduct->id], $maximumOnly->viewData('products')->modelKeys());

        $this->getJson(route('products.index', ['min_price' => -1]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('min_price');
        $this->getJson(route('products.index', ['max_price' => '50.000']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('max_price');
        $this->getJson(route('products.index', [
            'min_price' => 50000,
            'max_price' => 10000,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('max_price');
    }

    public function test_product_sort_options_apply_before_pagination_and_preserve_filters(): void
    {
        $category = Category::create([
            'name' => 'Nông sản sắp xếp',
            'slug' => 'nong-san-sap-xep',
            'status' => true,
        ]);
        $products = collect();
        $baseDate = Carbon::parse('2026-01-01 12:00:00');

        foreach (range(1, 13) as $number) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => "Sort Produce {$number}",
                'description' => 'Dùng kiểm tra thứ tự sản phẩm.',
                'price' => 10000 + (intdiv($number - 1, 2) * 1000),
                'stock' => 10,
                'status' => true,
            ]);
            $createdAt = $baseDate->copy()->addDays(intdiv($number - 1, 2));

            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

            $products->push([
                'id' => $product->id,
                'price' => (int) $product->price,
                'created_at' => $createdAt,
            ]);
        }

        $filters = [
            'search' => 'Sort Produce',
            'category_id' => $category->id,
            'min_price' => 10000,
            'max_price' => 20000,
        ];
        $sortOrders = [
            'newest' => fn (array $left, array $right): int => $right['created_at']->getTimestamp() <=> $left['created_at']->getTimestamp()
                    ?: $right['id'] <=> $left['id'],
            'price_asc' => fn (array $left, array $right): int => $left['price'] <=> $right['price']
                    ?: $right['id'] <=> $left['id'],
            'price_desc' => fn (array $left, array $right): int => $right['price'] <=> $left['price']
                    ?: $right['id'] <=> $left['id'],
        ];
        $priceAscendingIds = [];

        foreach ($sortOrders as $sort => $compare) {
            $expected = $products->all();
            usort($expected, $compare);
            if ($sort === 'price_asc') {
                $priceAscendingIds = array_column($expected, 'id');
            }

            $response = $this->get(route('products.index', $filters + ['sort' => $sort]));

            $response
                ->assertOk()
                ->assertSee('id="product-sort-form"', false)
                ->assertSee('onchange="this.form.submit()"', false)
                ->assertSee('value="'.$sort.'" selected', false)
                ->assertSee('search=Sort%20Produce', false)
                ->assertSee('category_id='.$category->id, false)
                ->assertSee('min_price=10000', false)
                ->assertSee('max_price=20000', false)
                ->assertSee('sort='.$sort, false)
                ->assertSee('page=2', false)
                ->assertDontSee('name="page"', false);

            $pageOneIds = $response->viewData('products')->modelKeys();
            $this->assertSame(
                array_column(array_slice($expected, 0, 12), 'id'),
                $pageOneIds
            );
            $this->assertSame(13, $response->viewData('products')->total());

            $secondPage = $this->get(route('products.index', $filters + [
                'sort' => $sort,
                'page' => 2,
            ]));

            $secondPage->assertOk();
            $this->assertSame(
                [array_column($expected, 'id')[12]],
                $secondPage->viewData('products')->modelKeys()
            );
        }

        $this->getJson(route('products.index', $filters + ['sort' => 'price;drop']))
            ->assertUnprocessable();
        $this->get(route('home', $filters + ['sort' => 'price_asc']))
            ->assertOk()
            ->assertSee('id="home-product-sort-form"', false)
            ->assertSee('value="price_asc" selected', false);

        $this->assertSame(
            array_slice($priceAscendingIds, 0, 12),
            $this->get(route('home', $filters + ['sort' => 'price_asc']))
                ->viewData('products')
                ->modelKeys()
        );
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

        $homeResponse = $this->get(route('home', [
            'categories' => [$parentCategory->id],
            'min_price' => 50000,
            'max_price' => 100000,
            'rating' => 4,
        ]));

        $homeResponse
            ->assertOk()
            ->assertSee($matchingProduct->name)
            ->assertDontSee($lowRatedProduct->name)
            ->assertDontSee($outOfRangeProduct->name)
            ->assertSee('name="categories[]"', false);

        $this->assertSame(
            [$matchingProduct->id],
            $homeResponse->viewData('products')->modelKeys()
        );
    }

    public function test_home_product_listing_shows_twelve_items_and_preserves_search(): void
    {
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
            'description' => 'Danh mục kiểm thử.',
            'status' => true,
        ]);

        foreach (range(1, 13) as $number) {
            Product::create([
                'category_id' => $category->id,
                'name' => "Cam Cao Phong {$number}",
                'description' => 'Cam tươi.',
                'price' => 45000,
                'stock' => 10,
                'status' => true,
            ]);
        }

        $response = $this->get(route('home', [
            'search' => 'cam',
            'categories' => [$category->id],
            'min_price' => 40000,
            'max_price' => 50000,
        ]));

        $response
            ->assertOk()
            ->assertSee('pagination', false)
            ->assertSee('page-item', false)
            ->assertSee('search=cam', false)
            ->assertSee('categories%5B0%5D='.$category->id, false)
            ->assertSee('min_price=40000', false)
            ->assertSee('max_price=50000', false)
            ->assertSee('page=2', false)
            ->assertDontSee('Showing 1 to', false);

        $this->assertCount(12, $response->viewData('products')->items());
        $this->assertSame(13, $response->viewData('products')->total());

        $secondPage = $this->get(route('home', [
            'search' => 'cam',
            'categories' => [$category->id],
            'min_price' => 40000,
            'max_price' => 50000,
            'page' => 2,
        ]));

        $secondPage->assertOk();
        $this->assertCount(1, $secondPage->viewData('products')->items());
    }

    public function test_empty_search_shows_keyword_and_clear_filters_link_on_both_listings(): void
    {
        foreach ([route('home'), route('products.index')] as $route) {
            $this->get($route.'?search=khong-co-san-pham')
                ->assertOk()
                ->assertSee('value="khong-co-san-pham"', false)
                ->assertSeeText('Không tìm thấy sản phẩm nào phù hợp với từ khóa')
                ->assertSeeText('khong-co-san-pham')
                ->assertSeeText('Xóa bộ lọc / Xem tất cả');
        }
    }
}
