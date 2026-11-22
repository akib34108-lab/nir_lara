@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-lg-12 grid-margin stretch-card"><div class="card"><div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="card-title mb-0">Purchase Orders</h4>@can('purchase-order-create')<a href="{{ route('purchase-orders.create') }}" class="btn btn-primary"><i class="ti-plus"></i> New Purchase Order</a>@endcan</div>
<form method="GET" class="form-row mb-3"><div class="col-md-4 mb-2"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search PO number"></div><div class="col-md-3 mb-2"><select name="supplier" class="form-control"><option value="">All Suppliers</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" {{ request('supplier') == $supplier->id ? 'selected' : '' }}>{{ $supplier->company_name }}</option>@endforeach</select></div><div class="col-md-3 mb-2"><select name="status" class="form-control"><option value="">All Statuses</option>@foreach(['Draft','Pending Approval','Approved','Partially Received','Completed','Cancelled'] as $status)<option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>@endforeach</select></div><div class="col-md-2 mb-2"><button class="btn btn-outline-primary mr-1">Filter</button><a href="{{ route('purchase-orders.index') }}" class="btn btn-light">Reset</a></div></form>
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>PO Number</th><th>Supplier</th><th>Request</th><th>Order Date</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($orders as $order)
<tr>
<td>{{ $order->po_number }}</td>
<td>{{ $order->supplier->company_name }}</td>
<td>{{ $order->purchaseRequest?->request_number ?: '-' }}</td>
<td>{{ $order->order_date->format('Y-m-d') }}</td>
<td>{{ number_format($order->grand_total,2) }}</td>
<td><span class="badge badge-secondary">{{ $order->status }}</span></td>
<td>
<div class="dropdown">
<button class="btn btn-light btn-sm" data-toggle="dropdown"><i class="ti-more-alt"></i></button>
<div class="dropdown-menu dropdown-menu-right">
<a class="dropdown-item" href="{{ route('purchase-orders.show',$order) }}"><i class="ti-eye mr-2"></i>View</a>
@can('purchase-order-edit')
@if(in_array($order->status,['Draft','Pending Approval']))
<a class="dropdown-item" href="{{ route('purchase-orders.edit',$order) }}"><i class="ti-pencil mr-2"></i>Edit</a>
@endif
@endcan
@can('purchase-order-delete')
@if($order->status === 'Draft')
<form method="POST" action="{{ route('purchase-orders.destroy',$order) }}" onsubmit="return confirm('Delete this purchase order?')">
@csrf
@method('DELETE')
<button class="dropdown-item text-danger"><i class="ti-trash mr-2"></i>Delete</button>
</form>
@endif
@endcan
</div>
</div>
</td>
</tr>
@empty<tr><td colspan="7" class="text-center">No purchase orders found.</td></tr>@endforelse
</tbody></table></div><div class="mt-3 d-flex justify-content-center">{{ $orders->withQueryString()->links() }}</div>
</div></div></div></div></div>
@endsection
