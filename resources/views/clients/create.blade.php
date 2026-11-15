@extends('layouts.master')
@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Create Client</h4>
                        <p class="card-description">
                            Enter client information below
                        </p>
                        <form class="forms-sample" method="POST" action="{{ route('clients.store') }}">
                            @csrf
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label class="col-form-label">Client Code</label>
                                    <input type="text" name="client_code"
                                        class="form-control"
                                        value="{{ $nextClientCode }}" readonly
                                        placeholder="Enter client code" required>
                                    @error('client_code')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Client Type</label>
                                    <select name="client_type" class="form-control" required>
                                        <option value="">Select Client Type</option>
                                        <option value="1" {{ old('client_type', 1) == 1 ? 'selected' : '' }}>Individual</option>
                                        <option value="2" {{ old('client_type') == 2 ? 'selected' : '' }}>Company</option>
                                        <option value="3" {{ old('client_type') == 3 ? 'selected' : '' }}>Government</option>
                                        <option value="4" {{ old('client_type') == 4 ? 'selected' : '' }}>Organization</option>
                                    </select>
                                    @error('client_type')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Client Name</label>
                                    <input type="text" name="name"
                                        class="form-control"
                                        value="{{ old('name') }}"
                                        placeholder="Enter client name" required>
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label class="col-form-label">Company Name</label>
                                    <input type="text" name="company_name"
                                        class="form-control"
                                        value="{{ old('company_name') }}"
                                        placeholder="Enter company name">
                                    @error('company_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Email</label>
                                    <input type="email" name="email"
                                        class="form-control"
                                        value="{{ old('email') }}"
                                        placeholder="Enter email address">
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Phone</label>
                                    <input type="text" name="phone"
                                        class="form-control"
                                        value="{{ old('phone') }}"
                                        placeholder="Enter phone number" required>
                                    @error('phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label class="col-form-label">Alternate Phone</label>
                                    <input type="text" name="alternate_phone"
                                        class="form-control"
                                        value="{{ old('alternate_phone') }}"
                                        placeholder="Enter alternate phone">
                                    @error('alternate_phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="col-form-label">Website</label>
                                    <input type="text" name="website"
                                        class="form-control"
                                        value="{{ old('website') }}"
                                        placeholder="https://example.com">
                                    @error('website')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-sm-4">
                                <label class="col-form-label">Tax Number</label>
                                    <input type="text" name="tax_number"
                                        class="form-control"
                                        value="{{ old('tax_number') }}"
                                        placeholder="Enter tax number">
                                    @error('tax_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-4">
                                    <label class="col-form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="3"
                                        placeholder="Enter full address">{{ old('address') }}</textarea>
                                    @error('address')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
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
                                    <label class="col-form-label">State</label>
                                    <input type="text" name="state"
                                        class="form-control"
                                        value="{{ old('state') }}"
                                        placeholder="Enter state">
                                    @error('state')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            {{-- Additional Information --}}
                            <h5 class="mb-3">Additional Information</h5>
                            <hr>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Country</label>
                                <div class="col-sm-9">
                                    <input type="text" name="country"
                                        class="form-control"
                                        value="{{ old('country', 'Bangladesh') }}"
                                        placeholder="Enter country">
                                    @error('country')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Postal Code</label>
                                <div class="col-sm-9">
                                    <input type="text" name="postal_code"
                                        class="form-control"
                                        value="{{ old('postal_code') }}"
                                        placeholder="Enter postal code">
                                    @error('postal_code')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Notes</label>
                                <div class="col-sm-9">
                                    <textarea name="notes" class="form-control" rows="3"
                                        placeholder="Enter additional notes">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Status</label>
                                <div class="col-sm-9">
                                    <select name="status" class="form-control" required>
                                        <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="2" {{ old('status') == 2 ? 'selected' : '' }}>Inactive</option>
                                        <option value="3" {{ old('status') == 3 ? 'selected' : '' }}>Pending</option>
                                        <option value="4" {{ old('status') == 4 ? 'selected' : '' }}>Blocked</option>
                                    </select>
                                    @error('status')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary mr-2">
                                Save Client
                            </button>

                            <a href="{{ route('clients.index') }}" class="btn btn-light">
                                Cancel
                            </a>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
