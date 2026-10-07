<?php

namespace Tests\Feature\Seller;

use App\Models\SellerRequest;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SellerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_submit_a_seller_application(): void
    {
        $buyer = User::factory()->create([
            'phone' => null,
            'role' => 'buyer',
        ]);

        $response = $this
            ->actingAs($buyer)
            ->post(route('seller.register.submit'), [
                'store_name' => 'Rau sạch nhà An',
                'phone' => '0912345678',
                'address' => 'Đà Lạt, Lâm Đồng',
                'description' => 'Rau sạch được trồng tại vườn.',
            ]);

        $response
            ->assertRedirect(route('seller.register'))
            ->assertSessionHas(
                'success',
                'Yêu cầu đã được gửi, vui lòng chờ Admin duyệt.'
            );

        $this->assertDatabaseHas('seller_requests', [
            'user_id' => $buyer->id,
            'store_name' => 'Rau sạch nhà An',
            'phone' => '0912345678',
            'address' => 'Đà Lạt, Lâm Đồng',
            'description' => 'Rau sạch được trồng tại vườn.',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $buyer->id,
            'role' => 'buyer',
        ]);
    }

    public function test_buyer_can_view_seller_registration_form(): void
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $this
            ->actingAs($buyer)
            ->get(route('seller.register'))
            ->assertOk()
            ->assertSee('Đăng ký Người bán');
    }

    public function test_seller_application_requires_all_requested_fields(): void
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $this
            ->actingAs($buyer)
            ->post(route('seller.register.submit'), [])
            ->assertSessionHasErrors([
                'store_name',
                'phone',
                'address',
                'description',
            ]);
    }

    public function test_buyer_cannot_submit_a_second_pending_application(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        SellerRequest::create([
            'user_id' => $buyer->id,
            'store_name' => 'Cửa hàng đang chờ duyệt',
            'phone' => '0912345678',
            'address' => 'Đà Lạt',
            'description' => 'Hồ sơ hiện tại.',
        ]);

        $this
            ->actingAs($buyer)
            ->post(route('seller.register.submit'), [
                'store_name' => 'Cửa hàng thứ hai',
                'phone' => '0987654321',
                'address' => 'Hà Nội',
                'description' => 'Hồ sơ mới.',
            ])
            ->assertRedirect(route('seller.register'))
            ->assertSessionHas('warning');

        $this->assertDatabaseCount('seller_requests', 1);
    }

    public function test_admin_can_approve_a_pending_seller_application(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $applicant = User::factory()->create([
            'role' => 'buyer',
        ]);
        $sellerRequest = SellerRequest::create([
            'user_id' => $applicant->id,
            'store_name' => 'Nông sản xanh',
            'phone' => '0912345678',
            'address' => 'Đà Lạt',
            'description' => 'Nông sản sạch tại vườn.',
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.seller-requests.approve', $sellerRequest));

        $response->assertRedirect(route('admin.seller-requests.index'));

        $this->assertDatabaseHas('users', [
            'id' => $applicant->id,
            'role' => 'seller',
        ]);
        $this->assertDatabaseHas('seller_requests', [
            'id' => $sellerRequest->id,
            'status' => 'approved',
        ]);
    }

    public function test_admin_can_reject_a_pending_seller_application(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        $applicant = User::factory()->create([
            'role' => 'buyer',
        ]);
        $sellerRequest = SellerRequest::create([
            'user_id' => $applicant->id,
            'store_name' => 'Nông sản xanh',
            'phone' => '0912345678',
            'address' => 'Đà Lạt',
            'description' => 'Nông sản sạch tại vườn.',
        ]);

        $response = $this
            ->actingAs($admin)
            ->patch(route('admin.seller-requests.reject', $sellerRequest));

        $response->assertRedirect(route('admin.seller-requests.index'));

        $this->assertDatabaseHas('users', [
            'id' => $applicant->id,
            'role' => 'buyer',
        ]);
        $this->assertDatabaseHas('seller_requests', [
            'id' => $sellerRequest->id,
            'status' => 'rejected',
        ]);
    }

    public function test_non_admin_cannot_access_seller_approvals(): void
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
        ]);

        $this
            ->actingAs($buyer)
            ->get(route('admin.seller-requests.index'))
            ->assertForbidden();
    }

    public function test_admin_seeder_creates_the_default_admin_account(): void
    {
        $this->seed(AdminUserSeeder::class);

        $admin = User::where('email', 'admin@gmail.com')->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('password', $admin->password));
    }
}
