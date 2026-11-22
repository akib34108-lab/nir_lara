@extends('layouts.master')
@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Material List</h4>
                        @can('materials-create')
                        <a href="{{ route('materials.create') }}" class="btn btn-primary"><i class="ti-plus"></i> Add Material</a>
                        @endcan
                    </div>
                    <form method="GET" class="form-row mb-3">
                        <div class="col-md-4 mb-2"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search code or name"></div>
                        <div class="col-md-3 mb-2"><select name="category" class="form-control"><option value="">All Categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select></div>
                        <div class="col-md-2 mb-2"><select name="status" class="form-control"><option value="">All Status</option><option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option><option value="2" {{ request('status') === '2' ? 'selected' : '' }}>Inactive</option></select></div>
                        <div class="col-md-3 mb-2"><button class="btn btn-outline-primary mr-2" type="submit">Filter</button><a href="{{ route('materials.index') }}" class="btn btn-light">Reset</a></div>
                    </form>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Unit</th><th>Minimum Stock</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                            @forelse($materials as $material)
                                <tr>
                                    <td>{{ $material->material_code }}</td><td>{{ $material->name }}</td><td>{{ $material->category->name }}</td><td>{{ $material->unit }}</td><td>{{ number_format($material->minimum_stock_level, 3) }}</td><td><span class="badge badge-{{ $material->status == 1 ? 'success' : 'secondary' }}">{{ $material->status == 1 ? 'Active' : 'Inactive' }}</span></td>
                                    <td><div class="dropdown"><button class="btn btn-light btn-sm" data-toggle="dropdown"><i class="ti-more-alt"></i></button><div class="dropdown-menu dropdown-menu-right">
                                        @can('materials-view')<a class="dropdown-item" href="{{ route('materials.show', $material) }}"><i class="ti-eye mr-2"></i>View</a>@endcan
                                        @can('materials-edit')<a class="dropdown-item" href="{{ route('materials.edit', $material) }}"><i class="ti-pencil mr-2"></i>Edit</a>@endcan
                                        @can('materials-delete')<form method="POST" action="{{ route('materials.destroy', $material) }}" onsubmit="return confirm('Delete this material?')">@csrf @method('DELETE')<button class="dropdown-item text-danger" type="submit"><i class="ti-trash mr-2"></i>Delete</button></form>@endcan
                                    </div></div></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center">No materials found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-center">{{ $materials->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
