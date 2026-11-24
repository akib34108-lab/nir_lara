<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('projects')
            ->select(['id', 'progress'])
            ->chunkById(100, function ($projects) {
                foreach ($projects as $project) {
                    $latestProgress = DB::table('project_progress')
                        ->where('project_id', $project->id)
                        ->orderByDesc('progress_date')
                        ->orderByDesc('id')
                        ->value('progress_percentage');

                    if ($latestProgress !== null && (int) $latestProgress === (int) $project->progress) {
                        continue;
                    }

                    $now = now();
                    DB::table('project_progress')->insert([
                        'project_id' => $project->id,
                        'progress_date' => $now->toDateString(),
                        'progress_percentage' => $project->progress,
                        'remarks' => 'Current project progress recorded during history synchronization.',
                        'created_by' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Keep synchronized progress snapshots as valid history if this migration is rolled back.
    }
};
