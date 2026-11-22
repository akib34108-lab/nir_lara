<?php
namespace App\Http\Controllers;

use App\Models\Projects;
use App\Models\ProjectProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectProgressController extends Controller
{
    public function store(Request $request, Projects $project)
    {
        $validated = $request->validate([
            'progress_date' => 'required|date',
            'progress_percentage' => 'required|integer|min:0|max:100',
            'remarks' => 'nullable|string',
        ]);
        DB::transaction(function () use ($validated, $project) {
            ProjectProgress::create([
                'project_id' => $project->id,
                'progress_date' => $validated['progress_date'],
                'progress_percentage' => $validated['progress_percentage'],
                'remarks' => $validated['remarks'] ?? null,
                'created_by' => auth()->id(),
            ]);
            $project->update([
                'progress' => $validated['progress_percentage'],
            ]);
        });
        return redirect()
            ->back()
            ->with('success', 'Project progress updated successfully.');
    }
}