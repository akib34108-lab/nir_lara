@extends('layouts.master')
@section('content')
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Suppliers</h4>
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
                            </form>
                            @can('supplier-create')
                            <a href="{{ route('suppliers.create') }}" class="btn btn-primary"><i class="ti-plus"></i> Add Supplier</a>
                            @endcan
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Company</th>
                                    <th>Contact Person</th>
                                    <th>Tax</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($suppliers as $supplier)
                                <tr>
                                    <td>{{ $supplier->supplier_code }}</td>
                                    <td>{{ $supplier->company_name }} <span class="badge badge-{{ $supplier->status == 1 ? 'success' : 'secondary' }}">{{ $supplier->status == 1 ? 'Active' : 'Inactive' }}</span></td>
                                    <td>
                                        <div>
                                            <span  class="font-weight-medium">{{ $supplier->contact_person ?: '-' }}</span><br/>
                                            <small class="text-muted">{{ $supplier->phone }}</small><br/>
                                            <small class="text-primary">{{ $supplier->email }}</small><br/>
                                            <small class="text-muted">{{ $supplier->address }}, {{ $supplier->city }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $supplier->tax_number }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm" data-toggle="dropdown"><i class="ti-more-alt"></i></button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @can('supplier-edit')
                                                <a class="dropdown-item" href="{{ route('suppliers.edit', $supplier) }}"><i class="ti-pencil mr-2"></i>Edit</a>
                                                @endcan
                                                @can('supplier-delete')
                                                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete this supplier?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="dropdown-item text-danger"><i class="ti-trash mr-2"></i>Delete</button>
                                                </form>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No suppliers found.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-center">{{ $suppliers->withQueryString()->links() }}</div>
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
