@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-md-12 grid-margin stretch-card"><div class="card"><div class="card-body">
<div class="d-flex justify-content-between"><h4 class="card-title">Material Details</h4>@can('materials-edit')<a href="{{ route('materials.edit', $material) }}" class="btn btn-primary">Edit</a>@endcan</div><hr>
<dl class="row"><dt class="col-sm-3">Code</dt><dd class="col-sm-9">{{ $material->material_code }}</dd><dt class="col-sm-3">Name</dt><dd class="col-sm-9">{{ $material->name }}</dd><dt class="col-sm-3">Category</dt><dd class="col-sm-9">{{ $material->category->name }}</dd><dt class="col-sm-3">Unit</dt><dd class="col-sm-9">{{ $material->unit }}</dd><dt class="col-sm-3">Minimum Stock</dt><dd class="col-sm-9">{{ number_format($material->minimum_stock_level, 3) }}</dd><dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ $material->status == 1 ? 'Active' : 'Inactive' }}</dd><dt class="col-sm-3">Description</dt><dd class="col-sm-9">{{ $material->description ?: '-' }}</dd></dl>
<a href="{{ route('materials.index') }}" class="btn btn-light">Back</a>
</div></div></div></div></div>
@endsection
