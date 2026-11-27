<div class="form-group">
    <label for="title">Title</label>
    <input id="title" name="title" type="text" maxlength="255" class="form-control"
           value="{{ old('title', $floorPlan->title) }}" required>
    @error('title')<small class="text-danger">{{ $message }}</small>@enderror
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label for="building_id">Building</label>
        <select id="building_id" name="building_id" class="form-control" required>
            <option value="">Select a building</option>
            @foreach($buildings as $building)
                <option value="{{ $building->id }}" {{ (string) old('building_id', $floorPlan->building_id) === (string) $building->id ? 'selected' : '' }}>
                    {{ $building->name }} ({{ $building->code }}) — {{ $building->site?->project_name ?? 'Site unavailable' }}
                </option>
            @endforeach
        </select>
        @error('building_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="form-group col-md-6">
        <label for="floor_id">Floor</label>
        <select id="floor_id" name="floor_id" class="form-control" required>
            <option value="">Select a floor</option>
            @foreach($floors as $floor)
                <option value="{{ $floor->id }}" data-building-id="{{ $floor->building_id }}"
                    {{ (string) old('floor_id', $floorPlan->floor_id) === (string) $floor->id ? 'selected' : '' }}>
                    {{ $floor->building?->name }} — {{ $floor->name }} ({{ $floor->floor_number }})
                </option>
            @endforeach
        </select>
        <small class="form-text text-muted">Only floors belonging to the selected building can be saved.</small>
        @error('floor_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>

<div class="form-group">
    <label for="description">Description <span class="text-muted">(optional)</span></label>
    <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $floorPlan->description) }}</textarea>
    @error('description')<small class="text-danger">{{ $message }}</small>@enderror
</div>

@if($floorPlan->exists)
    <div class="form-group">
        <label>Current File</label>
        <p class="mb-1">{{ $floorPlan->original_name }} ({{ $floorPlan->formattedFileSize() }})</p>
        @can('floor-plans-view')
            <a href="{{ route('floor-plans.preview', $floorPlan) }}" target="_blank" rel="noopener noreferrer">Preview current file</a>
        @endcan
    </div>
@endif

<div class="form-group">
    <label for="file">{{ $floorPlan->exists ? 'Replace File (optional)' : 'Floor Plan File' }}</label>
    <input id="file" name="file" type="file" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png"
           {{ $floorPlan->exists ? '' : 'required' }}>
    <small class="form-text text-muted">PDF, JPG, JPEG, or PNG; maximum 10 MB.</small>
    @error('file')<small class="text-danger">{{ $message }}</small>@enderror
</div>

@push('scripts')
<script>
    (function () {
        const buildingSelect = document.getElementById('building_id');
        const floorSelect = document.getElementById('floor_id');

        function filterFloors() {
            const buildingId = buildingSelect.value;
            let selectedFloorIsVisible = false;

            Array.from(floorSelect.options).forEach(function (option) {
                if (!option.value) {
                    return;
                }

                const visible = buildingId !== '' && option.dataset.buildingId === buildingId;
                option.hidden = !visible;
                option.disabled = !visible;

                if (visible && option.selected) {
                    selectedFloorIsVisible = true;
                }
            });

            floorSelect.disabled = buildingId === '';
            if (!selectedFloorIsVisible) {
                floorSelect.value = '';
            }
        }

        buildingSelect.addEventListener('change', filterFloors);
        filterFloors();
    })();
</script>
@endpush
