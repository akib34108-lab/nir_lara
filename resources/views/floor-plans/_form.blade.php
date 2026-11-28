    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="building_id">Building</label>
            <select id="building_id" name="building_id" class="form-control" required>
                <option value="">Select a building</option>
                @foreach($buildings as $building)
                    <option value="{{ $building->id }}"
                        data-total-floors="{{ $building->total_floors }}"
                        data-total-flats="{{ $building->total_flats }}"
                        data-basement-count="{{ $building->basement_count }}"
                        data-parking-capacity="{{ $building->parking_capacity }}"
                        data-configured-flats="{{ $building->configured_flat_count }}"
                        data-configured-parking="{{ $building->configured_parking_count }}"
                        {{ (string) old('building_id', $selectedBuildingId ?? $floorPlan->building_id) === (string) $building->id ? 'selected' : '' }}>
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
                    <option value="{{ $floor->id }}"
                        data-building-id="{{ $floor->building_id }}"
                        data-floor-name="{{ $floor->name }}"
                        data-floor-number="{{ $floor->floor_number }}"
                        data-floor-type="{{ $floor->floor_type }}"
                        data-floor-area="{{ $floor->floor_area }}"
                        data-floor-area-unit="{{ $floor->floor_area_unit }}"
                        data-configured-flats="{{ $floor->flats_count }}"
                        data-parking-spaces="{{ $floor->configuration['parking_spaces'] ?? 0 }}"
                        {{ (string) old('floor_id', $selectedFloorId ?? $floorPlan->floor_id) === (string) $floor->id ? 'selected' : '' }}>
                        {{ $floor->name }} ({{ $floor->floor_number }}) — {{ \App\Models\Floor::FLOOR_TYPES[$floor->floor_type] ?? ucfirst($floor->floor_type) }}
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted">Only floors belonging to the selected building are selectable. Basement floors are included.</small>
            @error('floor_id')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    @error('flats')<div class="text-danger mb-2">{{ $message }}</div>@enderror
    <div id="building-summary" class="row mb-3">
        <div class="col-md-3"><div class="border rounded p-2"><small class="text-muted">Building Total Floors</small><h5 id="building-total-floors">—</h5></div></div>
        <div class="col-md-3"><div class="border rounded p-2"><small class="text-muted">Building Total Flats</small><h5 id="building-total-flats">—</h5></div></div>
        <div class="col-md-3"><div class="border rounded p-2"><small class="text-muted">Basement Floors</small><h5 id="building-basement-count">—</h5></div></div>
        <div class="col-md-3"><div class="border rounded p-2"><small class="text-muted">Total Parking Spaces</small><h5 id="building-parking-capacity">—</h5></div></div>
        <div class="col-md-3 mt-2"><div class="border rounded p-2"><small class="text-muted">Configured Flats</small><h5 id="building-configured-flats">—</h5></div></div>
        <div class="col-md-3 mt-2"><div class="border rounded p-2"><small class="text-muted">Remaining Flats</small><h5 id="building-remaining-flats">—</h5></div></div>
        <div class="col-md-3 mt-2"><div class="border rounded p-2"><small class="text-muted">Configured Parking Spaces</small><h5 id="building-configured-parking">—</h5></div></div>
        <div class="col-md-3 mt-2"><div class="border rounded p-2"><small class="text-muted">Remaining Parking Spaces</small><h5 id="building-remaining-parking">—</h5></div></div>
    </div>
    <div id="floor-information" class="alert alert-light border" hidden>
        <strong id="selected-floor-heading"></strong>
        <span class="ml-2">Type: <span id="selected-floor-type"></span></span>
        <span class="ml-2">Area: <span id="selected-floor-area"></span></span>
    </div>
    <div class="form-group" id="parking-allocation-group" hidden>
        <label for="parking_spaces">Parking Spaces Allocated to This Basement</label>
        <input id="parking_spaces" name="parking_spaces" type="number" min="0" step="1" class="form-control"
               value="{{ old('parking_spaces', $floorPlan->floor?->configuration['parking_spaces'] ?? 0) }}">
        @error('parking_spaces')<small class="text-danger">{{ $message }}</small>@enderror
        <small class="form-text text-muted">Counts against the Building's existing parking capacity. Parking is not counted as Flats.</small>
    </div>

    @php
        $initialFlats = old('flats');
        if (! is_array($initialFlats)) {
            $initialFlats = $floorPlan->floor
                ? ($floorPlan->floor->flats->map(function ($flat) {
                    return array_merge($flat->toArray(), ['rooms' => $flat->rooms->map->toArray()->all()]);
                })->all() ?? [])
                : [];
        }

        $initialCommonAreas = old('common_areas');
        if (! is_array($initialCommonAreas)) {
            $initialCommonAreas = $floorPlan->floor
                ? ($floorPlan->floor->commonAreas->map->toArray()->all() ?? [])
                : [];
        }

        $roomTypes = [
            'master_bedroom' => 'Master Bedroom',
            'bedroom' => 'Bedroom',
            'living_room' => 'Living Room',
            'dining_room' => 'Dining Room',
            'kitchen' => 'Kitchen',
            'bathroom' => 'Bathroom',
            'common_bathroom' => 'Common Bathroom',
            'balcony' => 'Balcony',
            'store_room' => 'Store Room',
            'study_room' => 'Study Room',
            'maid_room' => 'Maid Room',
            'utility_room' => 'Utility Room',
            'other' => 'Other',
        ];
    @endphp

    <hr>
    <h5 class="mb-3">Floor Summary &amp; Flat Configuration</h5>
    <div class="row mb-3">
        <div class="col-md-3"><div class="border rounded p-2"><small class="text-muted">Total Flats</small><h5 id="summary-flats">0</h5></div></div>
        <div class="col-md-3"><div class="border rounded p-2"><small class="text-muted">Flat Area</small><h5 id="summary-flat-area">0.00</h5></div></div>
        <div class="col-md-3"><div class="border rounded p-2"><small class="text-muted">Common Area</small><h5 id="summary-common-area">0.00</h5></div></div>
        <div class="col-md-3"><div class="border rounded p-2"><small class="text-muted">Remaining Area</small><h5 id="summary-remaining-area">0.00</h5></div></div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="floor_area_summary">Total Floor Area</label>
            <input id="floor_area_summary" type="number" min="0" step="0.01" class="form-control"
                   value="{{ old('floor_area_summary', $floorPlan->floor?->floor_area) }}">
        </div>
        <div class="form-group col-md-8">
            <small class="text-muted d-block mt-4">Total Floor Area must equal Flat Area plus Common Area. A selected flat's rooms should not be added to floor area twice.</small>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <label class="mb-0">Flats</label>
        <div class="form-inline">
            <input id="flat-count" type="number" min="0" max="500" class="form-control form-control-sm mr-2"
                   value="{{ count(array_filter($initialFlats, fn ($flat) => empty($flat['_delete']))) }}" placeholder="Flat count">
            <button type="button" id="generate-flats" class="btn btn-sm btn-outline-secondary mr-2">Generate</button>
            <button type="button" id="add-flat" class="btn btn-sm btn-outline-primary"><i class="ti-plus"></i> Add Flat</button>
        </div>
    </div>
    <div id="flats-container">
    @foreach($initialFlats as $flatIndex => $flat)
        @php $flatData = (array) $flat; @endphp
        <div class="card mb-3 flat-card" data-flat-index="{{ $flatIndex }}">
            <input type="hidden" name="flats[{{ $flatIndex }}][id]" value="{{ $flatData['id'] ?? '' }}">
            <input type="hidden" name="flats[{{ $flatIndex }}][_delete]" value="0" class="flat-delete">
            <div class="card-header d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-link p-0 text-left flat-collapse" data-toggle="collapse" data-target="#flat-{{ $flatIndex }}">
                    Flat <span class="flat-title">{{ $flatData['flat_number'] ?? 'New' }}</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger remove-flat">Remove</button>
            </div>
            <div id="flat-{{ $flatIndex }}" class="collapse show">
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-3"><label>Flat Number</label><input name="flats[{{ $flatIndex }}][flat_number]" class="form-control flat-number" value="{{ $flatData['flat_number'] ?? '' }}" required></div>
                        <div class="form-group col-md-3"><label>Flat Name</label><input name="flats[{{ $flatIndex }}][name]" class="form-control" value="{{ $flatData['name'] ?? '' }}"></div>
                        <div class="form-group col-md-3"><label>Status</label><input name="flats[{{ $flatIndex }}][status]" class="form-control" value="{{ $flatData['status'] ?? 'planned' }}"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4"><label>Flat Area</label><input name="flats[{{ $flatIndex }}][area]" type="number" min="0" step="0.01" class="form-control flat-area" value="{{ $flatData['area'] ?? '' }}"></div>
                        <div class="form-group col-md-2"><label>Unit</label><select name="flats[{{ $flatIndex }}][area_unit]" class="form-control"><option value="sqft" {{ ($flatData['area_unit'] ?? 'sqft') === 'sqft' ? 'selected' : '' }}>sqft</option><option value="sqm" {{ ($flatData['area_unit'] ?? '') === 'sqm' ? 'selected' : '' }}>sqm</option></select></div>
                        <div class="form-group col-md-2"><label>Bedrooms</label><input name="flats[{{ $flatIndex }}][bedroom_count]" type="number" min="0" class="form-control room-quantity" data-room="bedroom" value="{{ $flatData['bedroom_count'] ?? 0 }}"></div>
                        <div class="form-group col-md-2"><label>Master Bedrooms</label><input name="flats[{{ $flatIndex }}][master_bedroom_count]" type="number" min="0" class="form-control room-quantity" data-room="master_bedroom" value="{{ $flatData['master_bedroom_count'] ?? 0 }}"></div>
                        <div class="form-group col-md-2"><label>Bathrooms</label><input name="flats[{{ $flatIndex }}][bathroom_count]" type="number" min="0" class="form-control room-quantity" data-room="bathroom" value="{{ $flatData['bathroom_count'] ?? 0 }}"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-2"><label>Balconies</label><input name="flats[{{ $flatIndex }}][balcony_count]" type="number" min="0" class="form-control room-quantity" data-room="balcony" value="{{ $flatData['balcony_count'] ?? 0 }}"></div>
                        <div class="form-group col-md-2"><label>Kitchens</label><input name="flats[{{ $flatIndex }}][kitchen_count]" type="number" min="0" class="form-control room-quantity" data-room="kitchen" value="{{ $flatData['kitchen_count'] ?? 0 }}"></div>
                        <div class="form-group col-md-2"><label>Living Rooms</label><input name="flats[{{ $flatIndex }}][living_room_count]" type="number" min="0" class="form-control room-quantity" data-room="living_room" value="{{ $flatData['living_room_count'] ?? 0 }}"></div>
                        <div class="form-group col-md-2"><label>Dining Rooms</label><input name="flats[{{ $flatIndex }}][dining_room_count]" type="number" min="0" class="form-control room-quantity" data-room="dining_room" value="{{ $flatData['dining_room_count'] ?? 0 }}"></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label>Description</label><textarea name="flats[{{ $flatIndex }}][description]" rows="2" class="form-control">{{ $flatData['description'] ?? '' }}</textarea></div>
                        <div class="form-group col-md-6"><label>Notes</label><textarea name="flats[{{ $flatIndex }}][notes]" rows="2" class="form-control">{{ $flatData['notes'] ?? '' }}</textarea></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Rooms</h6>
                        <div><button type="button" class="btn btn-sm btn-outline-secondary generate-rooms">Generate from quantities</button> <button type="button" class="btn btn-sm btn-outline-primary add-room"><i class="ti-plus"></i> Add Room</button></div>
                    </div>
                    <div class="rooms-container">
                    @foreach(($flatData['rooms'] ?? []) as $roomIndex => $room)
                        @php $roomData = (array) $room; @endphp
                        <div class="card mb-2 room-card">
                            <input type="hidden" name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][id]" value="{{ $roomData['id'] ?? '' }}">
                            <input type="hidden" name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][_delete]" value="0" class="room-delete">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between"><strong class="room-title">{{ $roomTypes[$roomData['room_type'] ?? ''] ?? 'Room' }}</strong><button type="button" class="btn btn-xs btn-outline-danger remove-room">Remove</button></div>
                                <div class="form-row mt-2">
                                    <div class="form-group col-md-3"><label>Room Name</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][name]" class="form-control" value="{{ $roomData['name'] ?? '' }}"></div>
                                    <div class="form-group col-md-2"><label>Length</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][length]" type="number" min="0" step="0.01" class="form-control room-length" value="{{ $roomData['length'] ?? '' }}"></div>
                                    <div class="form-group col-md-2"><label>Width</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][width]" type="number" min="0" step="0.01" class="form-control room-width" value="{{ $roomData['width'] ?? '' }}"></div>
                                    <div class="form-group col-md-2"><label>Area</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][area]" type="number" min="0" step="0.01" class="form-control room-area" value="{{ $roomData['area'] ?? '' }}"></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-2"><label>Unit</label><select name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][area_unit]" class="form-control"><option value="sqft" {{ ($roomData['area_unit'] ?? 'sqft') === 'sqft' ? 'selected' : '' }}>sqft</option><option value="sqm" {{ ($roomData['area_unit'] ?? '') === 'sqm' ? 'selected' : '' }}>sqm</option></select></div>
                                    <div class="form-group col-md-2"><label>Floor Level</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][floor_level]" class="form-control" value="{{ $roomData['floor_level'] ?? '' }}"></div>
                                    <div class="form-group col-md-2"><label>Attached Bath</label><select name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][attached_bathroom]" class="form-control"><option value="0" {{ !($roomData['attached_bathroom'] ?? false) ? 'selected' : '' }}>No</option><option value="1" {{ ($roomData['attached_bathroom'] ?? false) ? 'selected' : '' }}>Yes</option></select></div>
                                    <div class="form-group col-md-2"><label>Doors</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][door_count]" type="number" min="0" class="form-control" value="{{ $roomData['door_count'] ?? 0 }}"></div>
                                    <div class="form-group col-md-2"><label>Windows</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][window_count]" type="number" min="0" class="form-control" value="{{ $roomData['window_count'] ?? 0 }}"></div>
                                    <div class="form-group col-md-2"><label>AC Provision</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][ac_provision]" class="form-control" value="{{ $roomData['ac_provision'] ?? '' }}"></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-3"><label>Flooring</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][flooring_type]" class="form-control" value="{{ $roomData['flooring_type'] ?? '' }}"></div>
                                    <div class="form-group col-md-3"><label>Ceiling</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][ceiling_type]" class="form-control" value="{{ $roomData['ceiling_type'] ?? '' }}"></div>
                                    <div class="form-group col-md-3"><label>Wall Finish</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][wall_finish]" class="form-control" value="{{ $roomData['wall_finish'] ?? '' }}"></div>
                                    <div class="form-group col-md-3"><label>Ventilation</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][ventilation_details]" class="form-control" value="{{ $roomData['ventilation_details'] ?? '' }}"></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-4"><label>Electrical Provision</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][electrical_provision]" class="form-control" value="{{ $roomData['electrical_provision'] ?? '' }}"></div>
                                    <div class="form-group col-md-4"><label>Plumbing Provision</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][plumbing_provision]" class="form-control" value="{{ $roomData['plumbing_provision'] ?? '' }}"></div>
                                    <div class="form-group col-md-4"><label>Notes</label><input name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][notes]" class="form-control" value="{{ $roomData['notes'] ?? '' }}"></div>
                                </div>
                                <div class="form-group mb-0"><label>Description</label><textarea name="flats[{{ $flatIndex }}][rooms][{{ $roomIndex }}][description]" rows="2" class="form-control">{{ $roomData['description'] ?? '' }}</textarea></div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    </div>

    <hr>
    <div class="d-flex justify-content-between align-items-center mb-2"><h5 class="mb-0">Common Areas</h5><button type="button" id="add-common-area" class="btn btn-sm btn-outline-primary"><i class="ti-plus"></i> Add Common Area</button></div>
    <div id="common-areas-container">
    @foreach($initialCommonAreas as $areaIndex => $area)
    @php $areaData = (array) $area; @endphp
    <div class="card mb-2 common-area-card">
        <input type="hidden" name="common_areas[{{ $areaIndex }}][id]" value="{{ $areaData['id'] ?? '' }}">
        <input type="hidden" name="common_areas[{{ $areaIndex }}][_delete]" value="0" class="common-area-delete">
        <div class="card-body">
            <div class="d-flex justify-content-end"><button type="button" class="btn btn-sm btn-outline-danger remove-common-area">Remove</button></div>
            <div class="form-row mt-2">
                <div class="form-group col-md-3"><label>Area Name</label><input name="common_areas[{{ $areaIndex }}][name]" class="form-control" value="{{ $areaData['name'] ?? '' }}" required></div>
                <div class="form-group col-md-3"><label>Area Type</label><input name="common_areas[{{ $areaIndex }}][area_type]" class="form-control" value="{{ $areaData['area_type'] ?? '' }}" required></div>
                <div class="form-group col-md-2"><label>Length</label><input name="common_areas[{{ $areaIndex }}][length]" type="number" min="0" step="0.01" class="form-control common-length" value="{{ $areaData['length'] ?? '' }}"></div>
                <div class="form-group col-md-2"><label>Width</label><input name="common_areas[{{ $areaIndex }}][width]" type="number" min="0" step="0.01" class="form-control common-width" value="{{ $areaData['width'] ?? '' }}"></div>
                <div class="form-group col-md-2"><label>Area</label><input name="common_areas[{{ $areaIndex }}][area]" type="number" min="0" step="0.01" class="form-control common-area" value="{{ $areaData['area'] ?? '' }}"></div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-2"><label>Unit</label><select name="common_areas[{{ $areaIndex }}][area_unit]" class="form-control"><option value="sqft" {{ ($areaData['area_unit'] ?? 'sqft') === 'sqft' ? 'selected' : '' }}>sqft</option><option value="sqm" {{ ($areaData['area_unit'] ?? '') === 'sqm' ? 'selected' : '' }}>sqm</option></select></div>
                <div class="form-group col-md-5"><label>Description</label><textarea name="common_areas[{{ $areaIndex }}][description]" rows="2" class="form-control">{{ $areaData['description'] ?? '' }}</textarea></div>
                <div class="form-group col-md-5"><label>Notes</label><textarea name="common_areas[{{ $areaIndex }}][notes]" rows="2" class="form-control">{{ $areaData['notes'] ?? '' }}</textarea></div>
            </div>
        </div>
    </div>
    @endforeach
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
        const parkingInput = document.getElementById('parking_spaces');
        let previousFloorId = floorSelect.value;

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
            updateBuildingSummary();
            updateFloorInformation();
        }

        function updateBuildingSummary() {
            const option = buildingSelect.options[buildingSelect.selectedIndex];
            const data = option && option.value ? option.dataset : null;
            const ids = [
                'building-total-floors', 'building-total-flats', 'building-basement-count',
                'building-parking-capacity', 'building-configured-flats', 'building-remaining-flats',
                'building-configured-parking', 'building-remaining-parking', 'building-flat-status'
            ];
            if (!data) {
                ids.forEach(id => document.getElementById(id).textContent = '—');
                return;
            }

            const selectedFloor = floorSelect.options[floorSelect.selectedIndex];
            const selectedFloorFlats = selectedFloor && selectedFloor.value
                ? parseInt(selectedFloor.dataset.configuredFlats || '0', 10)
                : 0;
            const visibleFlatCount = Array.from(document.querySelectorAll('.flat-card')).filter(function (card) {
                return card.style.display !== 'none' && card.querySelector('.flat-delete')?.value !== '1';
            }).length;
            const configuredFlats = Math.max(0, parseInt(data.configuredFlats || '0', 10) - selectedFloorFlats + visibleFlatCount);

            const selectedFloorParking = selectedFloor && selectedFloor.value
                ? parseInt(selectedFloor.dataset.parkingSpaces || '0', 10)
                : 0;
            const enteredParking = selectedFloor && selectedFloor.dataset.floorType === 'basement'
                ? parseInt(parkingInput.value || '0', 10)
                : 0;
            const configuredParking = Math.max(0, parseInt(data.configuredParking || '0', 10) - selectedFloorParking + enteredParking);
            const totalFlats = parseInt(data.totalFlats || '0', 10);
            const parkingCapacity = parseInt(data.parkingCapacity || '0', 10);

            document.getElementById('building-total-floors').textContent = data.totalFloors;
            document.getElementById('building-total-flats').textContent = totalFlats;
            document.getElementById('building-basement-count').textContent = data.basementCount;
            document.getElementById('building-parking-capacity').textContent = parkingCapacity;
            document.getElementById('building-configured-flats').textContent = configuredFlats;
            document.getElementById('building-remaining-flats').textContent = totalFlats - configuredFlats;
            document.getElementById('building-configured-parking').textContent = configuredParking;
            document.getElementById('building-remaining-parking').textContent = parkingCapacity - configuredParking;
            document.getElementById('building-flat-status').textContent = configuredFlats === totalFlats ? 'Complete' : 'Incomplete';
        }

        function updateFloorInformation() {
            const option = floorSelect.options[floorSelect.selectedIndex];
            const selected = option && option.value;
            document.getElementById('floor-information').hidden = !selected;
            document.getElementById('parking-allocation-group').hidden = !selected || option.dataset.floorType !== 'basement';

            if (!selected) {
                return;
            }

            document.getElementById('selected-floor-heading').textContent =
                option.dataset.floorName + ' (Floor ' + option.dataset.floorNumber + ')';
            document.getElementById('selected-floor-type').textContent =
                option.dataset.floorType.replace(/_/g, ' ').replace(/\b\w/g, value => value.toUpperCase());
            const area = option.dataset.floorArea;
            document.getElementById('selected-floor-area').textContent =
                area ? Number(area).toFixed(2) + ' ' + option.dataset.floorAreaUnit : 'Not set';
            document.getElementById('floor_area_summary').value = area || '';
            document.getElementById('flat-count').value = activeFlatCount();
            if (option.dataset.floorType === 'basement') {
                parkingInput.value = option.dataset.parkingSpaces || '0';
            }
            updateBuildingSummary();
        }

        buildingSelect.addEventListener('change', filterFloors);
        floorSelect.addEventListener('change', function () {
            const selected = floorSelect.options[floorSelect.selectedIndex];
            if (!selected || !selected.value) {
                updateFloorInformation();
                return;
            }

            if (selected.value !== previousFloorId) {
                if (!confirm('Changing floors reloads the form with that floor’s saved configuration. Unsaved changes will be lost. Continue?')) {
                    floorSelect.value = previousFloorId;
                    updateFloorInformation();
                    return;
                }

                const url = new URL(window.location.href);
                url.searchParams.set('building_id', buildingSelect.value);
                url.searchParams.set('floor_id', selected.value);
                window.location.assign(url.toString());
                return;
            }
            updateFloorInformation();
        });
        parkingInput.addEventListener('input', updateBuildingSummary);
        document.addEventListener('input', function (event) {
            if (event.target.closest('.flat-card')) {
                updateBuildingSummary();
            }
        });
        document.addEventListener('click', function (event) {
            if (event.target.closest('.remove-flat') || event.target.closest('#add-flat') || event.target.closest('#generate-flats')) {
                window.setTimeout(updateBuildingSummary, 0);
            }
        });
        filterFloors();
    })();
</script>
@endpush

@push('scripts')
<script>
(function () {
    const roomLabels = {
        master_bedroom: 'Master Bedroom', bedroom: 'Bedroom', living_room: 'Living Room',
        dining_room: 'Dining Room', kitchen: 'Kitchen', bathroom: 'Bathroom',
        common_bathroom: 'Common Bathroom', balcony: 'Balcony', store_room: 'Store Room',
        study_room: 'Study Room', maid_room: 'Maid Room', utility_room: 'Utility Room', other: 'Other'
    };

    function nextIndex(container, className) {
        return container.querySelectorAll(className).length;
    }

    function activeFlatCount() {
        return Array.from(document.querySelectorAll('.flat-card')).filter(function (card) {
            return card.style.display !== 'none' && card.querySelector('.flat-delete')?.value !== '1';
        }).length;
    }

    function addFlatCard() {
        const container = document.getElementById('flats-container');
        const flatIndex = nextIndex(container, '.flat-card');
        const number = 'A-' + String(flatIndex + 101);
        const card = document.createElement('div');
        card.className = 'card mb-3 flat-card';
        card.dataset.flatIndex = flatIndex;
        card.innerHTML = `
            <input type="hidden" name="flats[${flatIndex}][id]" value="">
            <input type="hidden" name="flats[${flatIndex}][_delete]" value="0" class="flat-delete">
            <div class="card-header d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-link p-0 text-left flat-collapse" data-toggle="collapse" data-target="#flat-${flatIndex}">Flat <span class="flat-title">${number}</span></button>
                <button type="button" class="btn btn-sm btn-outline-danger remove-flat">Remove</button>
            </div>
            <div id="flat-${flatIndex}" class="collapse show">
              <div class="card-body">
                <div class="form-row">
                  <div class="form-group col-md-3"><label>Flat Number</label><input name="flats[${flatIndex}][flat_number]" class="form-control flat-number" value="${number}" required></div>
                  <div class="form-group col-md-3"><label>Flat Name</label><input name="flats[${flatIndex}][name]" class="form-control"></div>
                  <div class="form-group col-md-3"><label>Status</label><input name="flats[${flatIndex}][status]" class="form-control" value="planned"></div>
                </div>
                <div class="form-row">
                  <div class="form-group col-md-4"><label>Flat Area</label><input name="flats[${flatIndex}][area]" type="number" min="0" step="0.01" class="form-control flat-area"></div>
                  <div class="form-group col-md-2"><label>Unit</label><select name="flats[${flatIndex}][area_unit]" class="form-control"><option value="sqft">sqft</option><option value="sqm">sqm</option></select></div>
                  ${roomCountFields(flatIndex)}
                </div>
                <div class="form-row"><div class="form-group col-md-6"><label>Description</label><textarea name="flats[${flatIndex}][description]" rows="2" class="form-control"></textarea></div><div class="form-group col-md-6"><label>Notes</label><textarea name="flats[${flatIndex}][notes]" rows="2" class="form-control"></textarea></div></div>
                <div class="d-flex justify-content-between align-items-center mb-2"><h6 class="mb-0">Rooms</h6><div><button type="button" class="btn btn-sm btn-outline-secondary generate-rooms">Generate from quantities</button> <button type="button" class="btn btn-sm btn-outline-primary add-room"><i class="ti-plus"></i> Add Room</button></div></div>
                <div class="rooms-container"></div>
              </div>
            </div>`;
        container.appendChild(card);
        bindCardEvents(card);
        updateSummary();
        return card;
    }

    function roomCountFields(flatIndex) {
        const fields = [
            ['bedroom_count', 'bedroom', 'Bedrooms'], ['master_bedroom_count', 'master_bedroom', 'Master Bedrooms'],
            ['bathroom_count', 'bathroom', 'Bathrooms']
        ];
        let html = fields.map(function (field) {
            return `<div class="form-group col-md-2"><label>${field[2]}</label><input name="flats[${flatIndex}][${field[0]}]" type="number" min="0" class="form-control room-quantity" data-room="${field[1]}" value="0"></div>`;
        }).join('');
        html += [
            ['balcony_count','balcony','Balconies'], ['kitchen_count','kitchen','Kitchens'],
            ['living_room_count','living_room','Living Rooms'], ['dining_room_count','dining_room','Dining Rooms']
        ].map(function (field) {
            return `<div class="form-group col-md-2"><label>${field[2]}</label><input name="flats[${flatIndex}][${field[0]}]" type="number" min="0" class="form-control room-quantity" data-room="${field[1]}" value="0"></div>`;
        }).join('');
        return html;
    }

    function addRoomCard(flatCard) {
        const rooms = flatCard.querySelector('.rooms-container');
        const flatIndex = flatCard.dataset.flatIndex;
        const roomIndex = rooms.querySelectorAll('.room-card').length;
        const card = document.createElement('div');
        card.className = 'card mb-2 room-card';
        card.innerHTML = `
            <input type="hidden" name="flats[${flatIndex}][rooms][${roomIndex}][id]" value="">
            <input type="hidden" name="flats[${flatIndex}][rooms][${roomIndex}][_delete]" value="0" class="room-delete">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between"><strong class="room-title">Room</strong><button type="button" class="btn btn-xs btn-outline-danger remove-room">Remove</button></div>
              <div class="form-row mt-2">
                <div class="form-group col-md-3"><label>Room Name</label><input name="flats[${flatIndex}][rooms][${roomIndex}][name]" class="form-control"></div>
                <div class="form-group col-md-2"><label>Length</label><input name="flats[${flatIndex}][rooms][${roomIndex}][length]" type="number" min="0" step="0.01" class="form-control room-length"></div>
                <div class="form-group col-md-2"><label>Width</label><input name="flats[${flatIndex}][rooms][${roomIndex}][width]" type="number" min="0" step="0.01" class="form-control room-width"></div>
                <div class="form-group col-md-2"><label>Area</label><input name="flats[${flatIndex}][rooms][${roomIndex}][area]" type="number" min="0" step="0.01" class="form-control room-area"></div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-2"><label>Unit</label><select name="flats[${flatIndex}][rooms][${roomIndex}][area_unit]" class="form-control"><option value="sqft">sqft</option><option value="sqm">sqm</option></select></div>
                <div class="form-group col-md-2"><label>Floor Level</label><input name="flats[${flatIndex}][rooms][${roomIndex}][floor_level]" class="form-control"></div>
                <div class="form-group col-md-2"><label>Attached Bath</label><select name="flats[${flatIndex}][rooms][${roomIndex}][attached_bathroom]" class="form-control"><option value="0">No</option><option value="1">Yes</option></select></div>
                <div class="form-group col-md-2"><label>Doors</label><input name="flats[${flatIndex}][rooms][${roomIndex}][door_count]" type="number" min="0" class="form-control" value="0"></div>
                <div class="form-group col-md-2"><label>Windows</label><input name="flats[${flatIndex}][rooms][${roomIndex}][window_count]" type="number" min="0" class="form-control" value="0"></div>
                <div class="form-group col-md-2"><label>AC</label><input name="flats[${flatIndex}][rooms][${roomIndex}][ac_provision]" class="form-control"></div>
              </div>
              <div class="form-row"><div class="form-group col-md-3"><label>Flooring</label><input name="flats[${flatIndex}][rooms][${roomIndex}][flooring_type]" class="form-control"></div><div class="form-group col-md-3"><label>Ceiling</label><input name="flats[${flatIndex}][rooms][${roomIndex}][ceiling_type]" class="form-control"></div><div class="form-group col-md-3"><label>Wall Finish</label><input name="flats[${flatIndex}][rooms][${roomIndex}][wall_finish]" class="form-control"></div><div class="form-group col-md-3"><label>Ventilation</label><input name="flats[${flatIndex}][rooms][${roomIndex}][ventilation_details]" class="form-control"></div></div>
              <div class="form-row"><div class="form-group col-md-4"><label>Electrical Provision</label><input name="flats[${flatIndex}][rooms][${roomIndex}][electrical_provision]" class="form-control"></div><div class="form-group col-md-4"><label>Plumbing Provision</label><input name="flats[${flatIndex}][rooms][${roomIndex}][plumbing_provision]" class="form-control"></div><div class="form-group col-md-4"><label>Notes</label><input name="flats[${flatIndex}][rooms][${roomIndex}][notes]" class="form-control"></div></div>
              <div class="form-group mb-0"><label>Description</label><textarea name="flats[${flatIndex}][rooms][${roomIndex}][description]" rows="2" class="form-control"></textarea></div>
            </div>`;
        rooms.appendChild(card);
        bindRoomEvents(card);
        return card;
    }

    function addCommonAreaCard() {
        const container = document.getElementById('common-areas-container');
        const index = nextIndex(container, '.common-area-card');
        const card = document.createElement('div');
        card.className = 'card mb-2 common-area-card';
        card.innerHTML = `<input type="hidden" name="common_areas[${index}][id]" value=""><input type="hidden" name="common_areas[${index}][_delete]" value="0" class="common-area-delete"><div class="card-body"><div class="d-flex justify-content-end"><button type="button" class="btn btn-sm btn-outline-danger remove-common-area">Remove</button></div><div class="form-row mt-2"><div class="form-group col-md-3"><label>Area Name</label><input name="common_areas[${index}][name]" class="form-control" required></div><div class="form-group col-md-3"><label>Area Type</label><input name="common_areas[${index}][area_type]" class="form-control" required></div><div class="form-group col-md-2"><label>Length</label><input name="common_areas[${index}][length]" type="number" min="0" step="0.01" class="form-control common-length"></div><div class="form-group col-md-2"><label>Width</label><input name="common_areas[${index}][width]" type="number" min="0" step="0.01" class="form-control common-width"></div><div class="form-group col-md-2"><label>Area</label><input name="common_areas[${index}][area]" type="number" min="0" step="0.01" class="form-control common-area"></div></div><div class="form-row"><div class="form-group col-md-2"><label>Unit</label><select name="common_areas[${index}][area_unit]" class="form-control"><option value="sqft">sqft</option><option value="sqm">sqm</option></select></div><div class="form-group col-md-5"><label>Description</label><textarea name="common_areas[${index}][description]" rows="2" class="form-control"></textarea></div><div class="form-group col-md-5"><label>Notes</label><textarea name="common_areas[${index}][notes]" rows="2" class="form-control"></textarea></div></div></div>`;
        container.appendChild(card);
        bindCommonEvents(card);
        updateSummary();
    }

    function bindRoomEvents(card) {
        card.querySelector('.room-length').addEventListener('input', () => calculateRoomArea(card));
        card.querySelector('.room-width').addEventListener('input', () => calculateRoomArea(card));
        card.querySelector('.room-type').addEventListener('change', function () {
            card.querySelector('.room-title').textContent = roomLabels[this.value] || 'Room';
        });
        card.querySelector('.remove-room').addEventListener('click', function () {
            if (!confirm('Remove this room?')) return;
            card.querySelector('.room-delete').value = '1';
            card.style.display = 'none';
        });
    }

    function calculateRoomArea(card) {
        const length = parseFloat(card.querySelector('.room-length').value);
        const width = parseFloat(card.querySelector('.room-width').value);
        const area = card.querySelector('.room-area');
        if (!isNaN(length) && !isNaN(width) && length >= 0 && width >= 0) area.value = (length * width).toFixed(2);
    }

    function bindCommonEvents(card) {
        const length = card.querySelector('.common-length');
        const width = card.querySelector('.common-width');
        const area = card.querySelector('.common-area');
        function calculate() {
            const l = parseFloat(length.value), w = parseFloat(width.value);
            if (!isNaN(l) && !isNaN(w) && l >= 0 && w >= 0) area.value = (l * w).toFixed(2);
            updateSummary();
        }
        length.addEventListener('input', calculate);
        width.addEventListener('input', calculate);
        area.addEventListener('input', updateSummary);
        card.querySelector('.remove-common-area').addEventListener('click', function () {
            if (!confirm('Remove this common area?')) return;
            card.querySelector('.common-area-delete').value = '1';
            card.querySelectorAll('[required]').forEach(input => input.required = false);
            card.style.display = 'none';
            updateSummary();
        });
    }

    function bindCardEvents(card) {
        card.querySelector('.flat-number').addEventListener('input', function () {
            card.querySelector('.flat-title').textContent = this.value || 'New';
        });
        card.querySelector('.flat-area').addEventListener('input', updateSummary);
        card.querySelector('.remove-flat').addEventListener('click', function () {
            if (!confirm('Remove this flat? Its room records remain until you explicitly confirm deletion.')) return;
            card.querySelector('.flat-delete').value = '1';
            card.querySelector('.flat-number').required = false;
            card.style.display = 'none';
            updateSummary();
        });
        card.querySelector('.add-room').addEventListener('click', () => addRoomCard(card));
        card.querySelector('.generate-rooms').addEventListener('click', () => generateRooms(card));
        card.querySelectorAll('.room-card').forEach(bindRoomEvents);
    }

    function generateRooms(flatCard) {
        const existingCounts = {};
        flatCard.querySelectorAll('.room-card').forEach(function (roomCard) {
            if (roomCard.style.display === 'none') return;
            const type = roomCard.querySelector('.room-type')?.value;
            if (type) existingCounts[type] = (existingCounts[type] || 0) + 1;
        });
        flatCard.querySelectorAll('.room-quantity').forEach(function (input) {
            const type = input.dataset.room;
            const desired = parseInt(input.value || '0', 10);
            const existing = existingCounts[type] || 0;
            for (let i = existing; i < desired; i++) {
                const roomCard = addRoomCard(flatCard);
                roomCard.querySelector('.room-type').value = type;
                roomCard.querySelector('.room-title').textContent = roomLabels[type];
                roomCard.querySelector('.room-type').dispatchEvent(new Event('change'));
            }
        });
    }

    function updateSummary() {
        let flatArea = 0, flatCount = 0, commonArea = 0;
        document.querySelectorAll('.flat-card').forEach(function (card) {
            if (card.style.display === 'none' || card.querySelector('.flat-delete')?.value === '1') return;
            flatCount++;
            flatArea += parseFloat(card.querySelector('.flat-area')?.value || 0) || 0;
        });
        document.querySelectorAll('.common-area-card').forEach(function (card) {
            if (card.style.display === 'none' || card.querySelector('.common-area-delete')?.value === '1') return;
            commonArea += parseFloat(card.querySelector('.common-area')?.value || 0) || 0;
        });
        const floorArea = parseFloat(document.getElementById('floor_area_summary')?.value || 0) || 0;
        document.getElementById('summary-flats').textContent = flatCount;
        document.getElementById('summary-flat-area').textContent = flatArea.toFixed(2);
        document.getElementById('summary-common-area').textContent = commonArea.toFixed(2);
        document.getElementById('summary-remaining-area').textContent = (floorArea - flatArea - commonArea).toFixed(2);
    }

    document.getElementById('add-flat').addEventListener('click', addFlatCard);
    document.getElementById('generate-flats').addEventListener('click', function () {
        const count = parseInt(document.getElementById('flat-count').value || '0', 10);
        if (count < 0) return;
        while (activeFlatCount() < count) addFlatCard();
    });
    document.getElementById('add-common-area').addEventListener('click', addCommonAreaCard);
    document.querySelectorAll('.flat-card').forEach(bindCardEvents);
    document.querySelectorAll('.room-card').forEach(bindRoomEvents);
    document.querySelectorAll('.common-area-card').forEach(bindCommonEvents);
    document.getElementById('floor_area_summary').addEventListener('input', updateSummary);
    updateSummary();
})();
</script>
@endpush
