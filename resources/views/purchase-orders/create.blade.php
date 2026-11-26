
@extends('layouts.master')

@section('content')

<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <h4 class="card-title">Create Purchase Orders</h4>
                    <p class="text-muted">
                        Choose an approved project request to load its approved materials.
                    </p>

                    {{-- Purchase Request Selection --}}
                    <form id="purchase-request-selection-form"
                          method="GET"
                          action="{{ route('purchase-orders.create') }}">

                        <div class="form-row align-items-end">

                            <div class="form-group col-md-8">
                                <label for="purchase-request-select">
                                    Approved Project / Purchase Request
                                </label>

                                <select id="purchase-request-select"
                                        name="purchase_request"
                                        class="form-control"
                                        required>

                                    <option value="">Select a project and request</option>

                                    @foreach($purchaseRequests as $purchaseRequest)
                                        <option value="{{ $purchaseRequest->id }}"
                                            {{ $selectedRequest?->id == $purchaseRequest->id ? 'selected' : '' }}>

                                            {{ $purchaseRequest->project?->project_name ?? 'Project unavailable' }}
                                            — {{ $purchaseRequest->request_number }}
                                            ({{ $purchaseRequest->status }})

                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <button type="submit" class="btn btn-outline-primary">
                                    Load Materials
                                </button>
                            </div>

                        </div>
                    </form>

                    @if($purchaseRequests->isEmpty())
                        <div class="alert alert-info">
                            There are no approved purchase requests available for ordering.
                        </div>
                    @endif

                    @if($selectedRequest)

                        <hr>

                        <p class="text-muted">
                            Request {{ $selectedRequest->request_number }}

                            @if($selectedRequest->project)
                                — {{ $selectedRequest->project->project_name }}
                            @endif
                        </p>

                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
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

                        @php
                            $approvedItems = $selectedRequest->items
                                ->filter(fn ($item) => (float) $item->approved_quantity > 0);
                        @endphp

                        {{-- Purchase Order Generation Form --}}
                        <form id="purchase-order-generation-form"
                              method="POST"
                              action="{{ route('purchase-requests.purchase-orders.store', $selectedRequest) }}">

                            @csrf

                            {{-- Order Information --}}
                            <h5 class="mb-3">Order Information</h5>

                            <div class="form-row">

                                <div class="form-group col-md-4">
                                    <label for="order-date">Order Date</label>

                                    <input id="order-date"
                                           type="date"
                                           name="order_date"
                                           class="form-control"
                                           value="{{ old('order_date', now()->format('Y-m-d')) }}"
                                           required>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="expected-delivery-date">
                                        Expected Delivery Date
                                    </label>

                                    <input id="expected-delivery-date"
                                           type="date"
                                           name="expected_delivery_date"
                                           class="form-control"
                                           value="{{ old('expected_delivery_date') }}">
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="payment-terms">Payment Terms</label>

                                    <input id="payment-terms"
                                           type="text"
                                           name="payment_terms"
                                           class="form-control"
                                           value="{{ old('payment_terms') }}"
                                           placeholder="e.g. Net 30">
                                </div>

                            </div>

                            <div class="form-group">
                                <label for="purchase-order-notes">
                                    Notes (applied to each generated order)
                                </label>

                                <textarea id="purchase-order-notes"
                                          name="notes"
                                          class="form-control"
                                          rows="2">{{ old('notes') }}</textarea>
                            </div>

                            <hr>

                            {{-- Approved Materials --}}
                            <h5 class="mb-3">Approved Materials</h5>

                            <p class="text-muted">
                                Click a material row to enter its order details.
                            </p>

                            <div class="table-responsive">

                                <table class="table table-bordered table-hover">

                                    <thead class="thead-light">
                                        <tr>
                                            <th>Material</th>
                                            <th>Approved Quantity</th>
                                            <th>Remaining</th>
                                            <th width="120">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @forelse($approvedItems as $item)

                                            @php
                                                $approvedQuantity = (float) $item->approved_quantity;
                                                $remaining = $remainingQuantities[$item->id] ?? 0;
                                                $collapseId = 'material-details-' . $item->id;
                                            @endphp

                                            {{-- Material Summary --}}
                                            <tr class="material-toggle-row"
                                                data-toggle="collapse"
                                                data-target="#{{ $collapseId }}"
                                                aria-expanded="false"
                                                aria-controls="{{ $collapseId }}"
                                                style="cursor: pointer;">

                                                <td>
                                                    <strong>
                                                        {{ $item->material?->material_code ?? '-' }}
                                                        -
                                                        {{ $item->material?->name ?? 'Unknown material' }}
                                                    </strong>
                                                </td>

                                                <td>
                                                    {{ number_format($approvedQuantity, 3) }}
                                                    {{ $item->material?->unit ?? '' }}
                                                </td>

                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ number_format($remaining, 3) }}
                                                        {{ $item->material?->unit ?? '' }}
                                                    </span>
                                                </td>

                                                <td>
                                                    @if($remaining > 0.0001 && !$suppliers->isEmpty())
                                                        <button type="button"
                                                                class="btn btn-sm btn-outline-primary">
                                                            <i class="ti-plus"></i> Add Order
                                                        </button>
                                                    @else
                                                        <span class="text-muted">Unavailable</span>
                                                    @endif
                                                </td>

                                            </tr>

                                            {{-- Expandable Material Inputs --}}
                                            <tr>
                                                <td colspan="4" class="p-0 border-0">

                                                    <div id="{{ $collapseId }}" class="collapse">

                                                        <div class="bg-light p-3 border-bottom">

                                                            <h6 class="mb-3">
                                                                Order Details:
                                                                {{ $item->material?->name ?? 'Unknown material' }}
                                                            </h6>

                                                            <div class="form-row"
                                                                 data-order-row
                                                                 data-material="{{ $item->material?->name ?? 'Unknown material' }}"
                                                                 data-unit="{{ $item->material?->unit ?? '' }}">

                                                                {{-- Order Quantity --}}
                                                                <div class="form-group col-md-4">
                                                                    <label>Order Quantity</label>

                                                                    <input type="number"
                                                                           name="items[{{ $item->id }}][ordered_quantity]"
                                                                           class="form-control order-quantity"
                                                                           value="{{ old('items.'.$item->id.'.ordered_quantity', '0') }}"
                                                                           min="0"
                                                                           max="{{ $remaining }}"
                                                                           step="0.001"
                                                                           {{ $remaining <= 0.0001 ? 'disabled' : '' }}>
                                                                </div>

                                                                {{-- Supplier --}}
                                                                <div class="form-group col-md-4">
                                                                    <label>Supplier</label>

                                                                    <select name="items[{{ $item->id }}][supplier_id]"
                                                                            class="form-control order-supplier"
                                                                            {{ $remaining <= 0.0001 || $suppliers->isEmpty() ? 'disabled' : '' }}>

                                                                        <option value="">
                                                                            Select Supplier
                                                                        </option>

                                                                        @foreach($suppliers as $supplier)
                                                                            <option value="{{ $supplier->id }}"
                                                                                {{ old('items.'.$item->id.'.supplier_id') == $supplier->id ? 'selected' : '' }}>

                                                                                {{ $supplier->company_name }}

                                                                            </option>
                                                                        @endforeach

                                                                    </select>
                                                                </div>

                                                                {{-- Unit Price --}}
                                                                <div class="form-group col-md-4">
                                                                    <label>Unit Price</label>

                                                                    <input type="number"
                                                                           name="items[{{ $item->id }}][unit_price]"
                                                                           class="form-control order-price"
                                                                           value="{{ old('items.'.$item->id.'.unit_price') }}"
                                                                           min="0"
                                                                           step="0.01"
                                                                           placeholder="0.00"
                                                                           {{ $remaining <= 0.0001 ? 'disabled' : '' }}>
                                                                </div>

                                                                {{-- Discount --}}
                                                                <div class="form-group col-md-6">
                                                                    <label>Discount Amount</label>

                                                                    <input type="number"
                                                                           name="items[{{ $item->id }}][discount]"
                                                                           class="form-control order-discount"
                                                                           value="{{ old('items.'.$item->id.'.discount', '0') }}"
                                                                           min="0"
                                                                           step="0.01"
                                                                           placeholder="0.00"
                                                                           {{ $remaining <= 0.0001 ? 'disabled' : '' }}>
                                                                </div>

                                                                {{-- Tax --}}
                                                                <div class="form-group col-md-6">
                                                                    <label>Tax Amount</label>

                                                                    <input type="number"
                                                                           name="items[{{ $item->id }}][tax]"
                                                                           class="form-control order-tax"
                                                                           value="{{ old('items.'.$item->id.'.tax', '0') }}"
                                                                           min="0"
                                                                           step="0.01"
                                                                           placeholder="0.00"
                                                                           {{ $remaining <= 0.0001 ? 'disabled' : '' }}>
                                                                </div>

                                                                <div class="col-12">
                                                                    <small class="text-muted">
                                                                        Maximum order quantity:
                                                                        {{ number_format($remaining, 3) }}
                                                                        {{ $item->material?->unit ?? '' }}
                                                                    </small>
                                                                </div>

                                                            </div>

                                                        </div>
                                                    </div>

                                                </td>
                                            </tr>

                                        @empty

                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    This request has no approved quantities.
                                                </td>
                                            </tr>

                                        @endforelse

                                    </tbody>
                                </table>

                            </div>

                            <hr>

                            {{-- Purchase Order Preview --}}
                            <div class="card bg-light mb-3">

                                <div class="card-body">

                                    <h5 class="card-title">
                                        Supplier-Based Purchase Order Preview
                                    </h5>

                                    <div id="order-preview" aria-live="polite">
                                        Assign a supplier, quantity, unit price,
                                        discount, and tax to preview the purchase orders.
                                    </div>

                                </div>
                            </div>

                            {{-- Actions --}}
                            <button type="submit"
                                    class="btn btn-primary"
                                    {{ ! $hasRemainingQuantities || $suppliers->isEmpty() || $approvedItems->isEmpty() ? 'disabled' : '' }}>

                                <i class="ti-save"></i>
                                Generate Purchase Orders

                            </button>

                            <a href="{{ route('purchase-orders.index') }}"
                               class="btn btn-light">
                                Cancel
                            </a>

                        </form>

                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const selectionForm = document.getElementById('purchase-request-selection-form');
    const requestSelect = document.getElementById('purchase-request-select');

    const form = document.getElementById('purchase-order-generation-form');

    const preview = document.getElementById('order-preview');

    const rows = document.querySelectorAll('[data-order-row]');


    /*
    |--------------------------------------------------------------------------
    | Purchase Request Selection
    |--------------------------------------------------------------------------
    */

    if (requestSelect && selectionForm) {

        requestSelect.addEventListener('change', function () {

            if (requestSelect.value) {
                selectionForm.submit();
            }

        });

    }


    if (!form) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Material Details
    |--------------------------------------------------------------------------
    */

    function getMaterialData(row) {

        const quantityInput = row.querySelector('.order-quantity');
        const supplierInput = row.querySelector('.order-supplier');
        const priceInput = row.querySelector('.order-price');
        const discountInput = row.querySelector('.order-discount');
        const taxInput = row.querySelector('.order-tax');

        const quantity = Number(quantityInput.value);
        const price = Number(priceInput.value);
        const discount = Number(discountInput.value);
        const tax = Number(taxInput.value);

        const maxQuantity = Number(quantityInput.max);

        const data = {
            quantity: quantity,
            price: price,
            discount: discount,
            tax: tax,
            subtotal: 0,
            total: 0,
            supplierId: supplierInput.value,
            supplierName: '',
            valid: true
        };


        // Ignore materials not selected for ordering.
        if (quantityInput.value.trim() === '' || quantity === 0) {
            data.valid = false;
            data.ignored = true;
            return data;
        }


        if (
            !Number.isFinite(quantity) ||
            quantity < 0 ||
            quantity > maxQuantity ||
            !supplierInput.value ||
            priceInput.value.trim() === '' ||
            !Number.isFinite(price) ||
            price < 0 ||
            discountInput.value.trim() === '' ||
            !Number.isFinite(discount) ||
            discount < 0 ||
            taxInput.value.trim() === '' ||
            !Number.isFinite(tax) ||
            tax < 0
        ) {

            data.valid = false;
            return data;

        }


        data.supplierName =
            supplierInput.options[supplierInput.selectedIndex].text;

        data.subtotal = Number((quantity * price).toFixed(2));

        data.total = Number(
            (data.subtotal - discount + tax).toFixed(2)
        );


        if (discount > data.subtotal || data.total < 0) {
            data.valid = false;
        }


        return data;

    }


    /*
    |--------------------------------------------------------------------------
    | Render Supplier-wise Purchase Order Preview
    |--------------------------------------------------------------------------
    */

    function renderPreview() {

        const groups = new Map();

        let incomplete = false;


        rows.forEach(function (row) {

            const data = getMaterialData(row);


            if (data.ignored) {
                return;
            }


            if (!data.valid) {

                incomplete = true;

                return;

            }


            if (!groups.has(data.supplierId)) {

                groups.set(data.supplierId, {

                    name: data.supplierName,

                    lines: [],

                    subtotal: 0,

                    discount: 0,

                    tax: 0,

                    total: 0

                });

            }


            const group = groups.get(data.supplierId);

            group.subtotal += data.subtotal;

            group.discount += data.discount;

            group.tax += data.tax;

            group.total = Number(
                (group.subtotal - group.discount + group.tax).toFixed(2)
            );


            group.lines.push({

                material: row.dataset.material,

                quantity: data.quantity,

                unit: row.dataset.unit,

                price: data.price,

                subtotal: data.subtotal,

                discount: data.discount,

                tax: data.tax,

                total: data.total

            });

        });


        preview.textContent = '';


        if (groups.size === 0 && !incomplete) {

            preview.textContent =
                'Assign a supplier, quantity, unit price, discount, and tax to preview the purchase orders.';

            return {
                count: 0,
                incomplete: false
            };

        }


        /*
        |--------------------------------------------------------------------------
        | Display Supplier-wise Preview
        |--------------------------------------------------------------------------
        */

        if (groups.size > 0) {

            const countText = document.createElement('p');

            countText.className = 'font-weight-bold';

            countText.textContent =
                groups.size + ' Purchase Order(s) will be generated.';

            preview.appendChild(countText);


            groups.forEach(function (group) {

                const card = document.createElement('div');

                card.className = 'border rounded p-3 mb-3';


                const heading = document.createElement('h6');

                heading.className = 'font-weight-bold';

                heading.textContent = group.name + ' Purchase Order';

                card.appendChild(heading);


                const tableWrapper = document.createElement('div');

                tableWrapper.className = 'table-responsive';


                const table = document.createElement('table');

                table.className = 'table table-sm table-bordered mb-2';


                const thead = document.createElement('thead');

                thead.className = 'thead-light';


                const headerRow = document.createElement('tr');


                [
                    'Material',
                    'Quantity',
                    'Unit Price',
                    'Subtotal',
                    'Discount',
                    'Tax',
                    'Total'
                ].forEach(function (title) {

                    const th = document.createElement('th');

                    th.textContent = title;

                    headerRow.appendChild(th);

                });


                thead.appendChild(headerRow);

                table.appendChild(thead);


                const tbody = document.createElement('tbody');


                group.lines.forEach(function (line) {

                    const tr = document.createElement('tr');


                    const values = [

                        line.material,

                        line.quantity.toFixed(3) + ' ' + line.unit,

                        line.price.toFixed(2),

                        line.subtotal.toFixed(2),

                        line.discount.toFixed(2),

                        line.tax.toFixed(2),

                        line.total.toFixed(2)

                    ];


                    values.forEach(function (value) {

                        const td = document.createElement('td');

                        td.textContent = value;

                        tr.appendChild(td);

                    });


                    tbody.appendChild(tr);

                });


                table.appendChild(tbody);

                tableWrapper.appendChild(table);

                card.appendChild(tableWrapper);


                const summary = document.createElement('div');

                summary.className = 'text-right';


                const subtotal = document.createElement('p');

                subtotal.className = 'mb-1';

                subtotal.textContent =
                    'Subtotal: ' + group.subtotal.toFixed(2);


                const discount = document.createElement('p');

                discount.className = 'mb-1';

                discount.textContent =
                    'Total Discount: ' + group.discount.toFixed(2);


                const tax = document.createElement('p');

                tax.className = 'mb-1';

                tax.textContent =
                    'Total Tax: ' + group.tax.toFixed(2);


                const total = document.createElement('h6');

                total.className = 'font-weight-bold';

                total.textContent =
                    'Purchase Order Total: ' + group.total.toFixed(2);


                summary.appendChild(subtotal);

                summary.appendChild(discount);

                summary.appendChild(tax);

                summary.appendChild(total);


                card.appendChild(summary);

                preview.appendChild(card);

            });

        }


        if (incomplete) {

            const warning = document.createElement('p');

            warning.className = 'text-warning mb-0';

            warning.textContent =
                'Please complete supplier, quantity, unit price, discount, and tax for every selected material. Discount cannot exceed the material subtotal.';

            preview.appendChild(warning);

        }


        return {
            count: groups.size,
            incomplete: incomplete
        };

    }


    /*
    |--------------------------------------------------------------------------
    | Material Input Events
    |--------------------------------------------------------------------------
    */

    rows.forEach(function (row) {

        row.querySelectorAll('select, input').forEach(function (input) {

            input.addEventListener('input', renderPreview);

            input.addEventListener('change', renderPreview);

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Form Submit Validation
    |--------------------------------------------------------------------------
    */

    form.addEventListener('submit', function (event) {

        const previewState = renderPreview();


        if (previewState.incomplete || previewState.count === 0) {

            event.preventDefault();

            window.alert(
                previewState.incomplete
                    ? 'Please complete supplier, quantity, unit price, discount, and tax for every selected material.'
                    : 'Select at least one material, supplier, quantity, and unit price.'
            );

            return;

        }


        if (
            !window.confirm(
                'Generate ' +
                previewState.count +
                ' supplier-based purchase order(s)?'
            )
        ) {

            event.preventDefault();

        }

    });


    /*
    |--------------------------------------------------------------------------
    | Initial Preview
    |--------------------------------------------------------------------------
    */

    renderPreview();

});
</script>

@endpush
