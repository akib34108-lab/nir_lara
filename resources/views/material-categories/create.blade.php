@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-md-12 grid-margin stretch-card"><div class="card"><div class="card-body"><h4 class="card-title">Create Material Category</h4><hr>
<form method="POST" action="{{ route('material-categories.store') }}">@csrf
<div class="form-group"><label>Category Name</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required>@error('name')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>@error('description')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active</option><option value="2" {{ old('status') == 2 ? 'selected' : '' }}>Inactive</option></select>@error('status')<small class="text-danger">{{ $message }}</small>@enderror</div>
<button class="btn btn-primary"><i class="ti-save"></i> Save Category</button><a href="{{ route('material-categories.index') }}" class="btn btn-light">Cancel</a></form>
</div></div></div></div></div>
@endsection
