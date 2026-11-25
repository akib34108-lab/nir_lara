@extends('layouts.master')
@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Create Purchase Orders</h4>
                    <p class="text-muted">
                        Request {{ $purchase_request->request_number }}
                        @if($purchase_request->project)
                            — {{ $purchase_request->project->project_name }}
                        @endif
                    </p>
                    <hr>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(! $hasRemainingQuantities)
                        <div class="alert alert-info">
                            All approved quantities for this request have already been ordered.
                        </div>
                    @elseif($suppliers->isEmpty())
                        <div class="alert alert-warning">
                            There are no active suppliers available for purchase orders.
                        </div>
                    @endif

                    <form id="purchase-order-generation-form" method="POST"
                          action="{{ route('purchase-requests.purchase-orders.store', $purchase_request) }}">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="order-date">Order Date</label>
                                <input id="order-date" type="date" name="order_date" class="form-control"
                                       value="{{ old('order_date', now()->format('Y-m-d')) }}" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="expected-delivery-date">Expected Delivery Date</label>
                                <input id="expected-delivery-date" type="date" name="expected_delivery_date"
                                       class="form-control" value="{{ old('expected_delivery_date') }}">
                            </div>
                            <div class="form-group col-md-4">
                                <label for="payment-terms">Payment Terms</label>
                                <input id="payment-terms" type="text" name="payment_terms" class="form-control"
                                       value="{{ old('payment_terms') }}" placeholder="e.g. Net 30">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="purchase-order-notes">Notes (applied to each generated order)</label>
                            <textarea id="purchase-order-notes" name="notes" class="form-control"
                                      rows="2">{{ old('notes') }}</textarea>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Material</th>
                                        <th>Approved</th>
                                        <th>Already Ordered</th>
                                        <th>Remaining</th>
                                        <th>Quantity to Order</th>
                                        <th>Supplier</th>
                                        <th>Unit Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchase_request->items as $item)
                                        @php
                                            $approvedQuantity = (float) $item->approved_quantity;
                                            $alreadyOrdered = $orderedQuantities[$item->id] ?? 0;
                                            $remaining = $remainingQuantities[$item->id] ?? 0;
                                        @endphp
                                        @if($approvedQuantity > 0)
                                            <tr data-order-row
                                                data-material="{{ $item->material?->name ?? 'Unknown material' }}"
                                                data-unit="{{ $item->material?->unit ?? '' }}">
                                                <td>
                                                    {{ $item->material?->material_code ?? '-' }} -
                                                    {{ $item->material?->name ?? 'Unknown material' }}
                                                </td>
                                                <td>{{ number_format($approvedQuantity, 3) }} {{ $item->material?->unit }}</td>
                                                <td>{{ number_format($alreadyOrdered, 3) }} {{ $item->material?->unit }}</td>
                                                <td>{{ number_format($remaining, 3) }} {{ $item->material?->unit }}</td>
                                                <td>
                                                    <input type="number"
                                                           name="items[{{ $item->id }}][ordered_quantity]"
                                                           class="form-control order-quantity"
                                                           value="{{ old('items.'.$item->id.'.ordered_quantity', '0') }}"
                                                           min="0" max="{{ $remaining }}" step="0.001"
                                                           {{ $remaining <= 0.0001 ? 'disabled' : '' }}>
                                                </td>
                                                <td>
                                                    <select name="items[{{ $item->id }}][supplier_id]"
                                                            class="form-control order-supplier"
                                                            {{ $remaining <= 0.0001 || $suppliers->isEmpty() ? 'disabled' : '' }}>
                                                        <option value="">Select supplier</option>
                                                        @foreach($suppliers as $supplier)
                                                            <option value="{{ $supplier->id }}"
                                                                {{ old('items.'.$item->id.'.supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                                {{ $supplier->company_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number"
                                                           name="items[{{ $item->id }}][unit_price]"
                                                           class="form-control order-price"
                                                           value="{{ old('items.'.$item->id.'.unit_price') }}"
                                                           min="0" step="0.01" placeholder="0.00"
                                                           {{ $remaining <= 0.0001 ? 'disabled' : '' }}>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">This request has no items.</td>
                                        </tr>
                                    @endforelse
                                    @if($purchase_request->items->every(fn ($item) => (float) $item->approved_quantity <= 0))
                                        <tr>
                                            <td colspan="7" class="text-center">This request has no approved quantities.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="card bg-light mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Purchase Order Preview</h5>
                                <div id="order-preview" aria-live="polite">
                                    Select suppliers and quantities to preview the supplier-based orders.
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary"
                                {{ ! $hasRemainingQuantities || $suppliers->isEmpty() ? 'disabled' : '' }}>
                            <i class="ti-save"></i> Generate Purchase Orders
                        </button>
                        <a href="{{ route('purchase-requests.show', $purchase_request) }}" class="btn btn-light">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('purchase-order-generation-form');
    const preview = document.getElementById('order-preview');
    const rows = document.querySelectorAll('[data-order-row]');

    function renderPreview() {
        const groups = new Map();
        rows.forEach(function (row) {
            const supplier = row.querySelector('.order-supplier');
            const quantity = Number(row.querySelector('.order-quantity').value);
            const price = Number(row.querySelector('.order-price').value);
            if (!supplier.value || quantity <= 0) {
                return;
            }

            if (!groups.has(supplier.value)) {
                groups.set(supplier.value, {
                    name: supplier.options[supplier.selectedIndex].text,
                    lines: []
                });
            }
            groups.get(supplier.value).lines.push({
                material: row.dataset.material,
                quantity: quantity,
                unit: row.dataset.unit,
                price: price
            });
        });

        preview.textContent = '';
        if (groups.size === 0) {
            preview.textContent = 'Select suppliers and quantities to preview the supplier-based orders.';
            return 0;
        }

        const summary = document.createElement('p');
        summary.textContent = groups.size + ' Purchase Order(s) will be generated.';
        preview.appendChild(summary);

        groups.forEach(function (group) {
            const heading = document.createElement('h6');
            heading.textContent = group.name;
            preview.appendChild(heading);

            const list = document.createElement('ul');
            group.lines.forEach(function (line) {
                const entry = document.createElement('li');
                const subtotal = (line.quantity * line.price).toFixed(2);
                entry.textContent = line.material + ' — ' + line.quantity.toFixed(3) + ' ' + line.unit
                    + ' × ' + line.price.toFixed(2) + ' = ' + subtotal;
                list.appendChild(entry);
            });
            preview.appendChild(list);
        });

        return groups.size;
    }

    rows.forEach(function (row) {
        row.querySelectorAll('select, input').forEach(function (input) {
            input.addEventListener('input', renderPreview);
            input.addEventListener('change', renderPreview);
        });
    });

    form.addEventListener('submit', function (event) {
        const orderCount = renderPreview();
        if (orderCount === 0) {
            event.preventDefault();
            window.alert('Select at least one material, supplier, quantity, and unit price.');
        } else if (!window.confirm('Generate ' + orderCount + ' supplier-based purchase order(s)?')) {
            event.preventDefault();
        }
    });

    renderPreview();
});
</script>
@endpush
@endsection
