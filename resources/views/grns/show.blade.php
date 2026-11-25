@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-lg-12 grid-margin stretch-card"><div class="card"><div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<h4 class="card-title">{{ $grn->grn_number }}</h4><hr>
<dl class="row"><dt class="col-sm-3">Purchase Order</dt><dd class="col-sm-9">{{ $grn->purchaseOrder?->po_number ?? 'Purchase order unavailable' }}</dd><dt class="col-sm-3">Warehouse</dt><dd class="col-sm-9">{{ $grn->warehouse->name }}</dd><dt class="col-sm-3">Received By</dt><dd class="col-sm-9">{{ $grn->receiver->name }}</dd><dt class="col-sm-3">Received Date</dt><dd class="col-sm-9">{{ $grn->received_date->format('Y-m-d') }}</dd><dt class="col-sm-3">Status</dt><dd class="col-sm-9"><span class="badge badge-secondary">{{ $grn->status }}</span></dd><dt class="col-sm-3">Notes</dt><dd class="col-sm-9">{{ $grn->notes ?: '-' }}</dd></dl>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Material</th><th>Ordered</th><th>Received</th><th>Accepted</th><th>Rejected</th></tr></thead><tbody>@foreach($grn->items as $item)<tr><td>{{ $item->material->material_code }} - {{ $item->material->name }}</td><td>{{ number_format($item->ordered_quantity,3) }}</td><td>{{ number_format($item->received_quantity,3) }}</td><td>{{ number_format($item->accepted_quantity,3) }}</td><td>{{ number_format($item->rejected_quantity,3) }}</td></tr>@endforeach</tbody></table></div>
@if($grn->status === 'Draft')
    @can('inspection-create')
        <a href="{{ route('inspections.create',['grn'=>$grn->id]) }}" class="btn btn-primary">Inspect Materials</a>
    @endcan
@endif
@if($grn->status === 'Inspected')
    <div class="alert alert-info mt-3">
        Inspection is complete. Confirm this GRN to post accepted material quantities to inventory.
    </div>
    @can('grn-confirm')
        <form method="POST" action="{{ route('grns.confirm',$grn) }}" onsubmit="return confirm('Confirm this GRN and post accepted quantities?')">
            @csrf
            <button class="btn btn-success">Confirm GRN</button>
        </form>
    @endcan
@endif
<a href="{{ route('grns.index') }}" class="btn btn-light">Back</a>
</div></div></div></div></div>
@endsection
