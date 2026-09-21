<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Certificate;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TraceabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_creates_batch_and_qr_for_owned_product(): void
    {
        Storage::fake('public');
        $seller = User::factory()->create(['role' => 'seller']);
        $product = $this->productFor($seller);

        $response = $this->actingAs($seller)->post(route('seller.batches.store'), [
            'product_id' => $product->id,
            'batch_code' => 'LO-CAM-001',
            'origin' => 'Cao Phong, Hòa Bình',
            'producer' => 'Nông trại An Phú',
            'harvest_date' => '2026-09-01',
            'packaged_date' => '2026-09-02',
            'expiry_date' => '2026-09-20',
        ]);

        $batch = ProductBatch::firstOrFail();
        $response->assertRedirect(route('seller.batches.show', $batch));
        $this->assertDatabaseHas('product_batches', ['batch_code' => 'LO-CAM-001']);
        Storage::disk('public')->assertExists('qrcodes/LO-CAM-001.svg');
    }

    public function test_batch_code_must_be_unique_and_other_seller_is_forbidden(): void
    {
        $owner = User::factory()->create(['role' => 'seller']);
        $other = User::factory()->create(['role' => 'seller']);
        $batch = ProductBatch::create([
            'product_id' => $this->productFor($owner)->id,
            'batch_code' => 'LO-001', 'origin' => 'Việt Nam',
            'production_date' => '2026-09-01', 'harvest_date' => '2026-09-01',
            'packaged_date' => '2026-09-02',
        ]);

        $this->actingAs($other)->get(route('seller.batches.show', $batch))->assertForbidden();
        $this->actingAs($owner)->post(route('seller.batches.store'), [
            'product_id' => $batch->product_id, 'batch_code' => 'LO-001',
            'origin' => 'Việt Nam', 'harvest_date' => '2026-09-03', 'packaged_date' => '2026-09-03',
        ])->assertSessionHasErrors('batch_code');
    }

    public function test_trace_only_shows_approved_certificates(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $batch = ProductBatch::create([
            'product_id' => $this->productFor($seller)->id,
            'batch_code' => 'LO-TRACE-001', 'origin' => 'Đà Lạt',
            'production_date' => '2026-09-01', 'harvest_date' => '2026-09-01',
            'packaged_date' => '2026-09-02',
        ]);
        $batch->certificates()->createMany([
            ['name' => 'VietGAP', 'file_path' => 'certificates/vietgap.pdf', 'status' => Certificate::STATUS_APPROVED],
            ['name' => 'Chờ duyệt', 'file_path' => 'certificates/pending.pdf', 'status' => Certificate::STATUS_PENDING],
        ]);

        $this->get(route('trace.show', $batch->batch_code))
            ->assertOk()->assertSee('VietGAP')->assertDontSee('Chờ duyệt');
        $this->get(route('trace.show', 'KHONG-TON-TAI'))->assertNotFound();
    }

    public function test_certificate_rejects_executable_upload(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $batch = ProductBatch::create([
            'product_id' => $this->productFor($seller)->id,
            'batch_code' => 'LO-FILE-001', 'origin' => 'Việt Nam',
            'production_date' => '2026-09-01', 'harvest_date' => '2026-09-01',
            'packaged_date' => '2026-09-02',
        ]);

        $this->actingAs($seller)->post(route('seller.batches.certificates.store', $batch), [
            'name' => 'Tệp không hợp lệ',
            'file' => UploadedFile::fake()->create('virus.exe', 10, 'application/octet-stream'),
        ])->assertSessionHasErrors('file');
    }

    public function test_certificate_rejects_file_larger_than_five_megabytes(): void
    {
        $seller = User::factory()->create(['role' => 'seller']);
        $batch = ProductBatch::create([
            'product_id' => $this->productFor($seller)->id,
            'batch_code' => 'LO-LARGE-FILE-001', 'origin' => 'Việt Nam',
            'production_date' => '2026-09-01', 'harvest_date' => '2026-09-01',
            'packaged_date' => '2026-09-02',
        ]);

        $this->actingAs($seller)->post(route('seller.batches.certificates.store', $batch), [
            'name' => 'Tệp quá dung lượng',
            'file' => UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf'),
        ])->assertSessionHasErrors('file');
    }

    public function test_certificate_files_are_private_until_approved(): void
    {
        Storage::fake('local');
        $owner = User::factory()->create(['role' => 'seller']);
        $otherSeller = User::factory()->create(['role' => 'seller']);
        $batch = ProductBatch::create([
            'product_id' => $this->productFor($owner)->id,
            'batch_code' => 'LO-PRIVATE-001', 'origin' => 'Việt Nam',
            'production_date' => '2026-09-01', 'harvest_date' => '2026-09-01',
            'packaged_date' => '2026-09-02',
        ]);
        Storage::disk('local')->put('certificates/private.pdf', 'demo certificate');
        $certificate = $batch->certificates()->create([
            'name' => 'Chứng nhận riêng tư',
            'file_path' => 'certificates/private.pdf',
        ]);

        $this->get(route('trace.certificates.show', $certificate))->assertNotFound();
        $this->actingAs($otherSeller)
            ->get(route('seller.certificates.show', $certificate))
            ->assertForbidden();
        $this->actingAs($owner)
            ->get(route('seller.certificates.show', $certificate))
            ->assertOk();

        $certificate->update(['status' => Certificate::STATUS_APPROVED]);
        $this->get(route('trace.certificates.show', $certificate))->assertOk();
    }

    public function test_only_admin_can_access_dashboard_and_approve_certificate(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $seller = User::factory()->create(['role' => 'seller']);
        $certificate = ProductBatch::create([
            'product_id' => $this->productFor($seller)->id,
            'batch_code' => 'LO-ADMIN-001', 'origin' => 'Việt Nam',
            'production_date' => '2026-09-01', 'harvest_date' => '2026-09-01',
            'packaged_date' => '2026-09-02',
        ])->certificates()->create(['name' => 'OCOP', 'file_path' => 'certificates/ocop.pdf']);

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Trung tâm điều hành')
            ->assertSee('Hàng đợi cần xử lý');
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.products.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.certificates.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.reviews.index'))->assertOk();
        $this->actingAs($admin)->patch(route('admin.certificates.review', $certificate), ['status' => 'approved'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('certificates', ['id' => $certificate->id, 'status' => 'approved', 'approved_by' => $admin->id]);
    }

    public function test_admin_can_hide_review_and_protect_last_admin_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = $this->productFor(User::factory()->create(['role' => 'seller']));
        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 2,
            'comment' => 'Bình luận cần ẩn',
            'status' => Review::STATUS_APPROVED,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.reviews.review', $review), ['status' => Review::STATUS_REJECTED])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'status' => Review::STATUS_REJECTED,
            'moderated_by' => $admin->id,
        ]);
        $this->get(route('products.show', $product->id))
            ->assertOk()
            ->assertDontSee('Bình luận cần ẩn');

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $admin), ['role' => 'customer'])
            ->assertSessionHas('error');
        $this->actingAs($admin)
            ->patch(route('admin.users.status', $admin))
            ->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_locked_account_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'email' => 'locked@example.com',
            'password' => 'password',
            'is_active' => false,
        ]);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function productFor(User $seller): Product
    {
        $category = Category::create(['name' => 'Trái cây', 'slug' => 'trai-cay-'.uniqid(), 'status' => true]);

        return Product::create(['seller_id' => $seller->id, 'category_id' => $category->id, 'name' => 'Cam thử nghiệm', 'price' => 10000, 'stock' => 10, 'status' => true]);
    }
}
