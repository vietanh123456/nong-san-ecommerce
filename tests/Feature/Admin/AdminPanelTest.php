<?php

namespace Tests\Feature\Admin;

use App\Models\SellerRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_basic_statistics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'buyer']);
        $applicant = User::factory()->create(['role' => 'buyer']);
        SellerRequest::create([
            'user_id' => $applicant->id,
            'store_name' => 'Nông sản xanh',
            'phone' => '0912345678',
            'address' => 'Đà Lạt',
            'description' => 'Nông sản sạch tại vườn.',
        ]);

        $this
            ->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Xin chào')
            ->assertSee('Tài khoản')
            ->assertSee('Chứng nhận chờ duyệt');
    }

    public function test_admin_can_search_and_filter_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create([
            'name' => 'Nguyen Seller',
            'email' => 'seller@example.com',
            'role' => 'seller',
        ]);
        User::factory()->create([
            'name' => 'Nguyen Customer',
            'email' => 'customer@example.com',
            'role' => 'buyer',
        ]);

        $this
            ->actingAs($admin)
            ->get(route('admin.users.index', [
                'search' => 'seller@example.com',
                'role' => 'seller',
            ]))
            ->assertOk()
            ->assertSee('Nguyen Seller')
            ->assertDontSee('Nguyen Customer');
    }

    public function test_admin_can_search_products_without_case_sensitivity(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create([
            'name' => 'Trái cây',
            'slug' => 'trai-cay',
            'description' => 'Danh mục kiểm thử.',
            'status' => true,
        ]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Sầu Riêng Vườn Nhà',
            'description' => 'Thu hoạch tại vườn.',
            'price' => 90000,
            'stock' => 10,
            'status' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products.index', ['search' => 'SẦU RIÊNG']))
            ->assertOk()
            ->assertSee($product->name);
    }

    public function test_admin_can_demote_a_seller_to_buyer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);

        $this
            ->actingAs($admin)
            ->patch(route('admin.users.demote', $seller))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas(
                'success',
                'Đã chuyển quyền tài khoản thành Người mua thành công!'
            );

        $this->assertDatabaseHas('users', [
            'id' => $seller->id,
            'role' => 'buyer',
        ]);
    }

    public function test_admin_cannot_demote_a_non_seller_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['role' => 'buyer']);

        $this
            ->actingAs($admin)
            ->patch(route('admin.users.demote', $buyer))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('warning');

        $this->assertDatabaseHas('users', [
            'id' => $buyer->id,
            'role' => 'buyer',
        ]);
    }

    public function test_non_admin_cannot_access_admin_dashboard_or_user_management(): void
    {
        $customer = User::factory()->create(['role' => 'buyer']);

        $this
            ->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();

        $this
            ->actingAs($customer)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $seller = User::factory()->create(['role' => 'seller']);

        $this
            ->actingAs($customer)
            ->patch(route('admin.users.demote', $seller))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login_from_admin_dashboard(): void
    {
        $this
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_public_admin_login_bypass_route_does_not_exist(): void
    {
        $this->get('/bypass-login')->assertNotFound();
    }
}