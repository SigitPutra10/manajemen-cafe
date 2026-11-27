<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosAndReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_page_renders_updated_layout_header_badges_and_accent_for_kasir_and_admin(): void
    {
        $category = Category::query()->create(['name' => 'Coffee']);
        Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu',
            'price' => 25000,
            'is_available' => true,
        ]);

        $kasir = User::factory()->create([
            'name' => 'Kasir Satu',
            'role' => 'kasir',
        ]);

        $kasirResponse = $this->actingAs($kasir)->get('/pos');
        $kasirResponse->assertStatus(200);
        $kasirResponse->assertDontSee('Dashboard Admin');
        $kasirResponse->assertSee('Antrean Pesanan');
        $kasirResponse->assertSee('Kasir Satu');
        $kasirResponse->assertSee('inline-flex items-center m-0', false);
        $kasirResponse->assertSee('bg-[#9A3412]', false);
        $kasirResponse->assertSee('Kosongkan');
        $kasirResponse->assertSee('Coffee');
        $kasirResponse->assertSee('stroke-width="1.5"', false);

        $admin = User::factory()->create([
            'name' => 'Admin Utama',
            'role' => 'admin',
        ]);

        $adminResponse = $this->actingAs($admin)->get('/pos');
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee('Dashboard Admin');
    }

    public function test_receipt_page_disables_auto_print_and_places_actions_below_receipt(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $category = Category::query()->create(['name' => 'Coffee']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Americano',
            'price' => 22000,
            'is_available' => true,
        ]);

        $order = Order::query()->create([
            'user_id' => $kasir->id,
            'invoice_number' => 'INV-20261127-0001',
            'customer_name' => 'Budi',
            'table_number' => '04',
            'order_type' => 'dine_in',
            'total_price' => 22000,
            'pay_amount' => 50000,
            'change_amount' => 28000,
            'payment_method' => 'cash',
            'status' => 'processing',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 22000,
            'subtotal' => 22000,
        ]);

        $response = $this->actingAs($kasir)->get("/orders/{$order->id}/receipt");
        $response->assertStatus(200);
        $response->assertDontSee("window.addEventListener('load'", false);
        $response->assertSee('Cetak Struk');
        $response->assertSee('Kembali ke POS');
        $response->assertSee('onclick="window.print()"', false);

        $html = $response->getContent();
        $receiptPos = strpos($html, 'Terima kasih sudah berkunjung.');
        $actionsPos = strpos($html, 'class="no-print');
        $this->assertNotFalse($receiptPos);
        $this->assertNotFalse($actionsPos);
        $this->assertGreaterThan($receiptPos, $actionsPos);
    }

    public function test_guest_and_kasir_cannot_delete_report_transaction(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $order = Order::query()->create([
            'user_id' => $kasir->id,
            'invoice_number' => 'INV-20261127-0002',
            'customer_name' => 'Siti',
            'order_type' => 'takeaway',
            'total_price' => 20000,
            'pay_amount' => 20000,
            'change_amount' => 0,
            'payment_method' => 'qris',
            'status' => 'completed',
        ]);

        $this->delete("/admin/reports/{$order->id}")->assertRedirect('/login');
        $this->actingAs($kasir)->delete("/admin/reports/{$order->id}")->assertStatus(403);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_admin_can_view_delete_controls_and_delete_transaction_with_items(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::query()->create(['name' => 'Pastry']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Croissant',
            'price' => 25000,
            'is_available' => true,
        ]);

        $order = Order::query()->create([
            'user_id' => $admin->id,
            'invoice_number' => 'INV-20261127-0003',
            'customer_name' => 'Andi',
            'table_number' => '02',
            'order_type' => 'dine_in',
            'total_price' => 50000,
            'pay_amount' => 50000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $item = OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 25000,
            'subtotal' => 50000,
        ]);

        $indexResponse = $this->actingAs($admin)->get('/admin/reports');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('INV-20261127-0003');
        $indexResponse->assertSee('Hapus');
        $indexResponse->assertSee('Hapus Transaksi');
        $indexResponse->assertSee('text-[#B91C1C]', false);

        $deleteResponse = $this->actingAs($admin)->delete("/admin/reports/{$order->id}", [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ]);

        $deleteResponse->assertRedirect(route('admin.reports.index', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ]));
        $deleteResponse->assertSessionHas('status', 'Transaksi INV-20261127-0003 berhasil dihapus.');

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_items', ['id' => $item->id]);
    }
}
