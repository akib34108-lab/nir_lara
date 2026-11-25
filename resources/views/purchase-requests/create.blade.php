@extends('layouts.master')
@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Create Purchase Request</h4>
                        <hr>
                        <form method="POST" action="{{ route('purchase-requests.store') }}">@csrf
                            <div class="form-group row">
                                <div class="col-md-6">
                                    <label>Project</label>
                                    <select name="project_id" class="form-control" required>
                                        <option value="">Select Project</option>
                                        @foreach($projects as $project)
                                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>{{ $project->project_name }} ({{ $project->project_code }})</option>
                                        @endforeach
                                    </select>
                                    @error('project_id')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label>Purpose</label>
                                    <input type="text" name="purpose" class="form-control" value="{{ old('purpose') }}" required>
                                    @error('purpose')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                            <hr>
                            <h5>Requested Materials</h5>
                            <div id="request-items">
                                @php
                                    $oldItems = old('items', [['material_id'=>'','requested_quantity'=>'','required_date'=>'']]);
                                @endphp
                                @foreach($oldItems as $index => $item)
                                <div class="request-item form-group row">
                                    <div class="col-md-5">
                                        <select name="items[{{ $index }}][material_id]" class="form-control" required>
                                            <option value="">Select Material</option>
                                            @foreach($materials as $material)
                                            <option value="{{ $material->id }}" {{ ($item['material_id'] ?? '') == $material->id ? 'selected' : '' }}>{{ $material->material_code }} - {{ $material->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="items[{{ $index }}][requested_quantity]" class="form-control" value="{{ $item['requested_quantity'] ?? '' }}" min="0.001" step="0.001" placeholder="Quantity" required>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" name="items[{{ $index }}][required_date]" class="form-control" value="{{ old('items.'.$index.'.required_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" required>
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger remove-item" title="Remove">&times;</button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @error('items')
                            <small class="text-danger d-block mb-2">{{ $message }}</small>
                            @enderror
                            <button type="button" id="add-item" class="btn btn-outline-primary mb-3"><i class="ti-plus"></i> Add Material</button><br>
                            <button type="submit" class="btn btn-primary"><i class="ti-save"></i> Save Request</button><a href="{{ route('purchase-requests.index') }}" class="btn btn-light">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const date = new Date();
                date.setMinutes(date.getMinutes() - date.getTimezoneOffset());
            const today = date.toISOString().split('T')[0];
            const container = document.getElementById('request-items');
            const addButton = document.getElementById('add-item');
            const materials = @json($materials->map(fn($material) => [
                'id' => $material->id,
                'label' => $material->material_code . ' - ' . $material->name
            ])->values());
            let nextIndex = Math.max(
                ...Array.from(container.querySelectorAll('.request-item select'))
                    .map(select => Number(select.name.match(/\d+/)?.[0] || 0)),
                -1
            ) + 1;
            addButton.addEventListener('click', function () {
                const options = materials.map(material => `
                    <option value="${material.id}">
                        ${material.label}
                    </option>
                `).join('');
                const row = `
                    <div class="request-item form-group row">

                        <div class="col-md-5">
                            <select name="items[${nextIndex}][material_id]"
                                    class="form-control" required>
                                <option value="">Select Material</option>
                                ${options}
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number"
                                name="items[${nextIndex}][requested_quantity]"
                                class="form-control"
                                min="0.001"
                                step="0.001"
                                placeholder="Quantity"
                                required>
                        </div>
                        <div class="col-md-3">
                            <input type="date"
                                name="items[${nextIndex}][required_date]"
                                class="form-control"
                                 value="${today}"
                                required>
                        </div>
                        <div class="col-md-1">
                            <button type="button"
                                    class="btn btn-danger remove-item">
                                &times;
                            </button>
                        </div>

                    </div>
                `;
                container.insertAdjacentHTML('beforeend', row);
                nextIndex++;
            });
            container.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.remove-item');
                if (!removeButton) return;
                const rows = container.querySelectorAll('.request-item');
                if (rows.length > 1) {
                    removeButton.closest('.request-item').remove();
                }
            });
        });
    </script>
    @endpush
@endsection
