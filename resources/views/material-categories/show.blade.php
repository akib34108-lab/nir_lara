@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-md-12 grid-margin stretch-card"><div class="card"><div class="card-body"><div class="d-flex justify-content-between"><h4 class="card-title">Category Details</h4>@can('category-edit')<a href="{{ route('material-categories.edit', $material_category) }}" class="btn btn-primary">Edit</a>@endcan</div><hr>
<dl class="row"><dt class="col-sm-3">Name</dt><dd class="col-sm-9">{{ $material_category->name }}</dd><dt class="col-sm-3">Materials</dt><dd class="col-sm-9">{{ $material_category->materials_count }}</dd><dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ $material_category->status == 1 ? 'Active' : 'Inactive' }}</dd><dt class="col-sm-3">Description</dt><dd class="col-sm-9">{{ $material_category->description ?: '-' }}</dd></dl>
<a href="{{ route('material-categories.index') }}" class="btn btn-light">Back</a></div></div></div></div></div>
@endsection
