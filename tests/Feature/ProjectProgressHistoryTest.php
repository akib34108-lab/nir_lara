<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ProjectMilestone;
use App\Models\ProjectProgress;
use App\Models\Projects;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectProgressHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_a_milestone_saves_the_updated_progress_to_history(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $project = $this->createProject($user, 0);
        $milestone = ProjectMilestone::create([
            'project_id' => $project->id,
            'milestone_name' => 'Foundation',
            'percentage' => 70,
        ]);

        $response = $this->actingAs($user)->put(
            route('projects.milestones.update', [$project->id, $milestone->id]),
            ['status' => 'completed']
        );

        $response->assertRedirect(route('projects.index'));
        $this->assertSame(70, (int) $project->fresh()->progress);
        $this->assertDatabaseHas('project_progress', [
            'project_id' => $project->id,
            'progress_percentage' => 70,
            'remarks' => 'Milestone completed: Foundation',
            'created_by' => $user->id,
        ]);
    }

    public function test_project_progress_edits_are_recorded_and_new_same_day_history_is_shown_first(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $user = User::factory()->create();
        $user->assignRole('Super Admin');
        $project = $this->createProject($user, 70);
        ProjectProgress::create([
            'project_id' => $project->id,
            'progress_date' => now()->toDateString(),
            'progress_percentage' => 70,
            'remarks' => 'Earlier update',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)->put(route('projects.update', $project), [
            'client_id' => $project->client_id,
            'project_manager_id' => $user->id,
            'project_code' => $project->project_code,
            'project_name' => $project->project_name,
            'description' => $project->description,
            'site_address' => $project->site_address,
            'city' => $project->city,
            'start_date' => $project->start_date,
            'expected_end_date' => $project->expected_end_date,
            'actual_end_date' => $project->actual_end_date,
            'estimated_budget' => $project->estimated_budget,
            'status' => $project->status,
            'progress' => 0,
        ])->assertRedirect(route('projects.index'));

        $this->assertSame(0, (int) $project->fresh()->progress);

        $response = $this->actingAs($user)->get(route('projects.show', $project));
        $response->assertOk();
        $response->assertSeeInOrder([
            'badge badge-success">0%</span>',
            'badge badge-success">70%</span>',
        ], false);
    }

    public function test_history_sync_records_an_existing_current_percentage_once(): void
    {
        $user = User::factory()->create();
        $project = $this->createProject($user, 0);
        ProjectProgress::create([
            'project_id' => $project->id,
            'progress_date' => now()->toDateString(),
            'progress_percentage' => 70,
        ]);
        $migration = require database_path('migrations/2026_11_23_000000_sync_project_progress_history.php');

        $migration->up();
        $migration->up();

        $this->assertSame(
            0,
            (int) $project->progressHistory()->latest('progress_date')->latest('id')->value('progress_percentage')
        );
        $this->assertDatabaseCount('project_progress', 2);
    }

    private function createProject(User $manager, int $progress): Projects
    {
        $client = Client::create([
            'name' => 'Progress Test Client',
            'phone' => '1234567890',
        ]);

        return Projects::create([
            'client_id' => $client->id,
            'project_manager_id' => $manager->id,
            'project_code' => 'PR-TEST',
            'project_name' => 'Progress Test Project',
            'site_address' => 'Test address',
            'city' => 'Test city',
            'start_date' => '2026-01-01',
            'expected_end_date' => '2027-01-01',
            'estimated_budget' => 100000,
            'status' => 1,
            'progress' => $progress,
        ]);
    }
}
