@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-md-12 grid-margin stretch-card"><div class="card"><div class="card-body">
<h4 class="card-title">Create Material</h4><hr>
<form method="POST" action="{{ route('materials.store') }}">@csrf
<div class="form-group row">
<div class="col-md-4"><label>Material Code</label><input type="text" name="material_code" class="form-control" value="{{ old('material_code', $nextMaterialCode) }}" readonly required>@error('material_code')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="col-md-4"><label>Material Name</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required>@error('name')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="col-md-4"><label>Category</label><select name="category_id" class="form-control" required><option value="">Select Category</option>@foreach($categories as $category)<option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select>@error('category_id')<small class="text-danger">{{ $message }}</small>@enderror</div>
</div>
<div class="form-group row">
<div class="col-md-4"><label>Unit</label><input type="text" name="unit" class="form-control" value="{{ old('unit') }}" placeholder="e.g. pcs, bag, kg" required>@error('unit')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="col-md-4"><label>Minimum Stock Level</label><input type="number" name="minimum_stock_level" class="form-control" value="{{ old('minimum_stock_level', '0') }}" min="0" step="0.001" required>@error('minimum_stock_level')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="col-md-4"><label>Status</label><select name="status" class="form-control"><option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option><option value="2" {{ old('status') == 2 ? 'selected' : '' }}>Inactive</option></select>@error('status')<small class="text-danger">{{ $message }}</small>@enderror</div>
</div>
<div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>@error('description')<small class="text-danger">{{ $message }}</small>@enderror</div>
<button type="submit" class="btn btn-primary"><i class="ti-save"></i> Save Material</button><a href="{{ route('materials.index') }}" class="btn btn-light">Cancel</a>
</form>
</div></div></div></div></div>
@endsection
