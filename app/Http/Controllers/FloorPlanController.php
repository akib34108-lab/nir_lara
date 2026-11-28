<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFloorPlanRequest;
use App\Http\Requests\UpdateFloorPlanRequest;
use App\Models\Building;
use App\Models\Floor;
use App\Models\FloorCommonArea;
use App\Models\FloorFlat;
use App\Models\FloorFlatRoom;
use App\Models\FloorPlan;
use App\Models\Sites;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class FloorPlanController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'site_id' => ['nullable', 'integer', 'exists:sites,id'],
            'building_id' => ['nullable', 'integer', 'exists:buildings,id'],
            'floor_id' => ['nullable', 'integer', 'exists:floors,id'],
        ]);

        $floorPlans = FloorPlan::query()
            ->with([
                'building:id,site_id,name,code',
                'building.site:id,project_name,project_code',
                'floor:id,building_id,name,floor_number',
            ])
            ->when(isset($filters['search']), fn ($query) => $query->where('title', 'like', '%'.$filters['search'].'%'))
            ->when(isset($filters['site_id']), function ($query) use ($filters) {
                $query->whereHas('building', fn ($buildingQuery) => $buildingQuery->where('site_id', $filters['site_id']));
            })
            ->when(isset($filters['building_id']), fn ($query) => $query->where('building_id', $filters['building_id']))
            ->when(isset($filters['floor_id']), fn ($query) => $query->where('floor_id', $filters['floor_id']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('floor-plans.index', [
            'floorPlans' => $floorPlans,
            'sites' => Sites::query()->select('id', 'project_name', 'project_code')->orderBy('project_name')->get(),
            'buildings' => Building::query()->with('site:id,project_name,project_code')
                ->select('id', 'site_id', 'name', 'code')->orderBy('name')->get(),
            'floors' => Floor::query()->with('building:id,name,code')
                ->select('id', 'building_id', 'name', 'floor_number')->orderBy('floor_number')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $selection = $request->validate([
            'building_id' => [
                'nullable',
                'integer',
                Rule::exists('buildings', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'floor_id' => [
                'nullable',
                'integer',
                Rule::exists('floors', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ]);

        if (isset($selection['floor_id'])) {
            $floor = Floor::query()->findOrFail($selection['floor_id']);
            if (isset($selection['building_id']) && (int) $selection['building_id'] !== $floor->building_id) {
                abort(404);
            }
            $selection['building_id'] = $floor->building_id;
        }

        $floorPlan = new FloorPlan($selection);
        if (isset($selection['floor_id'])) {
            $floorPlan->setRelation('floor', Floor::query()
                ->with(['flats.rooms', 'commonAreas'])
                ->findOrFail($selection['floor_id']));
        }

        return view('floor-plans.create', array_merge(
            $this->formOptions(),
            compact('floorPlan'),
            [
                'selectedBuildingId' => $selection['building_id'] ?? null,
                'selectedFloorId' => $selection['floor_id'] ?? null,
            ]
        ));
    }

    public function store(StoreFloorPlanRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $upload = $request->file('file');
        unset($attributes['file']);
        $flatData = $attributes['flats'] ?? [];
        $commonAreaData = $attributes['common_areas'] ?? [];
        $floorAreaSummary = $attributes['floor_area_summary'] ?? null;
        $parkingSpaces = $attributes['parking_spaces'] ?? null;
        unset($attributes['flats'], $attributes['common_areas'], $attributes['floor_area_summary'], $attributes['parking_spaces']);

        if (! $upload instanceof UploadedFile) {
            throw new RuntimeException('A floor plan file is required.');
        }

        $storedFile = $this->storeFile($upload, (int) $attributes['building_id']);

        try {
            $floorPlan = DB::transaction(function () use ($attributes, $storedFile, $flatData, $commonAreaData, $floorAreaSummary, $parkingSpaces) {
                $plan = FloorPlan::create(array_merge($attributes, $storedFile));
                $this->syncFloorConfiguration((int) $attributes['floor_id'], $flatData, $commonAreaData, $floorAreaSummary, $parkingSpaces);

                return $plan;
            });
        } catch (Throwable $exception) {
            $this->removeStoredFileOrFail($storedFile['file_path']);
            throw $exception;
        }

        return redirect()
            ->route('floors.show', $floorPlan->floor_id)
            ->with('success', 'Floor plan uploaded successfully.');
    }

    public function edit(Request $request, FloorPlan $floorPlan): View
    {
        $selection = $request->validate([
            'building_id' => [
                'nullable',
                'integer',
                Rule::exists('buildings', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'floor_id' => [
                'nullable',
                'integer',
                Rule::exists('floors', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ]);
        $floorId = $selection['floor_id'] ?? $floorPlan->floor_id;
        $floor = Floor::query()->with(['building', 'flats.rooms', 'commonAreas'])->findOrFail($floorId);
        if (isset($selection['building_id']) && (int) $selection['building_id'] !== $floor->building_id) {
            abort(404);
        }

        $floorPlan->setRelation('building', $floor->building);
        $floorPlan->setRelation('floor', $floor);

        return view('floor-plans.edit', array_merge(
            $this->formOptions(),
            compact('floorPlan'),
            [
                'selectedBuildingId' => $floor->building_id,
                'selectedFloorId' => $floor->id,
            ]
        ));
    }

    public function update(UpdateFloorPlanRequest $request, FloorPlan $floorPlan): RedirectResponse
    {
        $attributes = $request->validated();
        $upload = $request->file('file');
        unset($attributes['file']);
        $flatData = $attributes['flats'] ?? [];
        $commonAreaData = $attributes['common_areas'] ?? [];
        $floorAreaSummary = $attributes['floor_area_summary'] ?? null;
        $parkingSpaces = $attributes['parking_spaces'] ?? null;
        unset($attributes['flats'], $attributes['common_areas'], $attributes['floor_area_summary'], $attributes['parking_spaces']);

        if ($upload !== null && ! $upload instanceof UploadedFile) {
            throw new RuntimeException('The replacement floor plan file is invalid.');
        }

        if (! $upload) {
            $oldPath = $floorPlan->file_path;
            $relocatedPath = null;
            $disk = Storage::disk(FloorPlan::STORAGE_DISK);

            if ((int) $attributes['building_id'] !== (int) $floorPlan->building_id) {
                if (! $this->isManagedPath($oldPath)) {
                    throw new RuntimeException('The existing floor plan file path is invalid.');
                }

                if ($disk->exists($oldPath)) {
                    $relocatedPath = $this->moveFileToBuilding($oldPath, (int) $attributes['building_id']);
                    $attributes['file_path'] = $relocatedPath;
                }
            }

            try {
                DB::transaction(function () use ($floorPlan, $attributes, $flatData, $commonAreaData, $floorAreaSummary, $parkingSpaces) {
                    $floorPlan->update($attributes);
                    $this->syncFloorConfiguration((int) $attributes['floor_id'], $flatData, $commonAreaData, $floorAreaSummary, $parkingSpaces);
                });
            } catch (Throwable $exception) {
                if ($relocatedPath !== null && ! $disk->move($relocatedPath, $oldPath)) {
                    throw new RuntimeException('The floor plan file could not be restored after the metadata update failed.', 0, $exception);
                }
                throw $exception;
            }

            return redirect()
                ->route('floors.show', $attributes['floor_id'])
                ->with('success', 'Floor plan details updated successfully.');
        }

        $oldAttributes = $floorPlan->only([
            'building_id',
            'floor_id',
            'title',
            'description',
            'file_path',
            'original_name',
            'mime_type',
            'file_size',
        ]);
        $storedFile = $this->storeFile($upload, (int) $attributes['building_id']);

        try {
            DB::transaction(function () use ($floorPlan, $attributes, $storedFile, $flatData, $commonAreaData, $floorAreaSummary, $parkingSpaces) {
                $floorPlan->update(array_merge($attributes, $storedFile));
                $this->syncFloorConfiguration((int) $attributes['floor_id'], $flatData, $commonAreaData, $floorAreaSummary, $parkingSpaces);
            });
        } catch (Throwable $exception) {
            $this->removeStoredFileOrFail($storedFile['file_path']);
            throw $exception;
        }

        if (! $this->removeStoredFile($oldAttributes['file_path'])) {
            DB::transaction(fn () => $floorPlan->update($oldAttributes));
            $this->removeStoredFileOrFail($storedFile['file_path']);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'The previous file could not be removed. The original floor plan was restored.');
        }

        return redirect()
            ->route('floors.show', $attributes['floor_id'])
            ->with('success', 'Floor plan updated successfully.');
    }

    public function destroy(FloorPlan $floorPlan): RedirectResponse
    {
        $filePath = $floorPlan->file_path;
        DB::transaction(fn () => $floorPlan->delete());

        if (! $this->removeStoredFile($filePath)) {
            DB::transaction(fn () => $floorPlan->restore());

            return redirect()
                ->route('floor-plans.index')
                ->with('error', 'The stored file could not be removed. The floor plan was restored.');
        }

        return redirect()
            ->route('floor-plans.index')
            ->with('success', 'Floor plan deleted successfully.');
    }

    public function preview(FloorPlan $floorPlan)
    {
        $path = $this->existingFilePath($floorPlan);

        return response()->file($path, [
            'Content-Type' => $floorPlan->mime_type,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function download(FloorPlan $floorPlan)
    {
        $path = $this->existingFilePath($floorPlan);

        return response()->download($path, $this->sanitizeName($floorPlan->original_name), [
            'Content-Type' => $floorPlan->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    private function formOptions(): array
    {
        $floors = Floor::query()
            ->with('building:id,name,code')
            ->withCount('flats')
            ->select('id', 'building_id', 'name', 'floor_number', 'floor_type', 'floor_area', 'floor_area_unit', 'configuration')
            ->orderBy('floor_number')
            ->get();
        $buildings = Building::query()
            ->with('site:id,project_name,project_code')
            ->select('id', 'site_id', 'name', 'code', 'total_floors', 'total_flats', 'basement_count', 'parking_capacity')
            ->orderBy('name')
            ->get();

        foreach ($buildings as $building) {
            $buildingFloors = $floors->where('building_id', $building->id);
            $building->setAttribute('configured_flat_count', (int) $buildingFloors->sum('flats_count'));
            $building->setAttribute('configured_parking_count', (int) $buildingFloors->sum(
                fn (Floor $floor) => (int) ($floor->configuration['parking_spaces'] ?? 0)
            ));
        }

        return [
            'buildings' => $buildings,
            'floors' => $floors,
        ];
    }

    private function syncFloorConfiguration(
        int $floorId,
        array $flatData,
        array $commonAreaData,
        mixed $floorAreaSummary = null,
        mixed $parkingSpaces = null
    ): void {
        $floor = Floor::query()->findOrFail($floorId);

        $submittedNumbers = collect($flatData)
            ->reject(fn ($flat) => ! empty($flat['_delete']))
            ->pluck('flat_number')
            ->filter(fn ($number) => is_string($number) && trim($number) !== '')
            ->map(fn ($number) => trim($number));

        if ($submittedNumbers->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'flats' => 'Each flat number must be unique within the selected floor.',
            ]);
        }

        $activeExistingNumbers = $floor->flats()
            ->pluck('flat_number')
            ->map(fn ($number) => trim((string) $number));

        foreach ($submittedNumbers as $number) {
            $matchingFlat = collect($flatData)->first(function ($flat) use ($number) {
                return ! empty($flat['flat_number']) && trim((string) $flat['flat_number']) === $number;
            });
            $submittedId = $matchingFlat['id'] ?? null;

            $duplicate = $activeExistingNumbers->filter(fn ($existing) => $existing === $number)->count();
            if ($duplicate > 1 || ($duplicate === 1 && empty($submittedId))) {
                throw ValidationException::withMessages([
                    'flats' => 'Flat number '.$number.' already exists on this floor.',
                ]);
            }
        }

        foreach ($flatData as $flatInput) {
            if (! empty($flatInput['_delete'])) {
                if (! empty($flatInput['id'])) {
                    $flat = FloorFlat::query()
                        ->where('floor_id', $floorId)
                        ->find((int) $flatInput['id']);
                    if ($flat) {
                        $flat->delete();
                    }
                }

                continue;
            }

            $attributes = collect($flatInput)->except(['id', '_delete', 'rooms'])->mapWithKeys(function ($value, $key) {
                return [$key => $value === '' ? null : $value];
            })->all();
            $attributes['area_unit'] = $attributes['area_unit'] ?? 'sqft';
            foreach ([
                'bedroom_count', 'master_bedroom_count', 'bathroom_count', 'balcony_count',
                'kitchen_count', 'living_room_count', 'dining_room_count',
            ] as $countField) {
                $attributes[$countField] = (int) ($attributes[$countField] ?? 0);
            }

            $flat = null;
            if (! empty($flatInput['id'])) {
                $flat = FloorFlat::query()
                    ->where('floor_id', $floorId)
                    ->find((int) $flatInput['id']);
            }

            if ($flat) {
                $flat->update($attributes);
            } else {
                $flat = $floor->flats()->create($attributes);
            }

            foreach (($flatInput['rooms'] ?? []) as $roomInput) {
                if (! empty($roomInput['_delete'])) {
                    if (! empty($roomInput['id'])) {
                        $room = FloorFlatRoom::query()
                            ->where('flat_id', $flat->id)
                            ->find((int) $roomInput['id']);
                        if ($room) {
                            $room->delete();
                        }
                    }

                    continue;
                }

                $length = isset($roomInput['length']) && $roomInput['length'] !== '' ? (float) $roomInput['length'] : null;
                $width = isset($roomInput['width']) && $roomInput['width'] !== '' ? (float) $roomInput['width'] : null;
                $manualArea = isset($roomInput['area']) && $roomInput['area'] !== '' ? (float) $roomInput['area'] : null;
                $calculatedArea = ($length !== null && $width !== null) ? $length * $width : null;
                $area = $manualArea ?? $calculatedArea;

                $roomAttributes = collect($roomInput)
                    ->except(['id', '_delete'])
                    ->map(fn ($value) => $value === '' ? null : $value)
                    ->all();
                $roomAttributes['area'] = $area;
                $roomAttributes['area_unit'] = $roomAttributes['area_unit'] ?? 'sqft';
                $roomAttributes['attached_bathroom'] = (bool) ($roomAttributes['attached_bathroom'] ?? false);
                $roomAttributes['door_count'] = (int) ($roomAttributes['door_count'] ?? 0);
                $roomAttributes['window_count'] = (int) ($roomAttributes['window_count'] ?? 0);
                $roomAttributes['properties'] = $roomAttributes['properties'] ?? null;

                $room = null;
                if (! empty($roomInput['id'])) {
                    $room = FloorFlatRoom::query()
                        ->where('flat_id', $flat->id)
                        ->find((int) $roomInput['id']);
                }

                if ($room) {
                    $room->update($roomAttributes);
                } else {
                    $flat->rooms()->create($roomAttributes);
                }
            }
        }

        foreach ($commonAreaData as $areaInput) {
            if (! empty($areaInput['_delete'])) {
                if (! empty($areaInput['id'])) {
                    $area = FloorCommonArea::query()
                        ->where('floor_id', $floorId)
                        ->find((int) $areaInput['id']);
                    if ($area) {
                        $area->delete();
                    }
                }

                continue;
            }

            $length = isset($areaInput['length']) && $areaInput['length'] !== '' ? (float) $areaInput['length'] : null;
            $width = isset($areaInput['width']) && $areaInput['width'] !== '' ? (float) $areaInput['width'] : null;
            $manualArea = isset($areaInput['area']) && $areaInput['area'] !== '' ? (float) $areaInput['area'] : null;
            $areaAttributes = collect($areaInput)
                ->except(['id', '_delete'])
                ->map(fn ($value) => $value === '' ? null : $value)
                ->all();
            $areaAttributes['area'] = $manualArea ?? (($length !== null && $width !== null) ? $length * $width : null);
            $areaAttributes['area_unit'] = $areaAttributes['area_unit'] ?? 'sqft';

            $area = null;
            if (! empty($areaInput['id'])) {
                $area = FloorCommonArea::query()
                    ->where('floor_id', $floorId)
                    ->find((int) $areaInput['id']);
            }

            if ($area) {
                $area->update($areaAttributes);
            } else {
                $floor->commonAreas()->create($areaAttributes);
            }
        }

        $building = $floor->building;
        $configuredFlats = (int) $building->floors()->withCount('flats')->get()->sum('flats_count');
        if ($configuredFlats > (int) $building->total_flats) {
            throw ValidationException::withMessages([
                'flats' => 'Configured flats cannot exceed the building total of '.$building->total_flats.'.',
            ]);
        }

        if ($parkingSpaces !== null && $parkingSpaces !== '') {
            if ($floor->floor_type !== 'basement' && (int) $parkingSpaces > 0) {
                throw ValidationException::withMessages([
                    'parking_spaces' => 'Parking spaces can only be allocated to a basement floor.',
                ]);
            }

            $otherFloorParking = $building->floors()
                ->whereKeyNot($floor->id)
                ->get()
                ->sum(fn (Floor $otherFloor) => (int) ($otherFloor->configuration['parking_spaces'] ?? 0));
            if ($otherFloorParking + (int) $parkingSpaces > (int) $building->parking_capacity) {
                throw ValidationException::withMessages([
                    'parking_spaces' => 'Configured parking spaces cannot exceed the building capacity of '.$building->parking_capacity.'.',
                ]);
            }

            $configuration = $floor->configuration ?? [];
            $configuration['parking_spaces'] = (int) $parkingSpaces;
            $floor->configuration = $configuration;
        }

        $floorUpdates = [
            'common_area' => $floor->commonAreas()->sum('area') ?: null,
        ];
        if ($floorAreaSummary !== null && $floorAreaSummary !== '') {
            $totalFlatArea = (float) $floor->flats()->sum('area');
            $totalCommonArea = (float) $floor->commonAreas()->sum('area');
            if (abs((float) $floorAreaSummary - $totalFlatArea - $totalCommonArea) > 0.01) {
                throw ValidationException::withMessages([
                    'floor_area_summary' => 'Total floor area must equal total flat area plus total common area.',
                ]);
            }
            $floorUpdates['floor_area'] = (float) $floorAreaSummary;
        }
        $floor->update($floorUpdates);
    }

    private function storeFile(UploadedFile $file, int $buildingId): array
    {
        $path = $file->store('property/floor-plans/'.$buildingId, FloorPlan::STORAGE_DISK);
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        if (! is_string($path) || $path === '') {
            throw new RuntimeException('The floor plan file could not be stored or inspected.');
        }

        if (
            $fileSize === false
            || ! is_string($mimeType)
            || ! in_array($mimeType, FloorPlan::ALLOWED_MIME_TYPES, true)
        ) {
            $this->removeStoredFileOrFail($path);
            throw new RuntimeException('The floor plan file could not be inspected as an allowed file type.');
        }

        if (! Storage::disk(FloorPlan::STORAGE_DISK)->exists($path)) {
            throw new RuntimeException('The floor plan file was not found after upload.');
        }

        return [
            'file_path' => $path,
            'original_name' => $this->sanitizeName($file->getClientOriginalName()),
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
        ];
    }

    private function moveFileToBuilding(string $oldPath, int $buildingId): string
    {
        if (! $this->isManagedPath($oldPath)) {
            throw new RuntimeException('The existing floor plan file path is invalid.');
        }

        $extension = pathinfo($oldPath, PATHINFO_EXTENSION);
        $newPath = 'property/floor-plans/'.$buildingId.'/'.Str::random(40);
        if ($extension !== '') {
            $newPath .= '.'.$extension;
        }

        if (! Storage::disk(FloorPlan::STORAGE_DISK)->move($oldPath, $newPath)) {
            throw new RuntimeException('The floor plan file could not be moved to the selected building.');
        }

        return $newPath;
    }

    private function sanitizeName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
        $name = trim($name);

        return mb_substr($name !== '' && $name !== '.' && $name !== '..' ? $name : 'floor-plan', 0, 255);
    }

    private function existingFilePath(FloorPlan $floorPlan): string
    {
        abort_unless(in_array($floorPlan->mime_type, FloorPlan::ALLOWED_MIME_TYPES, true), 404);
        abort_unless($this->isManagedPath($floorPlan->file_path), 404);

        $disk = Storage::disk(FloorPlan::STORAGE_DISK);
        abort_unless($disk->exists($floorPlan->file_path), 404);

        return $disk->path($floorPlan->file_path);
    }

    private function isManagedPath(string $path): bool
    {
        $path = str_replace('\\', '/', $path);
        $segments = explode('/', $path);

        return count($segments) >= 4
            && $segments[0] === 'property'
            && $segments[1] === 'floor-plans'
            && ! in_array('..', $segments, true);
    }

    private function removeStoredFile(string $path): bool
    {
        if (! $this->isManagedPath($path)) {
            return false;
        }

        $disk = Storage::disk(FloorPlan::STORAGE_DISK);

        return ! $disk->exists($path) || $disk->delete($path);
    }

    private function removeStoredFileOrFail(string $path): void
    {
        if (! $this->removeStoredFile($path)) {
            throw new RuntimeException('A floor plan file could not be cleaned up after a failed database operation.');
        }
    }
}
