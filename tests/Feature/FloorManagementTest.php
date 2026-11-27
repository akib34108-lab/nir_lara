<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Floor;
use App\Models\FloorPlan;
use App\Models\Sites;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class FloorManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test-dashboard', fn () => response()->noContent())->name('dashboard');
    }

    public function test_floor_can_be_created_under_a_building_with_json_configuration(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $attributes = $this->floorAttributes();
        $attributes['configuration'] = json_encode(['orientation' => 'north']);

        $response = $this->actingAs($user)->post(route('buildings.floors.store', $building), $attributes);

        $response->assertRedirect();
        $floor = Floor::query()->where('name', 'Ground Floor')->firstOrFail();
        $this->assertSame(['orientation' => 'north'], $floor->configuration);
        $this->assertSame($building->id, $floor->building_id);
        $this->assertTrue($floor->building->is($building));
    }

    public function test_floor_number_must_be_unique_within_its_building(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $building->floors()->create($this->floorAttributes());
        $duplicate = $this->floorAttributes();
        $duplicate['name'] = 'Duplicate Number';

        $this->actingAs($user)
            ->post(route('buildings.floors.store', $building), $duplicate)
            ->assertSessionHasErrors('floor_number');
    }

    public function test_floor_name_must_be_unique_and_floor_number_must_be_in_range(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $building->floors()->create($this->floorAttributes());

        $duplicateName = $this->floorAttributes('Ground Floor', 1, 'residential');
        $this->actingAs($user)
            ->post(route('buildings.floors.store', $building), $duplicateName)
            ->assertSessionHasErrors('name');

        $outOfRange = $this->floorAttributes('Too Low', -11, 'basement');
        $this->actingAs($user)
            ->post(route('buildings.floors.store', $building), $outOfRange)
            ->assertSessionHasErrors('floor_number');
    }

    public function test_floor_update_allows_its_existing_number_and_name(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $floor = $building->floors()->create($this->floorAttributes());
        $attributes = $this->floorAttributes();
        $attributes['configuration'] = null;

        $this->actingAs($user)
            ->put(route('floors.update', $floor), $attributes)
            ->assertRedirect(route('floors.show', $floor));
    }

    public function test_global_floor_list_filters_and_orders_floors_by_number(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $building->floors()->create($this->floorAttributes('Upper Floor', 1, 'residential', 'planned'));
        $building->floors()->create($this->floorAttributes('Basement', -1, 'basement', 'active'));
        $building->floors()->create($this->floorAttributes('Ground Floor', 0, 'ground', 'active'));

        $response = $this->actingAs($user)->get(route('floors.index', [
            'building_id' => $building->id,
            'site_id' => $building->site_id,
        ]));

        $response->assertOk();
        $this->assertSame([-1, 0, 1], $response->viewData('floors')->pluck('floor_number')->all());

        $filteredResponse = $this->actingAs($user)->get(route('floors.index', [
            'search' => 'Ground',
            'building_id' => $building->id,
            'site_id' => $building->site_id,
            'floor_type' => 'ground',
            'status' => 'active',
        ]));

        $filteredResponse->assertOk();
        $this->assertSame(['Ground Floor'], $filteredResponse->viewData('floors')->pluck('name')->all());
    }

    public function test_floor_create_configuration_and_detail_screens_render(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $floor = $building->floors()->create($this->floorAttributes());

        $this->actingAs($user)
            ->get(route('buildings.floors.create', $building))
            ->assertOk()
            ->assertSee('Add Floor');

        $this->actingAs($user)
            ->get(route('floors.edit', $floor))
            ->assertOk()
            ->assertSee('Floor Configuration');

        $this->actingAs($user)
            ->get(route('floors.show', $floor))
            ->assertOk()
            ->assertSee('Summary &amp; Configuration', false)
            ->assertSee('Floor Plans');
    }

    public function test_building_details_warn_when_floor_counts_exceed_building_configuration(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding([
            'total_floors' => 1,
            'basement_count' => 0,
        ]);
        $building->floors()->create($this->floorAttributes('Ground Floor', 0, 'ground', 'active'));
        $building->floors()->create($this->floorAttributes('First Floor', 1, 'residential', 'active'));
        $building->floors()->create($this->floorAttributes('Basement', -1, 'basement', 'active'));

        $this->actingAs($user)
            ->get(route('buildings.show', $building))
            ->assertOk()
            ->assertSee('Configured total floors: 1. Existing floor records numbered 0 or above: 2.')
            ->assertSee('Configured basement count: 0. Existing basement floor records: 1.');
    }

    public function test_floor_cannot_be_deleted_when_floor_plans_exist(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $floor = $building->floors()->create($this->floorAttributes());
        FloorPlan::create([
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'title' => 'Ground Floor Plan',
            'file_path' => 'property/floor-plans/'.$building->id.'/plan.pdf',
            'original_name' => 'plan.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);

        $this->actingAs($user)
            ->delete(route('floors.destroy', $floor))
            ->assertRedirect(route('floors.show', $floor))
            ->assertSessionHas('error', 'This floor cannot be deleted because it has floor plans.');

        $this->assertDatabaseHas('floors', [
            'id' => $floor->id,
            'deleted_at' => null,
        ]);
    }

    public function test_floor_can_be_soft_deleted_when_it_has_no_floor_plans(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $floor = $building->floors()->create($this->floorAttributes());

        $this->actingAs($user)
            ->delete(route('floors.destroy', $floor))
            ->assertRedirect(route('buildings.show', $building));

        $this->assertSoftDeleted('floors', ['id' => $floor->id]);
    }

    public function test_shareholder_can_view_floor_list_but_cannot_add_a_floor(): void
    {
        $user = $this->createUserWithRole('Shareholder');
        $building = $this->createBuilding();

        $this->actingAs($user)
            ->get(route('floors.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('buildings.floors.create', $building))
            ->assertForbidden();
    }

    private function createUserWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createBuilding(array $overrides = []): Building
    {
        $site = Sites::create([
            'project_code' => 'SS-FLOOR-01',
            'project_name' => 'Floor Test Site',
            'project_status' => 1,
            'address' => 'Test address',
            'city' => 'Dhaka',
            'district' => 'Dhaka',
            'country' => 'Bangladesh',
            'land_area' => 10,
            'land_area_unit' => 1,
        ]);

        return Building::create(array_merge([
            'site_id' => $site->id,
            'name' => 'Floor Test Building',
            'code' => 'BLD-FLOOR-01',
            'building_type' => 'Residential',
            'structure_type' => 'RCC Frame',
            'total_floors' => 6,
            'total_flats' => 24,
            'basement_count' => 1,
            'elevator_count' => 2,
            'parking_capacity' => 20,
            'status' => 'planning',
        ], $overrides));
    }

    private function floorAttributes(
        string $name = 'Ground Floor',
        int $floorNumber = 0,
        string $floorType = 'ground',
        string $status = 'planned'
    ): array {
        return [
            'name' => $name,
            'floor_number' => $floorNumber,
            'floor_type' => $floorType,
            'floor_area' => 1200,
            'floor_area_unit' => 'sqft',
            'common_area' => 100,
            'description' => 'Test floor description',
            'notes' => 'Test notes',
            'configuration' => null,
            'status' => $status,
        ];
    }
}
