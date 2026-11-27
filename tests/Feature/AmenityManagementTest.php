<?php

namespace Tests\Feature;

use App\Models\Amenity;
use App\Models\Building;
use App\Models\Sites;
use App\Models\User;
use Database\Seeders\AmenitySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AmenityManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test-dashboard', fn () => response()->noContent())->name('dashboard');
    }

    public function test_starter_amenity_seeder_is_safe_to_run_more_than_once(): void
    {
        $this->seed(AmenitySeeder::class);
        $this->seed(AmenitySeeder::class);

        $this->assertDatabaseCount('amenities', 12);
        $this->assertDatabaseHas('amenities', [
            'name' => 'Fire Safety System',
            'category' => 'Safety',
            'status' => 'active',
        ]);
    }

    public function test_super_admin_can_create_update_filter_and_view_amenities(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $attributes = $this->amenityAttributes();

        $this->actingAs($user)
            ->get(route('amenities.create'))
            ->assertOk()
            ->assertSee('Add Amenity')
            ->assertSee('Themify icon class');

        $this->actingAs($user)
            ->post(route('amenities.store'), $attributes)
            ->assertRedirect(route('amenities.index'));

        $amenity = Amenity::query()->where('name', 'Emergency Generator')->firstOrFail();
        $this->assertDatabaseHas('amenities', [
            'id' => $amenity->id,
            'category' => 'Utilities',
            'icon' => 'ti-bolt',
        ]);

        $this->actingAs($user)
            ->get(route('amenities.edit', $amenity))
            ->assertOk()
            ->assertSee('Edit Amenity');

        $updated = $attributes;
        $updated['description'] = 'Backup power for common areas.';
        $this->actingAs($user)
            ->put(route('amenities.update', $amenity), $updated)
            ->assertRedirect(route('amenities.show', $amenity));

        $this->actingAs($user)
            ->get(route('amenities.index', ['search' => 'Generator', 'category' => 'Utilities', 'status' => 'active']))
            ->assertOk()
            ->assertSee('Emergency Generator')
            ->assertDontSee('CCTV');

        $this->actingAs($user)
            ->get(route('amenities.show', $amenity))
            ->assertOk()
            ->assertSee('Backup power for common areas.');
    }

    public function test_amenity_cannot_be_deleted_while_assigned_to_a_building(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $amenity = Amenity::create($this->amenityAttributes());
        $building->amenities()->attach($amenity->id);

        $this->actingAs($user)
            ->delete(route('amenities.destroy', $amenity))
            ->assertRedirect(route('amenities.index'))
            ->assertSessionHas('error', 'This amenity cannot be deleted because it is assigned to a building. Set it inactive instead.');

        $this->assertDatabaseHas('amenities', ['id' => $amenity->id]);
    }

    public function test_unassigned_amenity_can_be_deleted(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $amenity = Amenity::create($this->amenityAttributes());

        $this->actingAs($user)
            ->delete(route('amenities.destroy', $amenity))
            ->assertRedirect(route('amenities.index'))
            ->assertSessionHas('success', 'Amenity deleted successfully.');

        $this->assertDatabaseMissing('amenities', ['id' => $amenity->id]);
    }

    public function test_shareholder_can_view_amenities_but_cannot_manage_them(): void
    {
        $user = $this->createUserWithRole('Shareholder');
        $amenity = Amenity::create($this->amenityAttributes());

        $this->actingAs($user)
            ->get(route('amenities.index'))
            ->assertOk()
            ->assertSee($amenity->name);

        $this->actingAs($user)
            ->get(route('amenities.show', $amenity))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('amenities.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('amenities.edit', $amenity))
            ->assertForbidden();
    }

    public function test_building_can_receive_multiple_amenities_without_changing_existing_pivot_details(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $existing = Amenity::create($this->amenityAttributes('Existing Generator'));
        $newAmenity = Amenity::create($this->amenityAttributes('CCTV', 'Safety'));
        $building->amenities()->attach($existing->id, [
            'quantity' => 1,
            'notes' => 'Keep original note',
        ]);

        $this->actingAs($user)
            ->post(route('buildings.amenities.store', $building), [
                'amenity_ids' => [$newAmenity->id],
                'quantity' => [$newAmenity->id => 4],
                'notes' => [$newAmenity->id => 'Entry points'],
            ])
            ->assertRedirect(route('buildings.show', $building));

        $this->assertDatabaseHas('building_amenity', [
            'building_id' => $building->id,
            'amenity_id' => $existing->id,
            'quantity' => 1,
            'notes' => 'Keep original note',
        ]);
        $this->assertDatabaseHas('building_amenity', [
            'building_id' => $building->id,
            'amenity_id' => $newAmenity->id,
            'quantity' => 4,
            'notes' => 'Entry points',
        ]);

        $this->actingAs($user)
            ->get(route('buildings.show', $building))
            ->assertOk()
            ->assertSee('Amenities &amp; Facilities', false)
            ->assertSee('Existing Generator')
            ->assertSee('CCTV');
    }

    public function test_inactive_amenities_cannot_be_assigned_to_a_building(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $amenity = Amenity::create($this->amenityAttributes('Inactive Generator', 'Utilities', 'inactive'));

        $response = $this->actingAs($user)
            ->post(route('buildings.amenities.store', $building), [
                'amenity_ids' => [$amenity->id],
            ]);

        $response->assertSessionHasErrors('amenity_ids.0');
        $this->actingAs($user)
            ->get(route('buildings.show', $building))
            ->assertOk()
            ->assertSee('Please correct the following:');

        $this->assertDatabaseMissing('building_amenity', [
            'building_id' => $building->id,
            'amenity_id' => $amenity->id,
        ]);
    }

    public function test_building_amenity_pivot_details_can_be_updated_and_removed(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $amenity = Amenity::create($this->amenityAttributes());
        $building->amenities()->attach($amenity->id, [
            'quantity' => 1,
            'notes' => 'Old notes',
        ]);

        $this->actingAs($user)
            ->put(route('buildings.amenities.update', [$building, $amenity]), [
                'quantity' => 3,
                'notes' => 'Updated notes',
            ])
            ->assertRedirect(route('buildings.show', $building));

        $this->assertDatabaseHas('building_amenity', [
            'building_id' => $building->id,
            'amenity_id' => $amenity->id,
            'quantity' => 3,
            'notes' => 'Updated notes',
        ]);

        $this->actingAs($user)
            ->delete(route('buildings.amenities.destroy', [$building, $amenity]))
            ->assertRedirect(route('buildings.show', $building));

        $this->assertDatabaseMissing('building_amenity', [
            'building_id' => $building->id,
            'amenity_id' => $amenity->id,
        ]);
    }

    public function test_reassigning_an_existing_amenity_does_not_duplicate_or_clear_pivot_details(): void
    {
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $amenity = Amenity::create($this->amenityAttributes());
        $building->amenities()->attach($amenity->id, [
            'quantity' => 2,
            'notes' => 'Existing pivot details',
        ]);

        $this->actingAs($user)
            ->post(route('buildings.amenities.store', $building), [
                'amenity_ids' => [$amenity->id],
            ])
            ->assertRedirect(route('buildings.show', $building));

        $this->assertDatabaseCount('building_amenity', 1);
        $this->assertDatabaseHas('building_amenity', [
            'building_id' => $building->id,
            'amenity_id' => $amenity->id,
            'quantity' => 2,
            'notes' => 'Existing pivot details',
        ]);
    }

    public function test_shareholder_cannot_mutate_building_amenities(): void
    {
        $user = $this->createUserWithRole('Shareholder');
        $building = $this->createBuilding();
        $amenity = Amenity::create($this->amenityAttributes());

        $this->actingAs($user)
            ->post(route('buildings.amenities.store', $building), [
                'amenity_ids' => [$amenity->id],
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->put(route('buildings.amenities.update', [$building, $amenity]), [
                'quantity' => 1,
                'notes' => 'Unauthorized update',
            ])
            ->assertForbidden();

        $this->actingAs($user)
            ->delete(route('buildings.amenities.destroy', [$building, $amenity]))
            ->assertForbidden();

        $this->assertDatabaseMissing('building_amenity', [
            'building_id' => $building->id,
            'amenity_id' => $amenity->id,
        ]);
    }

    private function createUserWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createBuilding(): Building
    {
        $site = Sites::create([
            'project_code' => 'SS-AMENITY-01',
            'project_name' => 'Amenity Test Site',
            'project_status' => 1,
            'address' => 'Test address',
            'city' => 'Dhaka',
            'district' => 'Dhaka',
            'country' => 'Bangladesh',
            'land_area' => 10,
            'land_area_unit' => 1,
        ]);

        return Building::create([
            'site_id' => $site->id,
            'name' => 'Amenity Test Building',
            'code' => 'BLD-AMENITY-01',
            'building_type' => 'Residential',
            'structure_type' => 'RCC Frame',
            'total_floors' => 4,
            'total_flats' => 8,
            'basement_count' => 0,
            'elevator_count' => 1,
            'parking_capacity' => 8,
            'status' => 'active',
        ]);
    }

    private function amenityAttributes(
        string $name = 'Emergency Generator',
        string $category = 'Utilities',
        string $status = 'active'
    ): array {
        return [
            'name' => $name,
            'category' => $category,
            'description' => 'Amenity for testing.',
            'icon' => 'ti-bolt',
            'status' => $status,
        ];
    }
}
