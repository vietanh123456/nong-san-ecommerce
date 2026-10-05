<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StatisticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_revenue_statistics(): void
    {
        $response = $this->get(route('admin.api.statistics.revenue'));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_revenue_statistics(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this
            ->actingAs($customer)
            ->get(route('admin.api.statistics.revenue'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_revenue_statistics_excluding_cancelled_orders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = $this->createProduct($seller);

        // Đơn hoàn thành hôm nay: được tính doanh thu.
        $this->createOrder($customer, $product, Order::STATUS_COMPLETED, 100000, now());

        // Đơn đã hủy: không được tính doanh thu.
        $this->createOrder($customer, $product, Order::STATUS_CANCELLED, 500000, now());

        // Đơn hoàn thành tháng trước: không nằm trong "hôm nay" / "7 ngày" / "tháng hiện tại".
        $this->createOrder(
            $customer,
            $product,
            Order::STATUS_COMPLETED,
            200000,
            now()->subMonths(2)
        );

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.api.statistics.revenue'));

        $response->assertOk();
        $response->assertJsonPath('data.total_revenue', 300000);
        $response->assertJsonPath('data.today_revenue', 100000);
        $response->assertJsonPath('data.last_7_days_revenue', 100000);
        $response->assertJsonPath('data.current_month_revenue', 100000);
    }

    public function test_admin_can_filter_revenue_by_custom_date_range(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = $this->createProduct($seller);

        $this->createOrder(
            $customer,
            $product,
            Order::STATUS_COMPLETED,
            150000,
            Carbon::parse('2026-01-10')
        );

        $this->createOrder(
            $customer,
            $product,
            Order::STATUS_COMPLETED,
            250000,
            Carbon::parse('2026-02-10')
        );

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.api.statistics.revenue', [
                'from_date' => '2026-01-01',
                'to_date' => '2026-01-31',
            ]));

        $response->assertOk();
        $response->assertJsonPath('data.custom_range_revenue.revenue', 150000);
    }

    public function test_guest_cannot_access_statistics_page(): void
    {
        $response = $this->get(route('admin.statistics.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_customer_cannot_access_statistics_page(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this
            ->actingAs($customer)
            ->get(route('admin.statistics.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_view_statistics_page_with_revenue_and_top_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = $this->createProduct($seller, 'Xoài Cát');

        $this->createOrder($customer, $product, Order::STATUS_COMPLETED, 300000, now(), 3);
        $this->createOrder($customer, $product, Order::STATUS_CANCELLED, 500000, now());

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.statistics.index'));

        $response->assertOk();
        $response->assertViewIs('admin.statistics');
        $response->assertSee('Báo cáo doanh thu');
        $response->assertSee('300.000');
        $response->assertSee('Xoài Cát');
    }

    public function test_admin_can_filter_statistics_page_by_custom_date_range(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);
        $product = $this->createProduct($seller);

        $this->createOrder(
            $customer,
            $product,
            Order::STATUS_COMPLETED,
            150000,
            Carbon::parse('2026-01-10')
        );

        $this->createOrder(
            $customer,
            $product,
            Order::STATUS_COMPLETED,
            250000,
            Carbon::parse('2026-02-10')
        );

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.statistics.index', [
                'from_date' => '2026-01-01',
                'to_date' => '2026-01-31',
            ]));

        $response->assertOk();
        $response->assertSee('150.000');
    }

    public function test_admin_can_view_overview_statistics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer1 = User::factory()->create(['role' => 'customer']);
        $customer2 = User::factory()->create(['role' => 'customer']);
        $seller = User::factory()->create(['role' => 'seller']);

        $productA = $this->createProduct($seller, 'Xoài Cát');
        $productB = $this->createProduct($seller, 'Cam Sành');

        $this->createOrder($customer1, $productA, Order::STATUS_COMPLETED, 300000, now(), 3);
        $this->createOrder($customer2, $productB, Order::STATUS_COMPLETED, 100000, now(), 1);
        $this->createOrder($customer1, $productA, Order::STATUS_PENDING, 50000, now());
        $this->createOrder($customer2, $productB, Order::STATUS_CANCELLED, 70000, now());

        $response = $this
            ->actingAs($admin)
            ->get(route('admin.api.statistics.overview'));

        $response->assertOk();
        $response->assertJsonPath('data.total_orders', 4);
        $response->assertJsonPath('data.orders_by_status.completed', 2);
        $response->assertJsonPath('data.orders_by_status.pending', 1);
        $response->assertJsonPath('data.orders_by_status.cancelled', 1);
        $response->assertJsonPath('data.total_customers', 2);
        $response->assertJsonPath('data.top_products_by_revenue.0.product_name', 'Xoài Cát');
        $response->assertJsonPath('data.top_products_by_quantity.0.product_name', 'Xoài Cát');
    }

    private function createProduct(User $seller, string $name = 'Cam Cao Phong'): Product
    {
        $category = Category::create([
            'name' => 'Trái cây '.uniqid(),
            'slug' => 'trai-cay-'.uniqid(),
            'description' => 'Danh mục dùng cho kiểm thử.',
            'status' => true,
        ]);

        return Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => $name,
            'description' => 'Sản phẩm dùng cho kiểm thử.',
            'price' => 50000,
            'stock' => 100,
            'origin' => 'Hòa Bình',
            'status' => true,
        ]);
    }

    private function createOrder(
        User $customer,
        Product $product,
        string $status,
        float $total,
        $createdAt,
        int $quantity = 1
    ): Order {
        $order = Order::create([
            'user_id' => $customer->id,
            'subtotal' => $total,
            'shipping_fee' => 0,
            'discount' => 0,
            'total' => $total,
            'payment_method' => 'cod',
            'payment_status' => $status === Order::STATUS_COMPLETED
                ? Order::PAYMENT_STATUS_PAID
                : Order::PAYMENT_STATUS_UNPAID,
            'status' => $status,
        ]);

        $order->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $total / $quantity,
            'subtotal' => $total,
        ]);

        return $order;
    }
}
