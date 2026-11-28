@php
    $configurationValue = $floor->configuration !== null
        ? json_encode($floor->configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        : '';
@endphp

<div class="form-group">
    <label for="name">Floor Name</label>
    <input id="name" name="name" type="text" class="form-control"
           value="{{ old('name', $floor->name) }}" maxlength="255" required>
    @error('name')<small class="text-danger">{{ $message }}</small>@enderror
</div>

<div class="form-group row">
    <div class="col-md-4">
        <label for="floor_number">Floor Number</label>
        <input id="floor_number" name="floor_number" type="number" min="-10" max="200" step="1"
               class="form-control" value="{{ old('floor_number', $floor->floor_number) }}" required>
        <small class="form-text text-muted">Suggestion: use a negative number for a basement and 0 for ground floor.</small>
        @error('floor_number')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="col-md-4">
        <label for="floor_type">Floor Type</label>
        <select id="floor_type" name="floor_type" class="form-control" required>
            <option value="">Select a floor type</option>
            @foreach($floorTypes as $value => $label)
                <option value="{{ $value }}" {{ old('floor_type', $floor->floor_type) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('floor_type')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="col-md-4">
        <label for="status">Status</label>
        <select id="status" name="status" class="form-control" required>
            @foreach($statuses as $value => $label)
                <option value="{{ $value }}" {{ old('status', $floor->status) === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('status')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>

<div class="form-group row">
    <div class="col-md-6">
        <label for="floor_area">Floor Area</label>
        <input id="floor_area" name="floor_area" type="number" min="0" step="0.01"
               class="form-control" value="{{ old('floor_area', $floor->floor_area) }}">
        @error('floor_area')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
    <div class="col-md-6">
        <label for="floor_area_unit">Area Unit</label>
        <select id="floor_area_unit" name="floor_area_unit" class="form-control" required>
            @foreach($floorAreaUnits as $value => $label)
                <option value="{{ $value }}" {{ old('floor_area_unit', $floor->floor_area_unit ?? 'sqft') === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('floor_area_unit')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>

<div class="form-group">
    <label for="common_area">Common Area</label>
    <input id="common_area" name="common_area" type="number" min="0" step="0.01"
           class="form-control" value="{{ old('common_area', $floor->common_area) }}">
    @error('common_area')<small class="text-danger">{{ $message }}</small>@enderror
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea id="description" name="description" rows="3" class="form-control">{{ old('description', $floor->description) }}</textarea>
    @error('description')<small class="text-danger">{{ $message }}</small>@enderror
</div>

<div class="form-group">
    <label for="notes">Notes</label>
    <textarea id="notes" name="notes" rows="3" class="form-control">{{ old('notes', $floor->notes) }}</textarea>
    @error('notes')<small class="text-danger">{{ $message }}</small>@enderror
</div>