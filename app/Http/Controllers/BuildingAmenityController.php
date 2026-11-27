<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuildingAmenitiesRequest;
use App\Http\Requests\UpdateBuildingAmenityRequest;
use App\Models\Amenity;
use App\Models\Building;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class BuildingAmenityController extends Controller
{
    public function store(StoreBuildingAmenitiesRequest $request, Building $building): RedirectResponse
    {
        $attributes = $request->validated();
        $amenityIds = $attributes['amenity_ids'];

        DB::transaction(function () use ($building, $attributes, $amenityIds) {
            $existingPivots = DB::table('building_amenity')
                ->where('building_id', $building->getKey())
                ->whereIn('amenity_id', $amenityIds)
                ->get()
                ->keyBy('amenity_id');

            $syncData = [];
            foreach ($amenityIds as $amenityId) {
                $existing = $existingPivots->get($amenityId);
                $syncData[$amenityId] = [
                    'quantity' => $attributes['quantity'][$amenityId] ?? $existing?->quantity,
                    'notes' => $attributes['notes'][$amenityId] ?? $existing?->notes,
                ];
            }

            $building->amenities()->syncWithoutDetaching($syncData);
        });

        return redirect()
            ->route('buildings.show', $building)
            ->with('success', 'Amenities assigned to the building successfully.');
    }

    public function update(
        UpdateBuildingAmenityRequest $request,
        Building $building,
        Amenity $amenity
    ): RedirectResponse {
        abort_unless($building->amenities()->whereKey($amenity->getKey())->exists(), 404);

        $building->amenities()->updateExistingPivot($amenity->getKey(), $request->validated());

        return redirect()
            ->route('buildings.show', $building)
            ->with('success', 'Building amenity details updated successfully.');
    }

    public function destroy(Building $building, Amenity $amenity): RedirectResponse
    {
        abort_unless($building->amenities()->whereKey($amenity->getKey())->exists(), 404);

        $building->amenities()->detach($amenity->getKey());

        return redirect()
            ->route('buildings.show', $building)
            ->with('success', 'Amenity removed from the building successfully.');
    }
}
