@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-lg-12 grid-margin stretch-card"><div class="card"><div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="d-flex justify-content-between align-items-center mb-3"><h4 class="card-title mb-0">Material Inspections</h4>@can('inspection-create')<a href="{{ route('inspections.create') }}" class="btn btn-primary"><i class="ti-plus"></i> New Inspection</a>@endcan</div>
<form method="GET" class="form-row mb-3"><div class="col-md-4 mb-2"><select name="status" class="form-control"><option value="">All Statuses</option>@foreach(['Accepted','Rejected','Partially Accepted'] as $status)<option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>@endforeach</select></div><div class="col-md-4 mb-2"><button class="btn btn-outline-primary mr-2">Filter</button><a href="{{ route('inspections.index') }}" class="btn btn-light">Reset</a></div></form>
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>GRN</th><th>Material</th><th>Inspected</th><th>Accepted</th><th>Rejected</th><th>Status</th><th>Date</th></tr></thead><tbody>
@forelse($inspections as $inspection)<tr><td>{{ $inspection->grn->grn_number }}</td><td>{{ $inspection->grnItem->material->name }}</td><td>{{ number_format($inspection->inspected_quantity,3) }}</td><td>{{ number_format($inspection->accepted_quantity,3) }}</td><td>{{ number_format($inspection->rejected_quantity,3) }}</td><td>{{ $inspection->quality_status }}</td><td>{{ $inspection->inspection_date->format('Y-m-d') }}</td></tr>
@empty<tr><td colspan="7" class="text-center">No inspections found.</td></tr>@endforelse
</tbody></table></div><div class="mt-3 d-flex justify-content-center">{{ $inspections->withQueryString()->links() }}</div>
</div></div></div></div></div>
@endsection
