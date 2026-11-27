<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_guest_and_kasir_cannot_access_admin_products(): void
    {
        $this->get('/admin/products')->assertRedirect('/login');

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->get('/admin/products')->assertStatus(403);
    }

    public function test_admin_can_view_empty_state_and_product_list_with_rupiah_format(): void
    {
        $admin = $this->createAdmin();

        $emptyResponse = $this->actingAs($admin)->get('/admin/products');
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertSee('Belum ada menu terdaftar');

        $category = Category::query()->create(['name' => 'Coffee']);
        Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu Gula Aren',
            'price' => 28000,
            'is_available' => true,
        ]);

        $listResponse = $this->actingAs($admin)->get('/admin/products');
        $listResponse->assertStatus(200);
        $listResponse->assertSee('Kopi Susu Gula Aren');
        $listResponse->assertSee('Coffee');
        $listResponse->assertSee('Rp 28.000');
        $listResponse->assertSee('Tersedia');
        $listResponse->assertDontSee('Belum ada menu terdaftar');
    }

    public function test_admin_can_filter_products_by_search_category_and_availability(): void
    {
        $admin = $this->createAdmin();
        $coffee = Category::query()->create(['name' => 'Coffee']);
        $pastry = Category::query()->create(['name' => 'Pastry']);

        Product::query()->create([
            'category_id' => $coffee->id,
            'name' => 'Americano',
            'price' => 22000,
            'is_available' => true,
        ]);
        Product::query()->create([
            'category_id' => $pastry->id,
            'name' => 'Croissant',
            'price' => 25000,
            'is_available' => false,
        ]);

        $filtered = $this->actingAs($admin)->get('/admin/products?category_id=' . $pastry->id . '&availability=unavailable');
        $filtered->assertStatus(200);
        $filtered->assertSee('Croissant');
        $filtered->assertDontSee('Americano');
    }

    public function test_admin_can_create_product_with_image_upload(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $category = Category::query()->create(['name' => 'Signature']);

        $file = UploadedFile::fake()->image('matcha.jpg', 400, 400);

        $response = $this->actingAs($admin)->post('/admin/products', [
            'category_id' => $category->id,
            'name' => 'Matcha Latte',
            'price' => 32000,
            'image' => $file,
            'is_available' => '1',
        ]);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('status');

        $product = Product::query()->where('name', 'Matcha Latte')->firstOrFail();
        $this->assertSame($category->id, $product->category_id);
        $this->assertSame(32000, $product->price);
        $this->assertTrue($product->is_available);
        $this->assertNotNull($product->image);
        $this->assertStringStartsWith('products/', $product->image);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_admin_can_update_product_and_replace_old_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $coffee = Category::query()->create(['name' => 'Coffee']);
        $nonCoffee = Category::query()->create(['name' => 'Non-Coffee']);

        $oldPath = UploadedFile::fake()->image('old.jpg')->store('products', 'public');
        $product = Product::query()->create([
            'category_id' => $coffee->id,
            'name' => 'Cappuccino',
            'price' => 26000,
            'image' => $oldPath,
            'is_available' => true,
        ]);

        $newFile = UploadedFile::fake()->image('new.png');

        $response = $this->actingAs($admin)->put("/admin/products/{$product->id}", [
            'category_id' => $nonCoffee->id,
            'name' => 'Cokelat Panas',
            'price' => 24000,
            'image' => $newFile,
            'is_available' => '0',
        ]);

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('status');

        $product->refresh();
        $this->assertSame($nonCoffee->id, $product->category_id);
        $this->assertSame('Cokelat Panas', $product->name);
        $this->assertSame(24000, $product->price);
        $this->assertFalse($product->is_available);
        $this->assertNotSame($oldPath, $product->image);

        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_admin_can_delete_product_and_its_stored_image(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();
        $category = Category::query()->create(['name' => 'Pastry']);

        $imagePath = UploadedFile::fake()->image('brownies.jpg')->store('products', 'public');
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Fudge Brownies',
            'price' => 20000,
            'image' => $imagePath,
            'is_available' => true,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/products/{$product->id}");

        $response->assertRedirect('/admin/products');
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_admin_can_toggle_product_availability_via_json_and_redirect(): void
    {
        $admin = $this->createAdmin();
        $category = Category::query()->create(['name' => 'Coffee']);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'V60 Pour Over',
            'price' => 30000,
            'is_available' => true,
        ]);

        $jsonResponse = $this->actingAs($admin)
            ->patchJson("/admin/products/{$product->id}/toggle-availability");

        $jsonResponse->assertOk()
            ->assertJson([
                'id' => $product->id,
                'is_available' => false,
                'status_label' => 'Stok Habis',
            ]);

        $this->assertFalse($product->fresh()->is_available);

        $redirectResponse = $this->actingAs($admin)
            ->patch("/admin/products/{$product->id}/toggle-availability");

        $redirectResponse->assertRedirect('/admin/products');
        $redirectResponse->assertSessionHas('status');
        $this->assertTrue($product->fresh()->is_available);
    }

    public function test_admin_products_uses_live_auto_filter_without_manual_filter_button(): void
    {
        $admin = $this->createAdmin();
        $category = Category::query()->create(['name' => 'Coffee']);
        Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Kopi Tubruk',
            'price' => 18000,
            'is_available' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin/products');
        $response->assertStatus(200);
        $response->assertSee('@input.debounce.400ms="submitFilter(true)"', false);
        $response->assertSee('@change="submitFilter()"', false);
        $response->assertDontSee('Filter</button>', false);

        $html = $response->getContent();
        $this->assertSame(1, preg_match('/<form[^>]*x-ref="filterForm"[\s\S]*?<\/form>/', $html, $matches));
        $this->assertStringNotContainsString('type="submit"', $matches[0]);

        $filteredResponse = $this->actingAs($admin)->get('/admin/products?search=Kop');
        $filteredResponse->assertStatus(200);
        $filteredResponse->assertSee('Kopi Tubruk');
        $filteredResponse->assertSee('Reset');
    }
}
