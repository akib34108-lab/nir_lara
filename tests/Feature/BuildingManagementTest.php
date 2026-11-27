<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Floor;
use App\Models\Sites;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BuildingManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test-dashboard', fn () => response()->noContent())->name('dashboard');
    }

    public function test_super_admin_can_create_a_building_for_a_site(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $site = $this->createSite('SS-BUILD-01');

        $response = $this->actingAs($user)->post(route('buildings.store'), $this->buildingAttributes($site));

        $response->assertRedirect(route('buildings.index'));
        $this->assertDatabaseHas('buildings', [
            'site_id' => $site->id,
            'code' => 'BLD-TEST-01',
            'name' => 'Test Building',
            'total_floors' => 6,
        ]);
    }

    public function test_building_update_allows_its_existing_code(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $site = $this->createSite('SS-BUILD-02');
        $building = Building::create($this->buildingAttributes($site));

        $attributes = $this->buildingAttributes($site);
        $attributes['name'] = 'Updated Building';

        $this->actingAs($user)
            ->put(route('buildings.update', $building), $attributes)
            ->assertRedirect(route('buildings.index'));

        $this->assertDatabaseHas('buildings', [
            'id' => $building->id,
            'code' => $building->code,
            'name' => 'Updated Building',
        ]);
    }

    public function test_building_code_must_be_unique(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $site = $this->createSite('SS-BUILD-UNIQUE');
        Building::create($this->buildingAttributes($site, 'BLD-UNIQUE-01', 'Original Building'));

        $duplicate = $this->buildingAttributes($site, 'BLD-UNIQUE-01', 'Another Building');
        $this->actingAs($user)
            ->post(route('buildings.store'), $duplicate)
            ->assertSessionHasErrors('code');

        $this->assertDatabaseCount('buildings', 1);
    }

    public function test_building_list_filters_by_search_site_and_status(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $firstSite = $this->createSite('SS-BUILD-03');
        $secondSite = $this->createSite('SS-BUILD-04');
        Building::create($this->buildingAttributes($firstSite, 'BLD-NORTH', 'North Tower'));
        Building::create($this->buildingAttributes($secondSite, 'BLD-SOUTH', 'South Tower'));

        $response = $this->actingAs($user)->get(route('buildings.index', [
            'search' => 'Tower',
            'site_id' => $firstSite->id,
            'status' => 'planning',
        ]));

        $response->assertOk()
            ->assertSee('North Tower')
            ->assertDontSee('South Tower');
    }

    public function test_building_cannot_be_deleted_when_it_has_floors(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $site = $this->createSite('SS-BUILD-05');
        $building = Building::create($this->buildingAttributes($site));
        Floor::create([
            'building_id' => $building->id,
            'name' => 'Ground Floor',
            'floor_number' => 0,
            'floor_type' => 'ground',
            'floor_area_unit' => 'sqft',
            'status' => 'planned',
        ]);

        $this->actingAs($user)
            ->delete(route('buildings.destroy', $building))
            ->assertRedirect(route('buildings.index'))
            ->assertSessionHas('error', 'This building cannot be deleted because it has floors.');

        $this->assertDatabaseHas('buildings', [
            'id' => $building->id,
            'deleted_at' => null,
        ]);
    }

    public function test_shareholder_can_view_buildings_but_cannot_create_them(): void
    {
        $user = $this->createUserWithRole('Shareholder');

        $this->actingAs($user)
            ->get(route('buildings.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('buildings.create'))
            ->assertForbidden();
    }

    private function createUserWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createSite(string $code): Sites
    {
        return Sites::create([
            'project_code' => $code,
            'project_name' => 'Test Site '.$code,
            'project_status' => 1,
            'address' => 'Test address',
            'city' => 'Dhaka',
            'district' => 'Dhaka',
            'country' => 'Bangladesh',
            'land_area' => 10,
            'land_area_unit' => 1,
        ]);
    }

    private function buildingAttributes(Sites $site, string $code = 'BLD-TEST-01', string $name = 'Test Building'): array
    {
        return [
            'site_id' => $site->id,
            'name' => $name,
            'code' => $code,
            'building_type' => 'Residential',
            'structure_type' => 'RCC Frame',
            'total_floors' => 6,
            'total_flats' => 24,
            'basement_count' => 1,
            'elevator_count' => 2,
            'parking_capacity' => 20,
            'status' => 'planning',
            'description' => 'Test building description',
        ];
    }
}
