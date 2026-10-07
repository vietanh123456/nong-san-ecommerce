<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
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
            route('home', ['search' => 'hat dieu']),
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
