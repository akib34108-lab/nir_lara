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
                        <div class="d-flex align-items-center">
                            <form method="GET" id="materialFilterForm" class="d-flex align-items-center mb-0">
                                <div class="mr-2">
                                    <input type="text"
                                        name="search"
                                        id="materialSearch"
                                        value="{{ request('search') }}"
                                        class="form-control"
                                        placeholder="Search code or name"
                                        style="width: 280px;">
                                </div>
                                <div class="mr-2">
                                    <select name="category"
                                            class="form-control"
                                            style="width: 180px;"
                                            onchange="document.getElementById('materialFilterForm').submit()">
                                        <option value="">All Categories</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ request('category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </form>
                            @can('materials-create')
                                <a href="{{ route('materials.create') }}" class="btn btn-primary">
                                    <i class="ti-plus"></i> Add Material
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Minimum Stock</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($materials as $material)
                                <tr>
                                    <td>{{ $material->material_code }}</td>
                                    <td>
                                        <div>
                                            <p style="font-weight: bold">{{ $material->name }}</p>
                                            <small>{{ $material->description ?: '-' }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $material->category->name }}</td>
                                    <td>{{ number_format($material->minimum_stock_level, 3) }} {{ $material->unit }}</td>
                                    <td><span class="badge badge-{{ $material->status == 1 ? 'success' : 'secondary' }}">{{ $material->status == 1 ? 'Active' : 'Inactive' }}</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm" data-toggle="dropdown"><i class="ti-more-alt"></i></button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @can('materials-edit')
                                                <a class="dropdown-item" href="{{ route('materials.edit', $material) }}"><i class="ti-pencil mr-2"></i>Edit</a>
                                                @endcan
                                                @can('materials-delete')
                                                <form method="POST" action="{{ route('materials.destroy', $material) }}" onsubmit="return confirm('Delete this material?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger" type="submit"><i class="ti-trash mr-2"></i>Delete</button>
                                                </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
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
@push('scripts')
    <script>
        $(document).ready(function () {
            let searchTimer;
            $('#materialSearch').on('blur', function () {
            $('#materialFilterForm').submit();
            });
        });
    </script>
@endpush
@endsection
