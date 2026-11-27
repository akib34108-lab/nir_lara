@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
<div class="content-wrapper">
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Role Information</h4>
                    <p class="card-description">
                        Update role information and permissions.
                    </p>
                    <form action="{{ route('roles.update', $role->id) }}"
                          method="POST">
                        @csrf
                        @method('PUT')
                        {{-- Role Name --}}
                        <div class="form-group">
                            <label for="name">
                                Role Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $role->name) }}"
                                   placeholder="Enter role name">
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <hr>
                        {{-- Permission Heading --}}
                        <div class="mb-3">
                            <h4 class="card-title mb-1">Assign Permissions</h4>
                            <small class="text-muted">
                                Select the permissions for this role.
                            </small>
                        </div>
                        @error('permissions')
                            <div class="text-danger mb-2">
                                {{ $message }}
                            </div>
                        @enderror
                        {{-- Select All Permissions --}}
                        <div class="form-group">
                            <div class="form-check">
                                <label class="form-check-label font-weight-bold">
                                    <input type="checkbox"
                                           class="form-check-input"
                                           id="selectAllPermissions">
                                    Select All Permissions
                                    <i class="input-helper"></i>
                                </label>
                            </div>
                        </div>
                        {{-- Permission Groups --}}
                        @php
                            $groupedPermissions = $permissions->groupBy(function ($permission) {
                                return explode('-', $permission->name)[0];
                            });
                            $selectedPermissions = old('permissions', $rolePermissions);
                        @endphp
                        <div class="row">
                            @foreach($groupedPermissions as $module => $modulePermissions)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="card border">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0 text-capitalize">
                                                {{ $module }}
                                            </h5>
                                            <div class="form-check mb-0">
                                                <label class="form-check-label small">
                                                    <input type="checkbox"
                                                           class="form-check-input select-module"
                                                           data-module="{{ $module }}">
                                                    Select All
                                                    <i class="input-helper"></i>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            @foreach($modulePermissions as $permission)
                                                <div class="form-check mb-2">
                                                    <label class="form-check-label">
                                                        <input type="checkbox"
                                                               name="permissions[]"
                                                               value="{{ $permission->name }}"
                                                               class="form-check-input permission-checkbox"
                                                               data-module="{{ $module }}"
                                                               {{ in_array($permission->name, $selectedPermissions) ? 'checked' : '' }}>
                                                        {{ $permission->name }}
                                                        <i class="input-helper"></i>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <hr>
                        {{-- Buttons --}}
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="ti-pencil"></i> Update Role
                        </button>
                        <a href="{{ route('roles.index') }}"
                           class="btn btn-light">
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
    $(document).ready(function () {
        // Select All Permissions
        $('#selectAllPermissions').on('change', function () {
            let checked = $(this).is(':checked');
            $('.permission-checkbox, .select-module')
                .prop('checked', checked);
        });
        // Select All Module Permissions
        $('.select-module').on('change', function () {
            let module = $(this).data('module');
            let checked = $(this).is(':checked');
            $('.permission-checkbox[data-module="' + module + '"]')
                .prop('checked', checked);
            updateAllCheckboxes();
        });
        // Individual Permission Checkbox
        $('.permission-checkbox').on('change', function () {
            updateAllCheckboxes()
        })
        // Update Select All Checkbox Status
        function updateAllCheckboxes() {
            $('.select-module').each(function () {
                let module = $(this).data('module');
                let total = $('.permission-checkbox[data-module="' + module + '"]').length;
                let checked = $('.permission-checkbox[data-module="' + module + '"]:checked').length;
                $(this).prop('checked', total === checked);
            });
            let totalPermissions = $('.permission-checkbox').length;
            let selectedPermissions = $('.permission-checkbox:checked').length;
            $('#selectAllPermissions').prop(
                'checked',
                totalPermissions === selectedPermissions
            );
        }
        // Initial Checkbox Status
        updateAllCheckboxes();
    });
</script>
@endpush
@include('layouts.partials.footer')
