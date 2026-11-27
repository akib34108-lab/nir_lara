<?php

namespace App\Http\Controllers;

use App\Models\Sites;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SitesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sites = Sites::query()
        ->when($request->filled('search'), function ($query) use ($request) {
            $query->where(function ($searchQuery) use ($request) {
                $searchQuery->where('project_name', 'like', '%' . $request->search . '%')
                    ->orWhere('project_code', 'like', '%' . $request->search . '%');
            });
        })
        ->latest()
        ->paginate(10);
        return view('sites.index', compact('sites'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $nextId = Sites::max('id') + 1;
        $nextSiteCode = 'SS-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        return view('sites.create', compact('nextSiteCode'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Basic Information
            'project_code' => 'required|string|max:255|unique:sites,project_code',
            'project_name' => 'required|string|max:255',
            'project_status' => 'required|integer|in:1,2,3,4,5',
            'description' => 'nullable|string',

            // Location Information
            'address' => 'required|string|max:255',
            'area' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'division' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:50',
            'google_maps_location' => 'nullable|url|max:255',

            // Land Information
            'land_area' => 'required|numeric|min:0',
            'land_area_unit' => 'required|integer|in:1,2,3,4,5,6',
            'plot_number' => 'nullable|string|max:255',
            'block' => 'nullable|string|max:255',
            'road_number' => 'nullable|string|max:255',
            'sector' => 'nullable|string|max:255',
            'land_description' => 'nullable|string',

            // Project Timeline
            'planned_start_date' => 'nullable|date',
            'planned_completion_date' => 'nullable|date|after_or_equal:planned_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date|after_or_equal:actual_start_date',

            // Share Information
            'total_shares' => 'nullable|integer|min:0',
            'per_share_value' => 'nullable|numeric|min:0',

            // Budget Information
            'estimated_project_budget' => 'nullable|numeric|min:0',
        ]);

        if (isset($validated['total_shares'], $validated['per_share_value'])) {
            $validated['estimated_project_budget'] = round(
                (float) $validated['total_shares'] * (float) $validated['per_share_value'],
                2
            );
        }

        Sites::create($validated);

        return redirect()
            ->route('sites.index')
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sites $site)
    {
        return view('sites.show', compact('site'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sites $site)
    {
        return view('sites.edit', compact('site'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sites $site)
    {
        $validated = $request->validate([
            // Basic Information
            'project_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('sites', 'project_code')->ignore($site->id),
            ],
            'project_name' => 'required|string|max:255',
            'project_status' => 'required|integer|in:1,2,3,4,5',
            'description' => 'nullable|string',

            // Location Information
            'address' => 'required|string|max:255',
            'area' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'division' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:50',
            'google_maps_location' => 'nullable|url|max:255',

            // Land Information
            'land_area' => 'required|numeric|min:0',
            'land_area_unit' => 'required|integer|in:1,2,3,4,5,6',
            'plot_number' => 'nullable|string|max:255',
            'block' => 'nullable|string|max:255',
            'road_number' => 'nullable|string|max:255',
            'sector' => 'nullable|string|max:255',
            'land_description' => 'nullable|string',

            // Project Timeline
            'planned_start_date' => 'nullable|date',
            'planned_completion_date' => 'nullable|date|after_or_equal:planned_start_date',
            'actual_start_date' => 'nullable|date',
            'actual_completion_date' => 'nullable|date|after_or_equal:actual_start_date',

            // Share Information
            'total_shares' => 'nullable|integer|min:0',
            'per_share_value' => 'nullable|numeric|min:0',

            // Budget Information
            'estimated_project_budget' => 'nullable|numeric|min:0',
        ]);

        if (isset($validated['total_shares'], $validated['per_share_value'])) {
            $validated['estimated_project_budget'] = round(
                (float) $validated['total_shares'] * (float) $validated['per_share_value'],
                2
            );
        }

        $site->update($validated);

        return redirect()
            ->route('sites.index')
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sites $site)
    {
        $site->delete();

        return redirect()
            ->route('sites.index')
            ->with('success', 'Project deleted successfully.');
    }
}
