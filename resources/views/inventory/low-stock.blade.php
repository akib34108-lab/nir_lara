@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-lg-12 grid-margin stretch-card"><div class="card"><div class="card-body">
<h4 class="card-title">Low Stock Alerts</h4>
<form method="GET" class="form-row mb-3"><div class="col-md-4 mb-2"><select name="warehouse" class="form-control"><option value="">All Warehouses</option>@foreach($warehouseOptions as $warehouse)<option value="{{ $warehouse->id }}" {{ request('warehouse') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>@endforeach</select></div><div class="col-md-4 mb-2"><select name="material" class="form-control"><option value="">All Materials</option>@foreach($materialOptions as $material)<option value="{{ $material->id }}" {{ request('material') == $material->id ? 'selected' : '' }}>{{ $material->name }}</option>@endforeach</select></div><div class="col-md-4 mb-2"><button class="btn btn-outline-primary mr-2">Filter</button><a href="{{ route('inventory.low-stock') }}" class="btn btn-light">Reset</a></div></form>
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Material</th><th>Warehouse</th><th>Available</th><th>Reserved</th><th>Minimum Stock</th><th>Status</th></tr></thead><tbody>
@forelse($inventories as $inventory)
@php $available=(float)$inventory->quantity-(float)$inventory->reserved_quantity; @endphp
<tr><td>{{ $inventory->material->material_code }} - {{ $inventory->material->name }}</td><td>{{ $inventory->warehouse->name }}</td><td>{{ number_format($available,3) }}</td><td>{{ number_format($inventory->reserved_quantity,3) }}</td><td>{{ number_format($inventory->material->minimum_stock_level,3) }}</td><td><span class="badge badge-danger">Low Stock</span></td></tr>
@empty<tr><td colspan="6" class="text-center">No low-stock items found.</td></tr>@endforelse
</tbody></table></div>
<a href="{{ route('inventory.index') }}" class="btn btn-light">Back to Inventory</a>
</div></div></div></div></div>
@endsection
