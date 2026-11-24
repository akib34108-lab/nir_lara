<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ProjectProgress;
use App\Models\Projects;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Projects::with('milestones')->latest()->paginate(10);
        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $nextId = (Projects::withTrashed()->max('id') ?? 0) + 1;
        $nextProjectCode = 'PR-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        $clients = Client::latest()->get();
        $projectManagers = User::role('Project Manager')->get();
        return view('projects.create', compact('nextProjectCode', 'clients', 'projectManagers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_manager_id' => 'required|exists:users,id',
            'project_code' => 'required|string|max:255|unique:projects,project_code',
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'site_address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'start_date' => 'required|date',
            'expected_end_date' => 'required|date|after_or_equal:start_date',
            'actual_end_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_budget' => 'required|numeric|min:0',
            'status' => 'required|integer|in:1,2,3,4,5,6',
            'progress' => 'required|integer|min:0|max:100',

        ]);

        $project = Projects::create($request->all());

        return redirect()->route('projects.milestones.create', $project->id)
                         ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Projects $project)
    {
        $client = Client::find($project->client_id);
        $projectManager = User::find($project->project_manager_id);
        $progressHistory = $project->progressHistory()
            ->with('creator')
            ->latest('progress_date')
            ->latest('id')
            ->get();
        return view('projects.show', compact('project', 'client', 'projectManager', 'progressHistory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Projects $project)
    {
        $clients = Client::latest()->get();
        $projectManagers = User::role('Project Manager')->get();
        return view('projects.edit', compact('project', 'clients', 'projectManagers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Projects $project)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_manager_id' => 'required|exists:users,id',
            'project_code' => 'required|string|max:255|unique:projects,project_code,' . $project->id,
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'site_address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'start_date' => 'required|date',
            'expected_end_date' => 'required|date|after_or_equal:start_date',
            'actual_end_date' => 'nullable|date|after_or_equal:start_date',
            'estimated_budget' => 'required|numeric|min:0',
            'status' => 'required|integer|in:1,2,3,4,5,6',
            'progress' => 'required|integer|min:0|max:100',
        ]);

        DB::transaction(function () use ($validated, $project) {
            $previousProgress = (int) $project->progress;
            $project->update($validated);

            if ($previousProgress !== (int) $validated['progress']) {
                ProjectProgress::create([
                    'project_id' => $project->id,
                    'progress_date' => now()->toDateString(),
                    'progress_percentage' => $validated['progress'],
                    'remarks' => 'Progress updated from project details.',
                    'created_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('projects.index')
                         ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Projects $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
