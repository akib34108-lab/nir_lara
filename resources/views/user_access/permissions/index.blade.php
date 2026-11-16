@extends('layouts.master')
@section('content')
<div class="content-wrapper">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    {{-- Card Header --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="card-title mb-1">All Permissions</h4>
                            <p class="card-description mb-0">
                                Manage all system permissions.
                            </p>
                        </div>
                        <div>
                            <button type="button" class="btn btn-primary"
                                    data-toggle="modal"
                                    data-target="#permissionModal">
                                <i class="ti-plus"></i> Add Permission
                            </button>
                        </div>
                    </div>
                    {{-- Permission Table --}}
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Permission Name</th>
                                    <th>Module Name</th>
                                    <th>Guard Name</th>
                                    <th>Created Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $permission)
                                    @php
                                        $module = explode('-', $permission->name)[0];
                                    @endphp
                                    <tr>
                                        <td>
                                            {{ $permissions->firstItem() + $loop->index }}
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $permission->name }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ ucfirst($module) }}
                                        </td>
                                        <td>
                                            {{ $permission->guard_name }}
                                        </td>
                                        <td>
                                            {{ $permission->created_at->format('d M, Y') }}
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm"
                                                        type="button"
                                                        data-toggle="dropdown"
                                                        aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <i class="ti-more-alt"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @can('permissions-edit')
                                                        <button type="button"
                                                                class="dropdown-item edit-permission-btn"
                                                                data-toggle="modal"
                                                                data-target="#permissionModal"
                                                                data-id="{{ $permission->id }}"
                                                                data-module="{{ explode('-', $permission->name, 2)[0] }}"
                                                                data-action="{{ explode('-', $permission->name, 2)[1] ?? '' }}"
                                                                data-url="{{ route('permissions.update', $permission->id) }}">
                                                            <i class="ti-pencil mr-2"></i> Edit
                                                        </button>
                                                    @endcan
                                                    @can('permissions-delete')
                                                        <form action="{{ route('permissions.destroy', $permission->id) }}"
                                                              method="POST"
                                                              onsubmit="return confirm('Are you sure you want to delete '+ '{{ $permission->name }}' + '?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="dropdown-item text-danger">

                                                                <i class="ti-trash mr-2"></i> Delete
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            No permissions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    <div class="d-flex justify-content-center mt-3">
                        {{ $permissions->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Add / Edit Permission Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1"
     role="dialog" aria-labelledby="permissionModalLabel"
     aria-hidden="true">

    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="permissionModalLabel">
                    Add Permission
                </h5>

                <button type="button" class="close"
                        data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="permissionForm"
                  action="{{ route('permissions.store') }}"
                  method="POST">

                @csrf

                <div id="methodField"></div>

                <div class="modal-body">

                    <div class="form-group">
                        <label for="module">
                            Module Name <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="module" id="module"
                               class="form-control"
                               placeholder="e.g. projects" required>
                    </div>

                    <div class="form-group">
                        <label for="action">
                            Permission Action <span class="text-danger">*</span>
                        </label>

                        <select name="action" id="action"
                                class="form-control" required>
                            <option value="">Select Action</option>
                            <option value="view">View</option>
                            <option value="create">Create</option>
                            <option value="edit">Edit</option>
                            <option value="delete">Delete</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="permission_name">
                            Permission Name
                        </label>

                        <input type="text" id="permission_name"
                               class="form-control"
                               placeholder="projects-create" readonly>
                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-light"
                            data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary"
                            id="permissionSubmitBtn">
                        <i class="ti-plus"></i> Save Permission
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
$(document).ready(function () {

    let storeUrl = "{{ route('permissions.store') }}";

    // Generate Permission Name
    function generatePermissionName() {

        let module = $('#module').val().trim().toLowerCase();
        let action = $('#action').val();

        let name = '';

        if (module && action) {
            name = module + '-' + action;
        }

        $('#permission_name').val(name);
    }

    $('#module, #action').on('input change', function () {
        generatePermissionName();
    });


    // Add Permission
    $('[data-target="#permissionModal"]').not('.edit-permission-btn').on('click', function () {

        $('#permissionForm')[0].reset();

        $('#permissionForm').attr('action', storeUrl);

        $('#methodField').html('');

        $('#permissionModalLabel').text('Add Permission');

        $('#permissionSubmitBtn').html(
            '<i class="ti-plus"></i> Save Permission'
        );

        generatePermissionName();
    });


    // Edit Permission
    $('.edit-permission-btn').on('click', function () {

        let id = $(this).data('id');
        let module = $(this).data('module');
        let action = $(this).data('action');
        let updateUrl = $(this).data('url');

        $('#permissionForm').attr('action', updateUrl);

        $('#methodField').html(
            '<input type="hidden" name="_method" value="PUT">'
        );

        $('#module').val(module);
        $('#action').val(action);

        $('#permissionModalLabel').text('Edit Permission');

        $('#permissionSubmitBtn').html(
            '<i class="ti-pencil"></i> Update Permission'
        );

        generatePermissionName();
    });

});
</script>
@endpush
