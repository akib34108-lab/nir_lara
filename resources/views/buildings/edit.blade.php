@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Building</h4>
                <p class="card-description">Update building information.</p>
                @if($errors->any())
                    <div class="alert alert-danger">Please correct the highlighted fields.</div>
                @endif

                <form method="POST" action="{{ route('buildings.update', $building) }}">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="site_id">Site</label>
                        <select id="site_id" name="site_id" class="form-control" required>
                            <option value="">Select a site</option>
                            @foreach($sites as $site)
                                <option value="{{ $site->id }}" {{ (string) old('site_id', $building->site_id) === (string) $site->id ? 'selected' : '' }}>
                                    {{ $site->project_name }} ({{ $site->project_code }})
                                </option>
                            @endforeach
                        </select>
                        @error('site_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="name">Building Name</label>
                            <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $building->name) }}" required>
                            @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="code">Building Code</label>
                            <input id="code" name="code" type="text" class="form-control" value="{{ old('code', $building->code) }}" required>
                            @error('code')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="building_type">Building Type</label>
                            <select id="building_type" name="building_type" class="form-control" required>
                                <option value="">Select a type</option>
                                @foreach($buildingTypes as $type)
                                    <option value="{{ $type }}" {{ old('building_type', $building->building_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('building_type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="structure_type">Structure Type</label>
                            <select id="structure_type" name="structure_type" class="form-control" required>
                                <option value="">Select a structure</option>
                                @foreach($structureTypes as $type)
                                    <option value="{{ $type }}" {{ old('structure_type', $building->structure_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            @error('structure_type')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-4">
                            <label for="total_floors">Total Floors</label>
                            <input id="total_floors" name="total_floors" type="number" min="0" step="1" class="form-control" value="{{ old('total_floors', $building->total_floors) }}" required>
                            @error('total_floors')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="total_flats">Total Flats</label>
                            <input id="total_flats" name="total_flats" type="number" min="0" step="1" class="form-control" value="{{ old('total_flats', $building->total_flats) }}" required>
                            @error('total_flats')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-4">
                            <label for="basement_count">Basement Count</label>
                            <input id="basement_count" name="basement_count" type="number" min="0" step="1" class="form-control" value="{{ old('basement_count', $building->basement_count) }}" required>
                            @error('basement_count')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="elevator_count">Elevator Count</label>
                            <input id="elevator_count" name="elevator_count" type="number" min="0" step="1" class="form-control" value="{{ old('elevator_count', $building->elevator_count) }}" required>
                            @error('elevator_count')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                        <div class="col-md-6">
                            <label for="parking_capacity">Parking Capacity</label>
                            <input id="parking_capacity" name="parking_capacity" type="number" min="0" step="1" class="form-control" value="{{ old('parking_capacity', $building->parking_capacity) }}" required>
                            @error('parking_capacity')<small class="text-danger">{{ $message }}</small>@enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control" required>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $building->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="4" class="form-control">{{ old('description', $building->description) }}</textarea>
                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ti-save"></i> Update Building
                    </button>
                    <a href="{{ route('buildings.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>

@include('layouts.partials.footer')
