<?php

namespace Tests\Feature\Seller;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_seller_dashboard(): void
    {
        $response = $this->get(
            route('seller.dashboard')
        );

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_customer_cannot_access_seller_dashboard(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this
            ->actingAs($customer)
            ->get(route('seller.dashboard'));

        $response->assertForbidden();
    }

    public function test_customer_cannot_access_seller_product_management(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this
            ->actingAs($customer)
            ->get(route('seller.products.index'));

        $response->assertForbidden();
    }

    public function test_seller_can_access_seller_dashboard(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $response = $this
            ->actingAs($seller)
            ->get(route('seller.dashboard'));

        $response->assertOk();
        $response->assertSee('Seller Dashboard');
    }

    public function test_seller_can_access_product_management(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $response = $this
            ->actingAs($seller)
            ->get(route('seller.products.index'));

        $response->assertOk();
        $response->assertSee('Sản phẩm của tôi');
    }
}