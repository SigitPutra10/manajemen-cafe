<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_sidebar_displays_dynamic_cafe_name_and_navigation_items(): void
    {
        Setting::query()->create([
            'cafe_name' => 'Kedai Kopi Senja',
            'address' => 'Jl. Merdeka No. 10',
            'phone' => '021-5550111',
        ]);

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Kedai Kopi Senja');
        $response->assertSee('Dashboard');
        $response->assertSee('Kategori');
        $response->assertSee('Menu/Produk');
        $response->assertSee('Laporan');
        $response->assertSee('POS Kasir');
        $response->assertSee('Antrean Barista');
        $response->assertSee('Pengaturan Kafe');
    }

    public function test_guest_and_kasir_cannot_access_admin_categories(): void
    {
        $this->get('/admin/categories')->assertRedirect('/login');

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->get('/admin/categories')->assertStatus(403);
    }

    public function test_admin_can_view_empty_state_and_category_list_with_product_count(): void
    {
        $admin = $this->createAdmin();

        $emptyResponse = $this->actingAs($admin)->get('/admin/categories');
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertSee('Belum ada kategori menu');

        $category = Category::query()->create(['name' => 'Coffee']);
        Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Espresso',
            'price' => 18000,
            'is_available' => true,
        ]);

        $listResponse = $this->actingAs($admin)->get('/admin/categories');
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Coffee');
        $listResponse->assertDontSee('Belum ada kategori menu');
    }

    public function test_admin_can_create_category_and_validates_duplicate(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Signature',
        ]);

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('categories', ['name' => 'Signature']);

        $duplicate = $this->actingAs($admin)->post('/admin/categories', [
            'name' => 'Signature',
        ]);

        $duplicate->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_category(): void
    {
        $admin = $this->createAdmin();
        $category = Category::query()->create(['name' => 'Pastry']);

        $response = $this->actingAs($admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Bakery & Pastry',
        ]);

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('status');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Bakery & Pastry',
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        $admin = $this->createAdmin();
        $category = Category::query()->create(['name' => 'Merchandise']);

        $response = $this->actingAs($admin)->delete("/admin/categories/{$category->id}");

        $response->assertRedirect('/admin/categories');
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
