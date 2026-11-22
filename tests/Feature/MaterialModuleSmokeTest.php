<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialModuleSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_material_module_pages_render_for_a_super_admin(): void
    {
        $user = User::factory()->create();
        $this->seed(RolePermissionSeeder::class);
        $user->assignRole('Super Admin');

        $paths = [
            '/materials',
            '/material-categories',
            '/suppliers',
            '/purchase-requests',
            '/purchase-orders',
            '/grns',
            '/inspections',
            '/inventory',
            '/inventory/low-stock',
            '/inventory/movements',
            '/material-issues',
            '/material-returns',
            '/stock-transfers',
            '/material-consumptions',
            '/stock-adjustments',
            '/material-reports',
        ];

        foreach ($paths as $path) {
            $response = $this->actingAs($user)->get($path);
            $response->assertStatus(200);
        }
    }
}
