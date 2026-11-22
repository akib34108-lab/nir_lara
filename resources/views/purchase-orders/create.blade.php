@extends('layouts.master')
@section('content')
<div class="content-wrapper"><div class="row"><div class="col-lg-12 grid-margin stretch-card"><div class="card"><div class="card-body"><h4 class="card-title">Create Purchase Order</h4><hr>
<form method="POST" action="{{ route('purchase-orders.store') }}">@csrf
<div class="form-group row"><div class="col-md-6"><label>Approved Purchase Request</label><select name="purchase_request_id" id="purchase-request-select" class="form-control" required><option value="">Select Request</option>@foreach($purchaseRequests as $purchaseRequest)<option value="{{ $purchaseRequest->id }}" {{ old('purchase_request_id',$selectedRequest?->id) == $purchaseRequest->id ? 'selected' : '' }}>{{ $purchaseRequest->request_number }} - {{ $purchaseRequest->project->project_name }}</option>@endforeach</select>@error('purchase_request_id')<small class="text-danger">{{ $message }}</small>@enderror</div><div class="col-md-6"><label>Supplier</label><select name="supplier_id" class="form-control" required><option value="">Select Supplier</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->company_name }}</option>@endforeach</select>@error('supplier_id')<small class="text-danger">{{ $message }}</small>@enderror</div></div>
<div class="form-group row"><div class="col-md-4"><label>Order Date</label><input type="date" name="order_date" class="form-control" value="{{ old('order_date',now()->format('Y-m-d')) }}" required></div><div class="col-md-4"><label>Expected Delivery Date</label><input type="date" name="expected_delivery_date" class="form-control" value="{{ old('expected_delivery_date') }}"></div><div class="col-md-4"><label>Payment Terms</label><input type="text" name="payment_terms" class="form-control" value="{{ old('payment_terms') }}" placeholder="e.g. Net 30"></div></div>
<div class="form-group"><label>Notes</label><textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea></div>
<hr><h5>Order Items</h5><div id="order-items">
@if($selectedRequest)
@foreach($selectedRequest->items as $index=>$item)
@if($item->approved_quantity > 0)<div class="form-group row"><input type="hidden" name="items[{{ $index }}][purchase_request_item_id]" value="{{ $item->id }}"><input type="hidden" name="items[{{ $index }}][material_id]" value="{{ $item->material_id }}"><div class="col-md-4"><label>{{ $item->material->name }}</label><input type="text" class="form-control" value="{{ $item->material->material_code }}" readonly></div><div class="col-md-3"><label>Ordered Quantity</label><input type="number" name="items[{{ $index }}][ordered_quantity]" class="form-control" value="{{ old('items.'.$index.'.ordered_quantity',$item->approved_quantity) }}" max="{{ $item->approved_quantity }}" min="0.001" step="0.001" required></div><div class="col-md-3"><label>Unit Price</label><input type="number" name="items[{{ $index }}][unit_price]" class="form-control" value="{{ old('items.'.$index.'.unit_price','0') }}" min="0" step="0.01" required></div></div>@endif
@endforeach
@else
<div class="alert alert-info">Select an approved purchase request to load its materials.</div>
@endif
</div>
<div class="form-group row"><div class="col-md-6"><label>Discount</label><input type="number" name="discount" class="form-control" value="{{ old('discount','0') }}" min="0" step="0.01" required></div><div class="col-md-6"><label>Tax</label><input type="number" name="tax" class="form-control" value="{{ old('tax','0') }}" min="0" step="0.01" required></div></div>
@error('items')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
<button type="submit" class="btn btn-primary"><i class="ti-save"></i> Save Purchase Order</button><a href="{{ route('purchase-orders.index') }}" class="btn btn-light">Cancel</a>
</form></div></div></div></div></div>
@push('scripts')<script>document.getElementById('purchase-request-select').addEventListener('change',function(){const base='{{ route('purchase-orders.create') }}';window.location.href=base+'?purchase_request='+this.value;});</script>@endpush
@endsection
