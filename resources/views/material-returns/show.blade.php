@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-lg-12 grid-margin stretch-card"><div class="card"><div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<h4 class="card-title">{{ $material_return->return_number }}</h4><hr>
<dl class="row"><dt class="col-sm-3">Return Type</dt><dd class="col-sm-9">{{ $material_return->return_type }}</dd><dt class="col-sm-3">Project</dt><dd class="col-sm-9">{{ $material_return->project?->project_name ?: '-' }}</dd><dt class="col-sm-3">Warehouse</dt><dd class="col-sm-9">{{ $material_return->warehouse->name }}</dd><dt class="col-sm-3">Supplier</dt><dd class="col-sm-9">{{ $material_return->supplier?->company_name ?: '-' }}</dd><dt class="col-sm-3">Responsible User</dt><dd class="col-sm-9">{{ $material_return->responsibleUser->name }}</dd><dt class="col-sm-3">Status</dt><dd class="col-sm-9"><span class="badge badge-secondary">{{ $material_return->status }}</span></dd><dt class="col-sm-3">Reason</dt><dd class="col-sm-9">{{ $material_return->reason ?: '-' }}</dd></dl>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Material</th><th>Returned</th><th>Accepted</th><th>Rejected</th></tr></thead><tbody>@foreach($material_return->items as $item)<tr><td>{{ $item->material->material_code }} - {{ $item->material->name }}</td><td>{{ number_format($item->quantity,3) }}</td><td>{{ number_format($item->accepted_quantity,3) }}</td><td>{{ number_format($item->rejected_quantity,3) }}</td></tr>@endforeach</tbody></table></div>
@if(in_array($material_return->status,['Draft','Approved']))
@can('material-return-approve')
<form method="POST" action="{{ route('material-returns.process',$material_return) }}" onsubmit="return confirm('Process this material return?')">@csrf
@if($material_return->return_type==='Warehouse')@foreach($material_return->items as $item)<div class="form-group row"><label class="col-md-5 col-form-label">{{ $item->material->name }} (Returned: {{ number_format($item->quantity,3) }})</label><div class="col-md-3"><input type="number" name="accepted_quantity[{{ $item->id }}]" class="form-control" value="{{ $item->quantity }}" min="0" max="{{ $item->quantity }}" step="0.001" required></div><div class="col-md-3"><input type="number" name="rejected_quantity[{{ $item->id }}]" class="form-control" value="0" min="0" max="{{ $item->quantity }}" step="0.001" required></div></div>@endforeach<button class="btn btn-success">Inspect & Add Accepted Stock</button>
@else<button class="btn btn-danger">Dispatch to Supplier</button>@endif
</form>@endcan
@endif
<a href="{{ route('material-returns.index') }}" class="btn btn-light mt-3">Back</a>
</div></div></div></div></div>
@endsection
