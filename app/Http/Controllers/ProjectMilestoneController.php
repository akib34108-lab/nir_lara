<?php

namespace App\Http\Controllers;

use App\Models\ProjectMilestone;
use App\Models\Projects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProjectProgress;

class ProjectMilestoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($projectId)
    {
        $project = Projects::findOrFail($projectId);
        $milestones = ProjectMilestone::select('milestone_name')
        ->distinct()
        ->orderBy('milestone_name')
        ->get();
        return view('milestones.create', compact('project', 'milestones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $projectId)
    {
        $project = Projects::findOrFail($projectId);
        $validated = $request->validate([
            'milestones' => ['required', 'array', 'min:1'],
            'milestones.*.milestone_name' => ['required','string','max:255'],
            'milestones.*.percentage' => ['required','numeric','gt:0','lte:100'],
        ]);

        $totalPercentage = collect($validated['milestones'])->sum('percentage');
        if (round($totalPercentage, 2) != 100) {
            return back()->withInput()->withErrors(['milestones' => 'Total milestone percentage must be 100%. Current total: ' . $totalPercentage . '%']);
        }

        DB::transaction(function () use ($validated, $project) {
            foreach ($validated['milestones'] as $milestone) {
                $project->milestones()->create([
                    'milestone_name' => $milestone['milestone_name'],
                    'percentage' => $milestone['percentage'],
                    'planned_start_date' => $milestone['planned_start_date'] ?? null,
                    'planned_end_date' => $milestone['planned_end_date'] ?? null,
                    'remarks' => $milestone['remarks'] ?? null,
                    'created_by' => auth()->id(),
                ]);
            }
        });
        return redirect()
            ->route('projects.show', $project->id)
            ->with('success', 'Project milestones created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectMilestone $projectMilestone)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($projectId, $milestoneId)
    {
        $project = Projects::findOrFail($projectId);
        $milestone = ProjectMilestone::where('project_id', $project->id)->findOrFail($milestoneId);
        return view('project-milestones.edit', compact('project','milestone'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $projectId, $milestoneId)
    {
        $project = Projects::findOrFail($projectId);
        $milestone = ProjectMilestone::where('project_id', $project->id)->findOrFail($milestoneId);

        if ($request->has('status') && !$request->has('milestone_name')) {
            $validated = $request->validate([
                'status' => ['required', 'in:pending,completed'],
            ]);

            DB::transaction(function () use ($validated, $milestone, $project) {
                $milestone->update([
                    'status' => $validated['status'],
                    'completed_date' => $validated['status'] === 'completed' ? now() : null,
                ]);

                $progress = $this->recalculateProgress($project);

                ProjectProgress::create([
                    'project_id' => $project->id,
                    'progress_date' => now()->toDateString(),
                    'progress_percentage' => $progress,
                    'remarks' => $validated['status'] === 'completed'
                        ? 'Milestone completed: ' . $milestone->milestone_name
                        : 'Milestone reopened: ' . $milestone->milestone_name,
                    'created_by' => auth()->id(),
                ]);
            });

            return redirect()
                ->route('projects.index')
                ->with('success', 'Milestone marked as ' . $validated['status'] . '.');
        }

        $validated = $request->validate([
            'milestone_name' => ['required', 'string', 'max:255'],
            'percentage' => ['required', 'numeric', 'gt:0', 'lte:100']
        ]);

        $otherPercentage = $project->milestones()
            ->where('id', '!=', $milestone->id)
            ->sum('percentage');

        $newTotal = $otherPercentage + $validated['percentage'];

        if (round($newTotal, 2) != 100) {
            return back()
                ->withInput()
                ->withErrors([
                    'percentage' => 'Total milestone percentage must be 100%. Current total: ' . $newTotal . '%',
                ]);
        }

        DB::transaction(function () use ($validated, $milestone, $project) {
            $previousProgress = (int) $project->progress;
            $milestone->update($validated);
            $progress = $this->recalculateProgress($project);

            if ($previousProgress !== $progress) {
                ProjectProgress::create([
                    'project_id' => $project->id,
                    'progress_date' => now()->toDateString(),
                    'progress_percentage' => $progress,
                    'remarks' => 'Progress recalculated after milestone update: ' . $milestone->milestone_name,
                    'created_by' => auth()->id(),
                ]);
            }
        });

        return redirect()
            ->route('projects.show', $project->id)
            ->with('success', 'Milestone updated successfully.');
    }

    private function recalculateProgress($project)
    {
        $progress = min(
            $project->milestones()->where('status', 'completed')->sum('percentage'),
            100
        );

        $project->update(['progress' => $progress]);

        return $progress;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($projectId, $milestoneId)
    {
        $project = Projects::findOrFail($projectId);
        $milestone = ProjectMilestone::where('project_id', $project->id)
            ->findOrFail($milestoneId);
        $milestone->delete();
        return redirect()
            ->route('projects.show', $project->id)
            ->with('success', 'Milestone deleted successfully.');
    }
}
