<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;
use App\Models\Amenity;
use App\Models\Building;
use App\Models\Sites;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BuildingController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'site_id' => ['nullable', 'integer', 'exists:sites,id'],
            'building_type' => ['nullable', Rule::in(Building::BUILDING_TYPES)],
            'status' => ['nullable', Rule::in(array_keys(Building::STATUS_LABELS))],
        ]);

        $buildings = Building::query()
            ->with('site:id,project_name,project_code,address')
            ->when($request->filled('search'), function ($query) use ($filters) {
                $query->where(function ($searchQuery) use ($filters) {
                    $searchQuery->where('name', 'like', '%'.$filters['search'].'%')
                        ->orWhere('code', 'like', '%'.$filters['search'].'%');
                });
            })
            ->when(isset($filters['site_id']), fn ($query) => $query->where('site_id', $filters['site_id']))
            ->when(isset($filters['building_type']), fn ($query) => $query->where('building_type', $filters['building_type']))
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->latest()
            ->paginate(10);

        $sites = Sites::query()
            ->select('id', 'project_name', 'project_code')
            ->orderBy('project_name')
            ->get();

        return view('buildings.index', [
            'buildings' => $buildings,
            'sites' => $sites,
            'buildingTypes' => Building::BUILDING_TYPES,
            'statuses' => Building::STATUS_LABELS,
        ]);
    }

    public function create(): View
    {
        return view('buildings.create', $this->formOptions());
    }

    public function store(StoreBuildingRequest $request): RedirectResponse
    {
        Building::create($request->validated());

        return redirect()
            ->route('buildings.index')
            ->with('success', 'Building created successfully.');
    }

    public function show(Building $building): View
    {
        $building->load(['site', 'floors', 'amenities', 'floorPlans.floor', 'floorPlans.building.site']);
        $assignableAmenities = Amenity::query()
            ->where('status', 'active')
            ->whereDoesntHave('buildings', fn ($query) => $query->whereKey($building->getKey()))
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('buildings.show', compact('building', 'assignableAmenities'));
    }

    public function edit(Building $building): View
    {
        return view('buildings.edit', array_merge(
            $this->formOptions(),
            compact('building')
        ));
    }

    public function update(UpdateBuildingRequest $request, Building $building): RedirectResponse
    {
        $building->update($request->validated());

        return redirect()
            ->route('buildings.index')
            ->with('success', 'Building updated successfully.');
    }

    public function destroy(Building $building): RedirectResponse
    {
        if ($building->floors()->withTrashed()->exists()) {
            return redirect()
                ->route('buildings.index')
                ->with('error', 'This building cannot be deleted because it has floors.');
        }

        $building->delete();

        return redirect()
            ->route('buildings.index')
            ->with('success', 'Building deleted successfully.');
    }

    private function formOptions(): array
    {
        return [
            'sites' => Sites::query()
                ->select('id', 'project_name', 'project_code')
                ->orderBy('project_name')
                ->get(),
            'buildingTypes' => Building::BUILDING_TYPES,
            'structureTypes' => Building::STRUCTURE_TYPES,
            'statuses' => Building::STATUS_LABELS,
        ];
    }
}
