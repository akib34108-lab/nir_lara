<?php

namespace Tests\Feature;

use App\Models\Building;
use App\Models\Floor;
use App\Models\FloorPlan;
use App\Models\Sites;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FloorPlanManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test-dashboard', fn () => response()->noContent())->name('dashboard');
    }

    public function test_upload_uses_private_randomized_storage_and_protected_preview_download(): void
    {
        Storage::fake('local');
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $floor = $this->createFloor($building);

        $this->actingAs($user)
            ->get(route('floor-plans.create', ['building_id' => $building->id, 'floor_id' => $floor->id]))
            ->assertOk()
            ->assertSee('Upload Floor Plan');

        $response = $this->actingAs($user)->post(route('floor-plans.store'), [
            'title' => 'Ground Floor Layout',
            'description' => 'Architectural drawing.',
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'file' => UploadedFile::fake()->createWithContent('Ground Floor.pdf', "%PDF-1.4\nFloor plan"),
        ]);

        $response->assertRedirect(route('floors.show', $floor));
        $plan = FloorPlan::query()->where('title', 'Ground Floor Layout')->firstOrFail();

        $this->assertStringStartsWith('property/floor-plans/'.$building->id.'/', $plan->file_path);
        $this->assertNotSame('Ground Floor.pdf', basename($plan->file_path));
        $this->assertSame('Ground Floor.pdf', $plan->original_name);
        Storage::disk('local')->assertExists($plan->file_path);

        $this->actingAs($user)
            ->get(route('floor-plans.preview', $plan))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'inline');

        $download = $this->actingAs($user)->get(route('floor-plans.download', $plan));
        $download->assertOk()->assertHeader('Content-Disposition');
        $this->assertStringContainsString('Ground Floor.pdf', $download->headers->get('Content-Disposition'));

        $this->actingAs($user)
            ->get(route('floor-plans.index', [
                'search' => 'Ground Floor',
                'site_id' => $building->site_id,
                'building_id' => $building->id,
                'floor_id' => $floor->id,
            ]))
            ->assertOk()
            ->assertSee('Ground Floor Layout');

        $this->actingAs($user)
            ->get(route('buildings.show', $building))
            ->assertOk()
            ->assertSee('Ground Floor Layout');

        $this->actingAs($user)
            ->get(route('floors.show', $floor))
            ->assertOk()
            ->assertSee('Ground Floor Layout');
    }

    public function test_upload_rejects_mismatched_floors_disallowed_mime_types_and_files_over_ten_mb(): void
    {
        Storage::fake('local');
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding('BLD-FP-01');
        $otherBuilding = $this->createBuilding('BLD-FP-02');
        $floor = $this->createFloor($building);
        $otherFloor = $this->createFloor($otherBuilding);

        $this->actingAs($user)
            ->post(route('floor-plans.store'), [
                'title' => 'Wrong Building Floor',
                'building_id' => $building->id,
                'floor_id' => $otherFloor->id,
                'file' => UploadedFile::fake()->createWithContent('plan.pdf', "%PDF-1.4\nDrawing"),
            ])
            ->assertSessionHasErrors('floor_id');

        $this->actingAs($user)
            ->post(route('floor-plans.store'), [
                'title' => 'Text File',
                'building_id' => $building->id,
                'floor_id' => $floor->id,
                'file' => UploadedFile::fake()->create('readme.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('file');

        $this->actingAs($user)
            ->post(route('floor-plans.store'), [
                'title' => 'Oversized File',
                'building_id' => $building->id,
                'floor_id' => $floor->id,
                'file' => UploadedFile::fake()->create('large.pdf', 10241, 'application/pdf'),
            ])
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('floor_plans', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());

        $existingPlan = $this->createPlan($building, $floor, 'Existing Plan');
        $this->actingAs($user)
            ->put(route('floor-plans.update', $existingPlan), [
                'title' => 'Attempted Reassignment',
                'building_id' => $building->id,
                'floor_id' => $otherFloor->id,
            ])
            ->assertSessionHasErrors('floor_id');

        $this->assertDatabaseHas('floor_plans', [
            'id' => $existingPlan->id,
            'floor_id' => $floor->id,
        ]);
    }

    public function test_replacing_file_updates_metadata_and_removes_the_previous_file(): void
    {
        Storage::fake('local');
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $floor = $this->createFloor($building);

        $this->actingAs($user)->post(route('floor-plans.store'), [
            'title' => 'Original Drawing',
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'file' => UploadedFile::fake()->createWithContent('original.pdf', "%PDF-1.4\nOriginal"),
        ])->assertRedirect(route('floors.show', $floor));

        $plan = FloorPlan::query()->firstOrFail();
        $oldPath = $plan->file_path;

        $this->actingAs($user)
            ->get(route('floor-plans.edit', $plan))
            ->assertOk()
            ->assertSee('Edit Floor Plan')
            ->assertSee('Replace File (optional)');

        $this->actingAs($user)
            ->put(route('floor-plans.update', $plan), [
                'title' => 'Revised Drawing',
                'description' => 'Updated plan.',
                'building_id' => $building->id,
                'floor_id' => $floor->id,
                'file' => UploadedFile::fake()->create('revised.png', 10, 'image/png'),
            ])
            ->assertRedirect(route('floors.show', $floor));

        $plan->refresh();
        $this->assertSame('Revised Drawing', $plan->title);
        $this->assertSame('revised.png', $plan->original_name);
        $this->assertSame('image/png', $plan->mime_type);
        $this->assertNotSame($oldPath, $plan->file_path);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($plan->file_path);

        $this->actingAs($user)
            ->get(route('floor-plans.preview', $plan))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_changing_building_moves_the_file_to_its_new_private_folder(): void
    {
        Storage::fake('local');
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding('BLD-FP-01');
        $newBuilding = $this->createBuilding('BLD-FP-02');
        $floor = $this->createFloor($building);
        $newFloor = $this->createFloor($newBuilding);
        $plan = $this->createPlan($building, $floor, 'Moved Plan');
        $oldPath = $plan->file_path;
        Storage::disk('local')->put($oldPath, "%PDF-1.4\nMoved");

        $this->actingAs($user)
            ->put(route('floor-plans.update', $plan), [
                'title' => 'Moved Plan',
                'building_id' => $newBuilding->id,
                'floor_id' => $newFloor->id,
            ])
            ->assertRedirect(route('floors.show', $newFloor));

        $plan->refresh();
        $this->assertSame($newBuilding->id, $plan->building_id);
        $this->assertSame($newFloor->id, $plan->floor_id);
        $this->assertStringStartsWith('property/floor-plans/'.$newBuilding->id.'/', $plan->file_path);
        Storage::disk('local')->assertMissing($oldPath);
        Storage::disk('local')->assertExists($plan->file_path);
    }

    public function test_delete_removes_existing_file_and_handles_already_missing_file(): void
    {
        Storage::fake('local');
        $user = $this->createUserWithRole('Super Admin');
        $building = $this->createBuilding();
        $floor = $this->createFloor($building);

        $this->actingAs($user)->post(route('floor-plans.store'), [
            'title' => 'Delete This Plan',
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'file' => UploadedFile::fake()->createWithContent('delete.pdf', "%PDF-1.4\nDelete"),
        ])->assertRedirect(route('floors.show', $floor));

        $plan = FloorPlan::query()->firstOrFail();
        $path = $plan->file_path;
        $this->actingAs($user)
            ->delete(route('floor-plans.destroy', $plan))
            ->assertRedirect(route('floor-plans.index'));

        $this->assertSoftDeleted('floor_plans', ['id' => $plan->id]);
        Storage::disk('local')->assertMissing($path);

        $missingFilePlan = $this->createPlan($building, $floor, 'Missing File Plan');
        $this->actingAs($user)
            ->delete(route('floor-plans.destroy', $missingFilePlan))
            ->assertRedirect(route('floor-plans.index'))
            ->assertSessionHas('success', 'Floor plan deleted successfully.');

        $this->assertSoftDeleted('floor_plans', ['id' => $missingFilePlan->id]);
    }

    public function test_shareholder_can_view_but_not_upload_edit_or_delete_floor_plans(): void
    {
        Storage::fake('local');
        $user = $this->createUserWithRole('Shareholder');
        $building = $this->createBuilding();
        $floor = $this->createFloor($building);
        $plan = $this->createPlan($building, $floor, 'Shareholder Plan');
        Storage::disk('local')->put($plan->file_path, "%PDF-1.4\nPlan");

        $this->actingAs($user)->get(route('floor-plans.index'))->assertOk();
        $this->actingAs($user)->get(route('floor-plans.preview', $plan))->assertOk();
        $this->actingAs($user)->get(route('floor-plans.download', $plan))->assertOk();
        $this->actingAs($user)->get(route('floor-plans.create'))->assertForbidden();
        $this->actingAs($user)->get(route('floor-plans.edit', $plan))->assertForbidden();
        $this->actingAs($user)->put(route('floor-plans.update', $plan), [])->assertForbidden();
        $this->actingAs($user)->delete(route('floor-plans.destroy', $plan))->assertForbidden();
    }

    private function createUserWithRole(string $role): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function createBuilding(string $code = 'BLD-FP-01'): Building
    {
        $site = Sites::create([
            'project_code' => 'SS-'.$code,
            'project_name' => 'Floor Plan Test Site '.$code,
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
            'name' => 'Floor Plan Test Building '.$code,
            'code' => $code,
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

    private function createFloor(Building $building): Floor
    {
        return $building->floors()->create([
            'name' => 'Ground Floor',
            'floor_number' => 0,
            'floor_type' => 'ground',
            'floor_area_unit' => 'sqft',
            'status' => 'active',
        ]);
    }

    private function createPlan(Building $building, Floor $floor, string $title): FloorPlan
    {
        return FloorPlan::create([
            'building_id' => $building->id,
            'floor_id' => $floor->id,
            'title' => $title,
            'file_path' => 'property/floor-plans/'.$building->id.'/'.uniqid('test-', true).'.pdf',
            'original_name' => $title.'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => 1024,
        ]);
    }
}
