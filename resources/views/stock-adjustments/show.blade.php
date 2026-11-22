@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-lg-12 grid-margin stretch-card"><div class="card"><div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<h4 class="card-title">{{ $stock_adjustment->adjustment_number }}</h4><hr>
<dl class="row"><dt class="col-sm-3">Warehouse</dt><dd class="col-sm-9">{{ $stock_adjustment->warehouse->name }}</dd><dt class="col-sm-3">Material</dt><dd class="col-sm-9">{{ $stock_adjustment->material->material_code }} - {{ $stock_adjustment->material->name }}</dd><dt class="col-sm-3">Previous Quantity</dt><dd class="col-sm-9">{{ number_format($stock_adjustment->previous_quantity,3) }}</dd><dt class="col-sm-3">Adjusted Quantity</dt><dd class="col-sm-9">{{ number_format($stock_adjustment->adjusted_quantity,3) }}</dd><dt class="col-sm-3">Difference</dt><dd class="col-sm-9">{{ number_format($stock_adjustment->difference,3) }}</dd><dt class="col-sm-3">Reason</dt><dd class="col-sm-9">{{ $stock_adjustment->reason }}</dd><dt class="col-sm-3">Remarks</dt><dd class="col-sm-9">{{ $stock_adjustment->remarks ?: '-' }}</dd><dt class="col-sm-3">Created By</dt><dd class="col-sm-9">{{ $stock_adjustment->creator->name }}</dd><dt class="col-sm-3">Approved By</dt><dd class="col-sm-9">{{ $stock_adjustment->approver?->name ?: '-' }}</dd><dt class="col-sm-3">Status</dt><dd class="col-sm-9"><span class="badge badge-secondary">{{ $stock_adjustment->status }}</span></dd></dl>
@if($stock_adjustment->status==='Pending')
@can('stock-adjustment-approve')
@if(auth()->id() !== $stock_adjustment->created_by)
<form method="POST" action="{{ route('stock-adjustments.approve',$stock_adjustment) }}" class="d-inline" onsubmit="return confirm('Approve this adjustment and update inventory?')">@csrf<button class="btn btn-success">Approve</button></form>
<form method="POST" action="{{ route('stock-adjustments.reject',$stock_adjustment) }}" class="d-inline">@csrf<input type="hidden" name="remarks" value="{{ $stock_adjustment->remarks }}"><button class="btn btn-danger">Reject</button></form>
@else<div class="alert alert-info">The creator cannot approve or reject this adjustment.</div>
@endif
@endcan
@endif
<a href="{{ route('stock-adjustments.index') }}" class="btn btn-light mt-3">Back</a>
</div></div></div></div></div>
@endsection
