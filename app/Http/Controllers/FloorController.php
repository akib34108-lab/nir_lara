<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFloorRequest;
use App\Http\Requests\UpdateFloorRequest;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Sites;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use JsonException;

class FloorController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'building_id' => ['nullable', 'integer', 'exists:buildings,id'],
            'site_id' => ['nullable', 'integer', 'exists:sites,id'],
            'floor_type' => ['nullable', Rule::in(array_keys(Floor::FLOOR_TYPES))],
            'status' => ['nullable', Rule::in(array_keys(Floor::STATUS_LABELS))],
        ]);

        $floors = Floor::query()
            ->with([
                'building:id,site_id,name,code',
                'building.site:id,project_name,project_code',
            ])
            ->when($request->filled('search'), function ($query) use ($filters) {
                $query->where('name', 'like', '%'.$filters['search'].'%');
            })
            ->when(isset($filters['building_id']), fn ($query) => $query->where('building_id', $filters['building_id']))
            ->when(isset($filters['site_id']), function ($query) use ($filters) {
                $query->whereHas('building', fn ($buildingQuery) => $buildingQuery->where('site_id', $filters['site_id']));
            })
            ->when(isset($filters['floor_type']), fn ($query) => $query->where('floor_type', $filters['floor_type']))
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('floor_number')
            ->orderBy('id')
            ->paginate(10);

        $buildings = Building::query()
            ->with('site:id,project_name,project_code')
            ->select('id', 'site_id', 'name', 'code')
            ->orderBy('name')
            ->get();

        $sites = Sites::query()
            ->select('id', 'project_name', 'project_code')
            ->orderBy('project_name')
            ->get();

        return view('floors.index', [
            'floors' => $floors,
            'buildings' => $buildings,
            'sites' => $sites,
            'floorTypes' => Floor::FLOOR_TYPES,
            'statuses' => Floor::STATUS_LABELS,
        ]);
    }

    public function create(Building $building): View
    {
        $building->load('site');

        return view('floors.create', [
            'building' => $building,
            'floor' => new Floor([
                'floor_area_unit' => 'sqft',
                'status' => 'planned',
            ]),
            'floorTypes' => Floor::FLOOR_TYPES,
            'floorAreaUnits' => Floor::FLOOR_AREA_UNITS,
            'statuses' => Floor::STATUS_LABELS,
        ]);
    }

    public function store(StoreFloorRequest $request, Building $building): RedirectResponse
    {
        $floor = $building->floors()->create($this->validatedAttributes($request));

        return redirect()
            ->route('floors.show', $floor)
            ->with('success', 'Floor created successfully.');
    }

    public function show(Floor $floor): View
    {
        $floor->load(['building.site', 'floorPlans.floor', 'floorPlans.building.site']);

        return view('floors.show', compact('floor'));
    }

    public function edit(Floor $floor): View
    {
        $floor->load('building.site');

        return view('floors.edit', [
            'floor' => $floor,
            'building' => $floor->building,
            'floorTypes' => Floor::FLOOR_TYPES,
            'floorAreaUnits' => Floor::FLOOR_AREA_UNITS,
            'statuses' => Floor::STATUS_LABELS,
        ]);
    }

    public function update(UpdateFloorRequest $request, Floor $floor): RedirectResponse
    {
        $floor->update($this->validatedAttributes($request));

        return redirect()
            ->route('floors.show', $floor)
            ->with('success', 'Floor configuration updated successfully.');
    }

    public function destroy(Floor $floor): RedirectResponse
    {
        if ($floor->floorPlans()->withTrashed()->exists()) {
            return redirect()
                ->route('floors.show', $floor)
                ->with('error', 'This floor cannot be deleted because it has floor plans.');
        }

        $building = $floor->building;
        $floor->delete();

        return redirect()
            ->route('buildings.show', $building)
            ->with('success', 'Floor deleted successfully.');
    }

    /**
     * @throws JsonException
     */
    private function validatedAttributes(FormRequest $request): array
    {
        $attributes = $request->validated();

        if (array_key_exists('configuration', $attributes) && $attributes['configuration'] !== null) {
            $attributes['configuration'] = json_decode($attributes['configuration'], false, 512, JSON_THROW_ON_ERROR);
        }

        return $attributes;
    }
}
