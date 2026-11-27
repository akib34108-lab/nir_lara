<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAmenityRequest;
use App\Http\Requests\UpdateAmenityRequest;
use App\Models\Amenity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AmenityController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', Rule::in(array_keys(Amenity::CATEGORIES))],
            'status' => ['nullable', Rule::in(array_keys(Amenity::STATUS_LABELS))],
        ]);

        $amenities = Amenity::query()
            ->withCount('buildings')
            ->when(isset($filters['search']), fn ($query) => $query->where('name', 'like', '%'.$filters['search'].'%'))
            ->when(isset($filters['category']), fn ($query) => $query->where('category', $filters['category']))
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('amenities.index', [
            'amenities' => $amenities,
            'categories' => Amenity::CATEGORIES,
            'statuses' => Amenity::STATUS_LABELS,
        ]);
    }

    public function create(): View
    {
        return view('amenities.create', [
            'amenity' => new Amenity(['status' => 'active']),
            'categories' => Amenity::CATEGORIES,
            'statuses' => Amenity::STATUS_LABELS,
        ]);
    }

    public function store(StoreAmenityRequest $request): RedirectResponse
    {
        Amenity::create($request->validated());

        return redirect()
            ->route('amenities.index')
            ->with('success', 'Amenity created successfully.');
    }

    public function show(Amenity $amenity): View
    {
        $amenity->load(['buildings.site']);

        return view('amenities.show', compact('amenity'));
    }

    public function edit(Amenity $amenity): View
    {
        return view('amenities.edit', [
            'amenity' => $amenity,
            'categories' => Amenity::CATEGORIES,
            'statuses' => Amenity::STATUS_LABELS,
        ]);
    }

    public function update(UpdateAmenityRequest $request, Amenity $amenity): RedirectResponse
    {
        $amenity->update($request->validated());

        return redirect()
            ->route('amenities.show', $amenity)
            ->with('success', 'Amenity updated successfully.');
    }

    public function destroy(Amenity $amenity): RedirectResponse
    {
        if (DB::table('building_amenity')->where('amenity_id', $amenity->getKey())->exists()) {
            return redirect()
                ->route('amenities.index')
                ->with('error', 'This amenity cannot be deleted because it is assigned to a building. Set it inactive instead.');
        }

        $amenity->delete();

        return redirect()
            ->route('amenities.index')
            ->with('success', 'Amenity deleted successfully.');
    }
}
