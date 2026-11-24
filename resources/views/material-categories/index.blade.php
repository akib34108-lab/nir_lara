@extends('layouts.master')
@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title mb-0">Material Categories List</h4>
                            <div class="d-flex align-items-center">
                                <form method="GET" id="materialFilterForm" class="d-flex align-items-center mb-0">
                                    <div class="mr-2">
                                        <input type="text"
                                            name="search"
                                            id="materialSearch"
                                            value="{{ request('search') }}"
                                            class="form-control"
                                            placeholder="Search Category"
                                            style="width: 280px;">
                                    </div>
                                </form>
                                @can('category-create')
                                    <a href="{{ route('material-categories.create') }}" class="btn btn-primary">
                                        <i class="ti-plus"></i> Add Category
                                    </a>
                                @endcan
                            </div>
                    </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Materials</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($categories as $category)
                                        <tr>
                                            <td>
                                                <div>
                                                    <p style="font-weight: bold">{{ $category->name }}</p>
                                                    <small>{{ $category->description ?: '-' }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $category->materials_count }}</td>
                                            <td>
                                                <span class="badge badge-{{ $category->status == 1 ? 'success' : 'secondary' }}">
                                                    {{ $category->status == 1 ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-light btn-sm" data-toggle="dropdown">
                                                        <i class="ti-more-alt"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        @can('category-edit')
                                                            <a
                                                                class="dropdown-item"
                                                                href="{{ route('material-categories.edit', $category) }}"
                                                            >
                                                                <i class="ti-pencil mr-2"></i>
                                                                Edit
                                                            </a>
                                                        @endcan
                                                        @can('category-delete')
                                                            <form
                                                                method="POST"
                                                                action="{{ route('material-categories.destroy', $category) }}"
                                                                onsubmit="return confirm('Delete this category?')"
                                                            >
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="dropdown-item text-danger">
                                                                    <i class="ti-trash mr-2"></i>
                                                                    Delete
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No categories found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $categories->withQueryString()->links() }}
                        </div>
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
