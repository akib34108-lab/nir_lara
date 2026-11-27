@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <strong>Please correct the highlighted fields.</strong>
                            </div>
                        @endif
                        <form class="forms-sample" method="POST" action="{{ route('sites.store') }}">
                            @csrf

                            <h5 class="mb-3">Project Information</h5>
                            <hr>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="col-form-label" for="project_code">Site Code</label>
                                    <input id="project_code" type="text" name="project_code" class="form-control"
                                        value="{{ old('project_code', $nextSiteCode) }}" readonly required>
                                    @error('project_code')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label" for="project_name">Project Name</label>
                                    <input id="project_name" type="text" name="project_name" class="form-control"
                                        value="{{ old('project_name') }}" placeholder="Enter project name" required>
                                    @error('project_name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label" for="project_status">Status</label>
                                    <select id="project_status" name="project_status" class="form-control" required>
                                        <option value="1" {{ old('project_status', 1) == 1 ? 'selected' : '' }}>Planning</option>
                                        <option value="2" {{ old('project_status') == 2 ? 'selected' : '' }}>Active</option>
                                        <option value="3" {{ old('project_status') == 3 ? 'selected' : '' }}>On Hold</option>
                                        <option value="4" {{ old('project_status') == 4 ? 'selected' : '' }}>Completed</option>
                                        <option value="5" {{ old('project_status') == 5 ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('project_status')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" for="description">Project Description</label>
                                <textarea id="description" name="description" class="form-control" rows="3"
                                        placeholder="Describe the project">{{ old('description') }}</textarea>
                                @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <h5 class="mb-3 mt-4">Location Information</h5>
                            <hr>
                            <div class="form-group">
                                <label class="col-form-label" for="address">Address</label>
                                <input id="address" type="text" name="address" class="form-control"
                                    value="{{ old('address') }}" placeholder="Enter street address" required>
                                @error('address')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="col-form-label" for="area">Area</label>
                                    <input id="area" type="text" name="area" class="form-control"
                                        value="{{ old('area') }}" placeholder="Enter area">
                                    @error('area')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label" for="city">City</label>
                                    <input id="city" type="text" name="city" class="form-control"
                                        value="{{ old('city') }}" placeholder="Enter city" required>
                                    @error('city')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label" for="district">District</label>
                                    <input id="district" type="text" name="district" class="form-control"
                                        value="{{ old('district') }}" placeholder="Enter district" required>
                                    @error('district')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="col-form-label" for="division">Division</label>
                                    <input id="division" type="text" name="division" class="form-control"
                                        value="{{ old('division') }}" placeholder="Enter division">
                                    @error('division')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label" for="country">Country</label>
                                    <input id="country" type="text" name="country" class="form-control"
                                        value="{{ old('country', 'Bangladesh') }}" required>
                                    @error('country')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label" for="postal_code">Postal Code</label>
                                    <input id="postal_code" type="text" name="postal_code" class="form-control"
                                        value="{{ old('postal_code') }}" placeholder="Enter postal code">
                                    @error('postal_code')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" for="google_maps_location">Google Maps Location</label>
                                <input id="google_maps_location" type="url" name="google_maps_location" class="form-control"
                                    value="{{ old('google_maps_location') }}" placeholder="https://maps.google.com/...">
                                @error('google_maps_location')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <h5 class="mb-3 mt-4">Land Information</h5>
                            <hr>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label class="col-form-label" for="land_area">Land Area</label>
                                    <input id="land_area" type="number" name="land_area" class="form-control"
                                        value="{{ old('land_area') }}" min="0" step="0.01" placeholder="Enter land area" required>
                                    @error('land_area')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label" for="land_area_unit">Area Unit</label>
                                    <select id="land_area_unit" name="land_area_unit" class="form-control" required>
                                        <option value="1" {{ old('land_area_unit', 1) == 1 ? 'selected' : '' }}>Katha</option>
                                        <option value="2" {{ old('land_area_unit') == 2 ? 'selected' : '' }}>Decimal</option>
                                        <option value="3" {{ old('land_area_unit') == 3 ? 'selected' : '' }}>Acre</option>
                                        <option value="4" {{ old('land_area_unit') == 4 ? 'selected' : '' }}>Bigha</option>
                                        <option value="5" {{ old('land_area_unit') == 5 ? 'selected' : '' }}>Square Feet</option>
                                        <option value="6" {{ old('land_area_unit') == 6 ? 'selected' : '' }}>Square Meter</option>
                                    </select>
                                    @error('land_area_unit')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-3">
                                    <label class="col-form-label" for="plot_number">Plot Number</label>
                                    <input id="plot_number" type="text" name="plot_number" class="form-control"
                                        value="{{ old('plot_number') }}">
                                    @error('plot_number')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="col-form-label" for="block">Block</label>
                                    <input id="block" type="text" name="block" class="form-control" value="{{ old('block') }}">
                                    @error('block')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="col-form-label" for="road_number">Road Number</label>
                                    <input id="road_number" type="text" name="road_number" class="form-control"
                                        value="{{ old('road_number') }}">
                                    @error('road_number')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="col-form-label" for="sector">Sector</label>
                                    <input id="sector" type="text" name="sector" class="form-control" value="{{ old('sector') }}">
                                    @error('sector')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-form-label" for="land_description">Land Description</label>
                                <textarea id="land_description" name="land_description" class="form-control" rows="3"
                                        placeholder="Additional land details">{{ old('land_description') }}</textarea>
                                @error('land_description')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>

                            <h5 class="mb-3 mt-4">Project Timeline</h5>
                            <hr>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label class="col-form-label" for="planned_start_date">Planned Start Date</label>
                                    <input id="planned_start_date" type="date" name="planned_start_date" class="form-control"
                                        value="{{ old('planned_start_date') }}">
                                    @error('planned_start_date')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label" for="planned_completion_date">Planned Completion Date</label>
                                    <input id="planned_completion_date" type="date" name="planned_completion_date" class="form-control"
                                        value="{{ old('planned_completion_date') }}">
                                    @error('planned_completion_date')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label class="col-form-label" for="actual_start_date">Actual Start Date</label>
                                    <input id="actual_start_date" type="date" name="actual_start_date" class="form-control"
                                        value="{{ old('actual_start_date') }}">
                                    @error('actual_start_date')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="col-form-label" for="actual_completion_date">Actual Completion Date</label>
                                    <input id="actual_completion_date" type="date" name="actual_completion_date" class="form-control"
                                        value="{{ old('actual_completion_date') }}">
                                    @error('actual_completion_date')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <h5 class="mb-3 mt-4">Shares & Budget</h5>
                            <hr>
                            <div class="form-group row">
                                <div class="col-md-4">
                                    <label class="col-form-label" for="total_shares">Total Shares</label>
                                    <input id="total_shares" type="number" name="total_shares" class="form-control"
                                        value="{{ old('total_shares') }}" min="0" step="1">
                                    @error('total_shares')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label" for="per_share_value">Value per Share (৳)</label>
                                    <input id="per_share_value" type="number" name="per_share_value" class="form-control"
                                        value="{{ old('per_share_value') }}" min="0" step="0.01">
                                    @error('per_share_value')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="col-form-label" for="estimated_project_budget">Estimated Project Budget (৳)</label>
                                    <input id="estimated_project_budget" type="number" name="estimated_project_budget" class="form-control"
                                        value="{{ old('estimated_project_budget') }}" min="0" step="0.01" readonly>
                                    @error('estimated_project_budget')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <hr>
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="ti-save"></i> Save Site
                            </button>
                            <a href="{{ route('sites.index') }}" class="btn btn-light">
                                <i class="ti-arrow-left"></i> Cancel
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
    <script>
        (() => {
            const totalSharesInput = document.getElementById('total_shares');
            const perShareValueInput = document.getElementById('per_share_value');
            const budgetInput = document.getElementById('estimated_project_budget');

            const calculateBudget = (clearWhenIncomplete) => {
                if (totalSharesInput.value === '' || perShareValueInput.value === '') {
                    if (clearWhenIncomplete) {
                        budgetInput.value = '';
                    }
                    return;
                }

                const totalShares = Number(totalSharesInput.value);
                const perShareValue = Number(perShareValueInput.value);
                if (Number.isFinite(totalShares) && Number.isFinite(perShareValue)) {
                    budgetInput.value = (totalShares * perShareValue).toFixed(2);
                }
            };

            totalSharesInput.addEventListener('input', () => calculateBudget(true));
            perShareValueInput.addEventListener('input', () => calculateBudget(true));
            calculateBudget(false);
        })();
    </script>
@endpush
@include('layouts.partials.footer')
