@extends('layouts.master')
@section('content')
<style>
    .popover {
        max-width: 280px;
    }
    .popover-body {
        max-height: 250px;
        overflow-y: auto;
        overflow-x: hidden;
    }
</style>
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">Role List</h4>
                        </div>
                        <div class="col-md-4 justify-content-end d-flex">
                            @can('roles-create')
                                <a href="{{ route('roles.create') }}"
                                   class="btn btn-primary btn-fw">
                                    <i class="ti-plus"></i> Add Role
                                </a>
                            @endcan
                        </div>
                    </div>
                    @if(session('success'))
                        <div class="alert alert-success mt-3">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger mt-3">{{ session('error') }}</div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Role Name</th>
                                    <th>Permissions</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td>{{ $roles->firstItem() + $loop->index }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>
                                            <button type="button"
                                                    class="badge badge-info border-0"
                                                    data-toggle="popover"
                                                    data-trigger="click"
                                                    data-toggle-tooltip="tooltip"
                                                    data-placement="right"
                                                    data-html="true"
                                                    title="Permissions"
                                                    data-content="@foreach($role->permissions as $permission)
                                                                        {{ $permission->name }}<br>
                                                                    @endforeach">
                                                {{ $role->permissions_count }} permissions
                                            </button>
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
                                                    @can('roles-edit')
                                                        <a href="{{ route('roles.edit', $role->id) }}"
                                                        class="dropdown-item">
                                                            <i class="ti-pencil mr-2"></i> Edit
                                                        </a>
                                                    @endcan
                                                    @can('roles-delete')
                                                        @if($role->name !== 'Super Admin')
                                                            <form action="{{ route('roles.destroy', $role->id) }}"
                                                                method="POST"
                                                                onsubmit="return confirm('Are you sure you want to delete this role?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="ti-trash mr-2"></i> Delete
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            No roles found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $roles->withQueryString()->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
@push('scripts')
<script>
    $(function () {
        $('[data-toggle="popover"]').popover();
        $('[data-toggle="popover"]').tooltip({
            placement: 'bottom',
            trigger: 'hover'
        });
    });
</script>
@endpush
@endsection