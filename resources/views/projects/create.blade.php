@extends('layouts.master')
@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Create Project</h4>
                        <p class="card-description">
                            Enter project information below
                        </p>
                        <hr/>
                        <form class="forms-sample" method="POST" action="{{ route('projects.store') }}">
                            @csrf
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label class="col-form-label">Project Code</label>
                                    <input type="text" name="project_code"
                                        class="form-control"
                                        value="{{ $nextProjectCode }}" readonly
                                        placeholder="Enter project code" required>
                                    @error('project_code')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Client</label>
                                    <select name="client_id" class="form-control" required>
                                        <option value="">Select Client</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Project Manager</label>
                                    <select name="project_manager_id" class="form-control" required>
                                        <option value="">Select Project Manager</option>
                                        @foreach($projectManagers as $manager)
                                            <option value="{{ $manager->id }}" {{ old('project_manager_id') == $manager->id ? 'selected' : '' }}>{{ $manager->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('project_manager_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label class="col-form-label">Project Name</label>
                                    <input type="text" name="project_name"
                                        class="form-control"
                                        value="{{ old('project_name') }}"
                                        placeholder="Enter project name" required>
                                    @error('project_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Description</label>
                                    <input type="text" name="description"
                                        class="form-control"
                                        value="{{ old('description') }}"
                                        placeholder="Enter description">
                                    @error('description')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Site Address</label>
                                    <input type="text" name="site_address"
                                        class="form-control"
                                        value="{{ old('site_address') }}"
                                        placeholder="Enter site address">
                                    @error('site_address')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label class="col-form-label">City</label>
                                    <input type="text" name="city"
                                        class="form-control"
                                        value="{{ old('city') }}"
                                        placeholder="Enter city">
                                    @error('city')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Start Date</label>
                                    <input type="date" name="start_date"
                                        class="form-control"
                                        value="{{ old('start_date') }}"
                                        placeholder="Enter start date">
                                    @error('start_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Expected End Date</label>
                                    <input type="date" name="expected_end_date"
                                        class="form-control"
                                        value="{{ old('expected_end_date') }}"
                                        placeholder="Enter expected end date">
                                    @error('expected_end_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label class="col-form-label">Actual End Date</label>
                                    <input type="date" name="actual_end_date"
                                        class="form-control"
                                        value="{{ old('actual_end_date') }}"
                                        placeholder="Enter actual end date">
                                    @error('actual_end_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Estimated Budget</label>
                                    <input type="number" name="estimated_budget" step="0.01" min="0"
                                        class="form-control"
                                        value="{{ old('estimated_budget') }}"
                                        placeholder="Enter Estimated Budget">
                                    @error('estimated_budget')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-sm-3 col-form-label">Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Planning</option>
                                        <option value="2" {{ old('status') == 2 ? 'selected' : '' }}>Pending</option>
                                        <option value="3" {{ old('status') == 3 ? 'selected' : '' }}>Active</option>
                                        <option value="4" {{ old('status') == 4 ? 'selected' : '' }}>On Hold</option>
                                        <option value="5" {{ old('status') == 5 ? 'selected' : '' }}>Completed</option>
                                        <option value="6" {{ old('status') == 6 ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                    @error('status')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <label class="col-form-label">Progress(%)</label>
                                    <input type="number" name="progress"
                                        class="form-control"
                                        value="{{ old('progress', '0') }}"
                                        placeholder="Enter Progress">
                                    @error('progress')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <hr/>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary mr-2">
                                    Next <i class="ti-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection