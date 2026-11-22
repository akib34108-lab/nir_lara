@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-lg-12 grid-margin stretch-card"><div class="card"><div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="card-title mb-0">Stock Transfers</h4>@can('stock-transfer-create')<a href="{{ route('stock-transfers.create') }}" class="btn btn-primary"><i class="ti-plus"></i> New Transfer</a>@endcan</div>
<form method="GET" class="form-row mb-3"><div class="col-md-5 mb-2"><input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search transfer number"></div><div class="col-md-3 mb-2"><select name="status" class="form-control"><option value="">All Statuses</option>@foreach(['Pending','In Transit','Received','Cancelled'] as $status)<option value="{{ $status }}" {{ request('status')==$status?'selected':'' }}>{{ $status }}</option>@endforeach</select></div><div class="col-md-4 mb-2"><button class="btn btn-outline-primary mr-2">Filter</button><a href="{{ route('stock-transfers.index') }}" class="btn btn-light">Reset</a></div></form>
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Transfer</th><th>Source</th><th>Destination</th><th>Date</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($transfers as $transfer)<tr><td>{{ $transfer->transfer_number }}</td><td>{{ $transfer->sourceWarehouse->name }}</td><td>{{ $transfer->destinationWarehouse->name }}</td><td>{{ $transfer->transfer_date->format('Y-m-d') }}</td><td><span class="badge badge-secondary">{{ $transfer->status }}</span></td><td><a href="{{ route('stock-transfers.show',$transfer) }}" class="btn btn-light btn-sm"><i class="ti-eye"></i></a></td></tr>@empty<tr><td colspan="6" class="text-center">No stock transfers found.</td></tr>@endforelse
</tbody></table></div><div class="mt-3 d-flex justify-content-center">{{ $transfers->withQueryString()->links() }}</div>
</div></div></div></div></div>
@endsection
