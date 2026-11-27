<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRoleAndSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_neutral_initial_data(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@cafe.test',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'kasir@cafe.test',
            'role' => 'kasir',
        ]);

        $this->assertDatabaseHas('settings', [
            'cafe_name' => 'Nama Cafe',
        ]);

        $this->assertSame(3, Category::query()->count());
        $this->assertDatabaseHas('categories', ['name' => 'Coffee']);
        $this->assertDatabaseHas('categories', ['name' => 'Non-Coffee']);
        $this->assertDatabaseHas('categories', ['name' => 'Pastry']);

        $this->assertSame(6, Product::query()->count());
    }

    public function test_login_page_displays_cafe_name_from_settings_and_demo_buttons(): void
    {
        Setting::query()->create([
            'cafe_name' => 'Kopi Nusantara',
            'address' => 'Jl. Melati No. 1',
            'phone' => '021-5550199',
        ]);

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Kopi Nusantara');
        $response->assertSee('Isi Akun Demo Admin');
        $response->assertSee('Isi Akun Demo Kasir');
    }

    public function test_login_page_falls_back_to_app_name_when_settings_is_empty(): void
    {
        config(['app.name' => 'Cafe Fallback Name']);

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Cafe Fallback Name');
    }

    public function test_admin_login_redirects_to_admin_dashboard_and_grants_access(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@cafe.test',
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect('/admin/dashboard');

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertStatus(200);
    }

    public function test_kasir_login_redirects_to_pos_and_blocks_admin_routes(): void
    {
        $kasir = User::factory()->create([
            'email' => 'kasir@cafe.test',
            'role' => 'kasir',
        ]);

        $response = $this->post('/login', [
            'email' => $kasir->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($kasir);
        $response->assertRedirect('/pos');

        $this->actingAs($kasir)
            ->get('/pos')
            ->assertStatus(200);

        $this->actingAs($kasir)
            ->get('/admin/dashboard')
            ->assertStatus(403);
    }
}
