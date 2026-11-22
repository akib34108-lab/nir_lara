@extends('layouts.master')
@section('content')
<div class="content-wrapper">
<div class="row"><div class="col-md-4 grid-margin"><div class="card"><div class="card-body"><p class="text-muted">Low Stock Items</p><h3>{{ $lowStockCount }}</h3><a href="{{ route('inventory.low-stock') }}">View low stock</a></div></div></div><div class="col-md-4 grid-margin"><div class="card"><div class="card-body"><p class="text-muted">Estimated Stock Value</p><h3>{{ number_format($totalStockValue,2) }}</h3></div></div></div><div class="col-md-4 grid-margin"><div class="card"><div class="card-body"><p class="text-muted">Stock Movements</p><h3>{{ \App\Models\StockTransaction::count() }}</h3><a href="{{ route('inventory.movements') }}">View history</a></div></div></div></div>
<div class="row"><div class="col-lg-12 grid-margin stretch-card"><div class="card"><div class="card-body">
<h4 class="card-title">Material Inventory</h4>
<form method="GET" class="form-row mb-3">
<div class="col-md-3 mb-2"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search material"></div>
<div class="col-md-2 mb-2"><select name="warehouse" class="form-control"><option value="">All Warehouses</option>@foreach($warehouseOptions as $warehouse)<option value="{{ $warehouse->id }}" {{ request('warehouse') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>@endforeach</select></div>
<div class="col-md-3 mb-2"><select name="material" class="form-control"><option value="">All Materials</option>@foreach($materialOptions as $material)<option value="{{ $material->id }}" {{ request('material') == $material->id ? 'selected' : '' }}>{{ $material->name }}</option>@endforeach</select></div>
<div class="col-md-2 mb-2"><select name="category" class="form-control"><option value="">All Categories</option>@foreach($categoryOptions as $category)<option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select></div>
<div class="col-md-2 mb-2"><button class="btn btn-outline-primary mr-1">Filter</button><a href="{{ route('inventory.index') }}" class="btn btn-light">Reset</a></div>
</form>
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Material</th><th>Warehouse</th><th>Available</th><th>Reserved</th><th>Minimum</th><th>Stock Value</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($inventories as $inventory)
@php $available=(float)$inventory->quantity-(float)$inventory->reserved_quantity; $low=$available <= (float)$inventory->material->minimum_stock_level; @endphp
<tr><td>{{ $inventory->material->material_code }} - {{ $inventory->material->name }}</td><td>{{ $inventory->warehouse->name }}</td><td>{{ number_format($available,3) }}</td><td>{{ number_format($inventory->reserved_quantity,3) }}</td><td>{{ number_format($inventory->material->minimum_stock_level,3) }}</td><td>{{ number_format($available*(float)$inventory->average_cost,2) }}</td><td><span class="badge badge-{{ $low?'danger':'success' }}">{{ $low?'Low Stock':'Available' }}</span></td><td><a href="{{ route('inventory.show',$inventory) }}" class="btn btn-light btn-sm"><i class="ti-eye"></i></a></td></tr>
@empty<tr><td colspan="8" class="text-center">No inventory records found.</td></tr>@endforelse
</tbody></table></div>
<div class="mt-3 d-flex justify-content-center">{{ $inventories->withQueryString()->links() }}</div>
</div></div></div></div>
</div>
@endsection
