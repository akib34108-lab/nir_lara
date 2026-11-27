@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">Users List</h4>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Email Verified</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $users->firstItem() + $loop->index }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @forelse($user->roles as $role)
                                                <span class="badge badge-info">{{ $role->name }}</span>
                                            @empty
                                                <span class="badge badge-secondary">No Role Assigned</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @if($user->hasVerifiedEmail())
                                                <span class="badge badge-success">Verified</span>
                                            @else
                                                <span class="badge badge-danger">Not Verified Yet</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm" type="button"
                                                        data-toggle="dropdown"
                                                        aria-haspopup="true" aria-expanded="false">
                                                    <i class="ti-more-alt"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @can('users-edit')
                                                        <button type="button"
                                                                class="dropdown-item assign-role-btn"
                                                                data-toggle="modal"
                                                                data-target="#assignRoleModal"
                                                                data-name="{{ $user->name }}"
                                                                data-role="{{ $user->roles->first()?->name }}"
                                                                data-url="{{ route('users-list.assignRole', $user->id) }}">
                                                            <i class="ti-key mr-2"></i> Assign Role
                                                        </button>
                                                    @endcan
                                                    @can('users-delete')
                                                        @if($user->id !== auth()->id())
                                                            <form action="{{ route('users-list.destroy', $user->id) }}"
                                                                  method="POST"
                                                                  onsubmit="return confirm('Are you sure you want to delete ' + '{{ $user->name }}' + '?')">
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
                                        <td colspan="6" class="text-center">No users found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $users->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Assign Role Modal -->
<div class="modal fade" id="assignRoleModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Role</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <form id="assignRoleForm" action="" method="POST">
                @csrf
                @method('PUT')

                <div class="modal-body">
                    <div class="form-group">
                        <label>User</label>
                        <input type="text" id="assignUserName" class="form-control" readonly>
                    </div>

                    <div class="form-group">
                        <label for="assignRoleSelect">Role <span class="text-danger">*</span></label>
                        <select name="role" id="assignRoleSelect" class="form-control" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti-check"></i> Save Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function () {
    $('.assign-role-btn').on('click', function () {
        $('#assignRoleForm').attr('action', $(this).data('url'));
        $('#assignUserName').val($(this).data('name'));
        $('#assignRoleSelect').val($(this).data('role') || '');
    });
});
</script>
@endpush
@include('layouts.partials.footer')