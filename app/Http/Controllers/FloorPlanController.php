<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFloorPlanRequest;
use App\Http\Requests\UpdateFloorPlanRequest;
use App\Models\Building;
use App\Models\Floor;
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

        return view('floor-plans.create', array_merge(
            $this->formOptions(),
            ['floorPlan' => new FloorPlan($selection)]
        ));
    }

    public function store(StoreFloorPlanRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $upload = $request->file('file');
        unset($attributes['file']);

        if (! $upload instanceof UploadedFile) {
            throw new RuntimeException('A floor plan file is required.');
        }

        $storedFile = $this->storeFile($upload, (int) $attributes['building_id']);

        try {
            $floorPlan = DB::transaction(fn () => FloorPlan::create(array_merge($attributes, $storedFile)));
        } catch (Throwable $exception) {
            $this->removeStoredFileOrFail($storedFile['file_path']);
            throw $exception;
        }

        return redirect()
            ->route('floors.show', $floorPlan->floor_id)
            ->with('success', 'Floor plan uploaded successfully.');
    }

    public function edit(FloorPlan $floorPlan): View
    {
        $floorPlan->load(['building', 'floor']);

        return view('floor-plans.edit', array_merge(
            $this->formOptions(),
            compact('floorPlan')
        ));
    }

    public function update(UpdateFloorPlanRequest $request, FloorPlan $floorPlan): RedirectResponse
    {
        $attributes = $request->validated();
        $upload = $request->file('file');
        unset($attributes['file']);

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
                DB::transaction(fn () => $floorPlan->update($attributes));
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
            DB::transaction(fn () => $floorPlan->update(array_merge($attributes, $storedFile)));
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
        return [
            'buildings' => Building::query()
                ->with('site:id,project_name,project_code')
                ->select('id', 'site_id', 'name', 'code')
                ->orderBy('name')
                ->get(),
            'floors' => Floor::query()
                ->with('building:id,name,code')
                ->select('id', 'building_id', 'name', 'floor_number')
                ->orderBy('floor_number')
                ->get(),
        ];
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
