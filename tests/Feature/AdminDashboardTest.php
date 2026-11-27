<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_dashboard_aggregates_revenue_chart_for_day_week_month_and_year(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 27, 15, 30, 0));

        $admin = User::factory()->create(['role' => 'admin']);

        $this->createOrderAt($admin, 'INV-20261127-0001', 45000, Carbon::create(2026, 11, 27, 9, 15, 0));
        $this->createOrderAt($admin, 'INV-20261127-0002', 80000, Carbon::create(2026, 11, 27, 14, 40, 0));
        $this->createOrderAt($admin, 'INV-20261124-0001', 60000, Carbon::create(2026, 11, 24, 10, 0, 0));
        $this->createOrderAt($admin, 'INV-20261105-0001', 100000, Carbon::create(2026, 11, 5, 11, 0, 0));
        $this->createOrderAt($admin, 'INV-20260310-0001', 200000, Carbon::create(2026, 3, 10, 12, 0, 0));
        $this->createOrderAt($admin, 'INV-20251127-0001', 500000, Carbon::create(2025, 11, 27, 12, 0, 0));

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Tren Pendapatan');
        $response->assertSee('Hari');
        $response->assertSee('Minggu');
        $response->assertSee('Bulan');
        $response->assertSee('Tahun');
        $response->assertSee('bg-[#9A3412]', false);
        $response->assertDontSee('—');

        $revenueChart = $response->viewData('revenueChart');

        $this->assertCount(24, $revenueChart['day']['labels']);
        $this->assertSame('00:00', $revenueChart['day']['labels'][0]);
        $this->assertSame('23:00', $revenueChart['day']['labels'][23]);
        $this->assertSame(45000, $revenueChart['day']['values'][9]);
        $this->assertSame(80000, $revenueChart['day']['values'][14]);
        $this->assertSame(125000, $revenueChart['day']['total']);
        $this->assertSame(2, $revenueChart['day']['transactions']);

        $this->assertCount(7, $revenueChart['week']['labels']);
        $this->assertSame(185000, $revenueChart['week']['total']);
        $this->assertSame(3, $revenueChart['week']['transactions']);

        $this->assertCount(30, $revenueChart['month']['labels']);
        $this->assertSame(285000, $revenueChart['month']['total']);
        $this->assertSame(4, $revenueChart['month']['transactions']);

        $this->assertCount(12, $revenueChart['year']['labels']);
        $this->assertSame('Jan', $revenueChart['year']['labels'][0]);
        $this->assertSame('Des', $revenueChart['year']['labels'][11]);
        $this->assertSame(200000, $revenueChart['year']['values'][2]);
        $this->assertSame(285000, $revenueChart['year']['values'][10]);
        $this->assertSame(485000, $revenueChart['year']['total']);
        $this->assertSame(5, $revenueChart['year']['transactions']);
    }

    public function test_deleted_order_is_excluded_from_dashboard_revenue_chart(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 11, 27, 15, 30, 0));

        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->createOrderAt($admin, 'INV-20261127-9999', 50000, Carbon::create(2026, 11, 27, 10, 0, 0));

        $this->actingAs($admin)
            ->delete("/admin/reports/{$order->id}")
            ->assertRedirect();

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        $revenueChart = $response->viewData('revenueChart');
        $this->assertSame(0, $revenueChart['day']['total']);
        $this->assertSame(0, $revenueChart['week']['total']);
        $this->assertSame(0, $revenueChart['month']['total']);
        $this->assertSame(0, $revenueChart['year']['total']);
    }

    private function createOrderAt(User $user, string $invoice, int $totalPrice, Carbon $timestamp): Order
    {
        $order = Order::query()->create([
            'user_id' => $user->id,
            'invoice_number' => $invoice,
            'customer_name' => 'Pelanggan Uji',
            'order_type' => 'takeaway',
            'total_price' => $totalPrice,
            'pay_amount' => $totalPrice,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $order->forceFill([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ])->save();

        return $order;
    }
}
