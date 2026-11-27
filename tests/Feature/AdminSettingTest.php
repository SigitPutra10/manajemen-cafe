<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSettingTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_guest_and_kasir_cannot_access_admin_settings(): void
    {
        $this->get('/admin/settings')->assertRedirect('/login');
        $this->put('/admin/settings', ['cafe_name' => 'Test'])->assertRedirect('/login');

        $kasir = User::factory()->create(['role' => 'kasir']);
        $this->actingAs($kasir)->get('/admin/settings')->assertStatus(403);
        $this->actingAs($kasir)->put('/admin/settings', ['cafe_name' => 'Test'])->assertStatus(403);
    }

    public function test_admin_can_view_settings_page_with_existing_values(): void
    {
        Setting::query()->create([
            'cafe_name' => 'Kopi Nusantara',
            'address' => 'Jl. Sudirman No. 88',
            'phone' => '021-5559999',
        ]);

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Kafe');
        $response->assertSee('Kopi Nusantara');
        $response->assertSee('Jl. Sudirman No. 88');
        $response->assertSee('021-5559999');
    }

    public function test_admin_can_update_settings_and_changes_reflect_in_navbar_and_title(): void
    {
        Setting::query()->create([
            'cafe_name' => 'Nama Lama',
            'address' => 'Alamat Lama',
            'phone' => '021-1111111',
        ]);

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->put('/admin/settings', [
            'cafe_name' => '  Kopi Senja Baru  ',
            'address' => '  Jl. Kemang Raya No. 5  ',
            'phone' => '  021-7778889  ',
        ]);

        $response->assertRedirect('/admin/settings');
        $response->assertSessionHas('status', 'Pengaturan kafe berhasil disimpan.');

        $this->assertDatabaseHas('settings', [
            'cafe_name' => 'Kopi Senja Baru',
            'address' => 'Jl. Kemang Raya No. 5',
            'phone' => '021-7778889',
        ]);

        // Verify navbar & page title reflect updated cafe_name on Admin & POS pages
        $adminDashboard = $this->actingAs($admin)->get('/admin/dashboard');
        $adminDashboard->assertStatus(200);
        $adminDashboard->assertSee('Kopi Senja Baru');
        $adminDashboard->assertSee('<title>Dashboard - Kopi Senja Baru</title>', false);

        $posPage = $this->actingAs($admin)->get('/pos');
        $posPage->assertStatus(200);
        $posPage->assertSee('Kopi Senja Baru');
        $posPage->assertSee('<title>Kasir POS - Kopi Senja Baru</title>', false);
    }

    public function test_admin_can_create_settings_when_table_is_empty_and_nullable_fields_work(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->put('/admin/settings', [
            'cafe_name' => 'Roastery Pagi',
            'address' => '   ',
            'phone' => '',
        ]);

        $response->assertRedirect('/admin/settings');
        $this->assertDatabaseHas('settings', [
            'cafe_name' => 'Roastery Pagi',
            'address' => null,
            'phone' => null,
        ]);
    }

    public function test_cafe_name_is_required_and_validated(): void
    {
        $admin = $this->createAdmin();

        $emptyResponse = $this->actingAs($admin)->put('/admin/settings', [
            'cafe_name' => '   ',
            'address' => 'Jl. Kopi No. 1',
            'phone' => '08123456789',
        ]);

        $emptyResponse->assertSessionHasErrors('cafe_name');

        $tooLongResponse = $this->actingAs($admin)->put('/admin/settings', [
            'cafe_name' => str_repeat('A', 101),
        ]);

        $tooLongResponse->assertSessionHasErrors('cafe_name');
    }
}
