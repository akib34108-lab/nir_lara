@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-md-12 grid-margin stretch-card"><div class="card"><div class="card-body"><h4 class="card-title">Edit Material Category</h4><hr>
<form method="POST" action="{{ route('material-categories.update', $material_category) }}">@csrf @method('PUT')
<div class="form-group"><label>Category Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $material_category->name) }}" required>@error('name')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $material_category->description) }}</textarea></div>
<div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="1" {{ old('status', $material_category->status) == 1 ? 'selected' : '' }}>Active</option><option value="2" {{ old('status', $material_category->status) == 2 ? 'selected' : '' }}>Inactive</option></select></div>
<button class="btn btn-primary"><i class="ti-save"></i> Update Category</button><a href="{{ route('material-categories.index') }}" class="btn btn-light">Cancel</a></form>
</div></div></div></div></div>
@endsection
