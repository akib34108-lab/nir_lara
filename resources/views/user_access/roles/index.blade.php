@extends('layouts.master')
@section('content')
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
                                    Add Role
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
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($roles as $role)
                                    <tr>
                                        <td>{{ $roles->firstItem() + $loop->index }}</td>
                                        <td>{{ $role->name }}</td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $role->permissions_count }} permissions
                                            </span>
                                        </td>

                                        <td>
                                            @can('roles-list')
                                                <a href="{{ route('roles.show', $role->id) }}"
                                                   class="btn btn-info btn-sm">
                                                    View
                                                </a>
                                            @endcan

                                            @can('roles-edit')
                                                <a href="{{ route('roles.edit', $role->id) }}"
                                                   class="btn btn-warning btn-sm">
                                                    Edit
                                                </a>
                                            @endcan

                                            @can('roles-delete')
                                                @if($role->name !== 'Super Admin')
                                                    <form action="{{ route('roles.destroy', $role->id) }}"
                                                          method="POST"
                                                          style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                                class="btn btn-danger btn-sm"
                                                                onclick="return confirm('Are you sure you want to delete this role?')">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
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

                </div>   {{-- card-body --}}
            </div>       {{-- card --}}
        </div>           {{-- col-lg-12 --}}
    </div>               {{-- row --}}
@endsection