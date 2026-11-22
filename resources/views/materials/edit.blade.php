@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-md-12 grid-margin stretch-card"><div class="card"><div class="card-body">
<h4 class="card-title">Edit Material</h4><hr>
<form method="POST" action="{{ route('materials.update', $material) }}">@csrf @method('PUT')
<div class="form-group row">
<div class="col-md-4"><label>Material Code</label><input type="text" name="material_code" class="form-control" value="{{ old('material_code', $material->material_code) }}" readonly required>@error('material_code')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="col-md-4"><label>Material Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $material->name) }}" required>@error('name')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="col-md-4"><label>Category</label><select name="category_id" class="form-control" required><option value="">Select Category</option>@foreach($categories as $category)<option value="{{ $category->id }}" {{ old('category_id', $material->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select>@error('category_id')<small class="text-danger">{{ $message }}</small>@enderror</div>
</div>
<div class="form-group row">
<div class="col-md-4"><label>Unit</label><input type="text" name="unit" class="form-control" value="{{ old('unit', $material->unit) }}" required>@error('unit')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="col-md-4"><label>Minimum Stock Level</label><input type="number" name="minimum_stock_level" class="form-control" value="{{ old('minimum_stock_level', $material->minimum_stock_level) }}" min="0" step="0.001" required>@error('minimum_stock_level')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="col-md-4"><label>Status</label><select name="status" class="form-control"><option value="1" {{ old('status', $material->status) == 1 ? 'selected' : '' }}>Active</option><option value="2" {{ old('status', $material->status) == 2 ? 'selected' : '' }}>Inactive</option></select>@error('status')<small class="text-danger">{{ $message }}</small>@enderror</div>
</div>
<div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $material->description) }}</textarea></div>
<button type="submit" class="btn btn-primary"><i class="ti-save"></i> Update Material</button><a href="{{ route('materials.index') }}" class="btn btn-light">Cancel</a>
</form>
</div></div></div></div></div>
@endsection
