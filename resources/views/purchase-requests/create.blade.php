@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-lg-12 grid-margin stretch-card"><div class="card"><div class="card-body"><h4 class="card-title">Create Purchase Request</h4><hr>
<form method="POST" action="{{ route('purchase-requests.store') }}">@csrf
<div class="form-group row"><div class="col-md-6"><label>Project</label><select name="project_id" class="form-control" required><option value="">Select Project</option>@foreach($projects as $project)<option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->project_name }} ({{ $project->project_code }})</option>@endforeach</select>@error('project_id')<small class="text-danger">{{ $message }}</small>@enderror</div><div class="col-md-6"><label>Purpose</label><input type="text" name="purpose" class="form-control" value="{{ old('purpose') }}" required>@error('purpose')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
<div class="form-group"><label>Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea></div>
<hr><h5>Requested Materials</h5>
<div id="request-items">
@php $oldItems = old('items', [['material_id'=>'','requested_quantity'=>'','required_date'=>'']]); @endphp
@foreach($oldItems as $index => $item)<div class="request-item form-group row">
<div class="col-md-5"><select name="items[{{ $index }}][material_id]" class="form-control" required><option value="">Select Material</option>@foreach($materials as $material)<option value="{{ $material->id }}" {{ ($item['material_id'] ?? '') == $material->id ? 'selected' : '' }}>{{ $material->material_code }} - {{ $material->name }}</option>@endforeach</select></div>
<div class="col-md-3"><input type="number" name="items[{{ $index }}][requested_quantity]" class="form-control" value="{{ $item['requested_quantity'] ?? '' }}" min="0.001" step="0.001" placeholder="Quantity" required></div>
<div class="col-md-3"><input type="date" name="items[{{ $index }}][required_date]" class="form-control" value="{{ $item['required_date'] ?? '' }}" required></div>
<div class="col-md-1"><button type="button" class="btn btn-danger remove-item" title="Remove">&times;</button></div>
</div>@endforeach
</div>
@error('items')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
<button type="button" id="add-item" class="btn btn-outline-primary mb-3"><i class="ti-plus"></i> Add Material</button><br>
<button type="submit" class="btn btn-primary"><i class="ti-save"></i> Save Request</button><a href="{{ route('purchase-requests.index') }}" class="btn btn-light">Cancel</a>
</form>
</div></div></div></div></div>
@push('scripts')
<script>
const materials = @json($materials->map(fn($material) => ['id'=>$material->id,'label'=>$material->material_code.' - '.$material->name])->values());
document.getElementById('add-item').addEventListener('click', function () {
    const container = document.getElementById('request-items');
    const index = container.querySelectorAll('.request-item').length;
    const options = materials.map(m => `<option value="${m.id}">${m.label}</option>`).join('');
    container.insertAdjacentHTML('beforeend', `<div class="request-item form-group row"><div class="col-md-5"><select name="items[${index}][material_id]" class="form-control" required><option value="">Select Material</option>${options}</select></div><div class="col-md-3"><input type="number" name="items[${index}][requested_quantity]" class="form-control" min="0.001" step="0.001" placeholder="Quantity" required></div><div class="col-md-3"><input type="date" name="items[${index}][required_date]" class="form-control" required></div><div class="col-md-1"><button type="button" class="btn btn-danger remove-item">&times;</button></div></div>`);
});
document.getElementById('request-items').addEventListener('click', function (event) { if (event.target.classList.contains('remove-item') && document.querySelectorAll('.request-item').length > 1) event.target.closest('.request-item').remove(); });
</script>
@endpush
@endsection
