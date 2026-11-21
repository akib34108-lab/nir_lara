<?php

namespace App\Http\Controllers;

use App\Models\Projects;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Projects::latest()->paginate(10);
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

        Projects::create($request->all());

        return redirect()->route('projects.index')
                         ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Projects $projects)
    {
        return view('projects.show', compact('projects'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Projects $projects)
    {
        return view('projects.edit', compact('projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Projects $projects)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'project_manager_id' => 'required|exists:users,id',
            'project_code' => 'required|string|max:255|unique:projects,project_code,' . $projects->id,
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

        $projects->update($request->all());

        return redirect()->route('projects.index')
                         ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Projects $projects)
    {
        $projects->delete();
        return redirect()->route('projects.index')
                         ->with('success', 'Project deleted successfully.');
    }
}
