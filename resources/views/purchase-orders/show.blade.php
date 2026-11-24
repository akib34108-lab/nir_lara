
@extends('layouts.master')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    {{-- Error Message --}}
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    {{-- Page Header --}}
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">
                            {{ $purchase_order->po_number }}
                        </h4>

                        <div>
                            @can('purchase-order-edit')
                                @if(in_array($purchase_order->status, ['Draft', 'Pending Approval']))
                                    <a href="{{ route('purchase-orders.edit', $purchase_order) }}"
                                       class="btn btn-primary">
                                        <i class="ti-pencil"></i> Edit
                                    </a>
                                @endif
                            @endcan
                        </div>
                    </div>

                    <hr>

                    {{-- Purchase Order Information --}}
                    <h5 class="mb-3">Purchase Order Information</h5>

                    <dl class="row">

                        <dt class="col-sm-3">Purchase Request</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_order->purchaseRequest?->request_number ?: '-' }}
                        </dd>

                        <dt class="col-sm-3">Supplier</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_order->supplier?->company_name ?? '-' }}
                        </dd>

                        <dt class="col-sm-3">Order Date</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_order->order_date?->format('Y-m-d') ?? '-' }}
                        </dd>

                        <dt class="col-sm-3">Expected Delivery</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_order->expected_delivery_date?->format('Y-m-d') ?? '-' }}
                        </dd>

                        <dt class="col-sm-3">Payment Terms</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_order->payment_terms ?: '-' }}
                        </dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            @php
                                $statusClass = [
                                    'Draft' => 'badge-secondary',
                                    'Pending Approval' => 'badge-warning',
                                    'Approved' => 'badge-success',
                                    'Partially Received' => 'badge-info',
                                    'Completed' => 'badge-success',
                                    'Cancelled' => 'badge-danger',
                                ][$purchase_order->status] ?? 'badge-secondary';
                            @endphp

                            <span class="badge {{ $statusClass }}">
                                {{ $purchase_order->status }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Created By</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_order->creator?->name ?? '-' }}
                        </dd>

                        <dt class="col-sm-3">Approved By</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_order->approver?->name ?? '-' }}
                        </dd>

                        <dt class="col-sm-3">Notes</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_order->notes ?: '-' }}
                        </dd>

                    </dl>

                    <hr>

                    {{-- Ordered Materials --}}
                    <h5 class="mb-3">Ordered Materials</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Material</th>
                                    <th>Ordered Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($purchase_order->items as $item)
                                    <tr>
                                        <td>
                                            {{ $item->material?->material_code ?? '-' }}
                                            -
                                            {{ $item->material?->name ?? '-' }}
                                        </td>

                                        <td>
                                            {{ number_format($item->ordered_quantity, 3) }}
                                            {{ $item->material?->unit ?? '' }}
                                        </td>

                                        <td>
                                            {{ number_format($item->unit_price, 2) }}
                                        </td>

                                        <td>
                                            {{ number_format($item->subtotal, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            No materials found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    {{-- Order Summary --}}
                    <h5 class="mb-3">Order Summary</h5>

                    <div class="row">
                        <div class="col-md-6 ml-auto">

                            <dl class="row">

                                <dt class="col-8 text-right">
                                    Discount
                                </dt>
                                <dd class="col-4 text-right">
                                    {{ number_format($purchase_order->discount ?? 0, 2) }}
                                </dd>

                                <dt class="col-8 text-right">
                                    Tax
                                </dt>
                                <dd class="col-4 text-right">
                                    {{ number_format($purchase_order->tax ?? 0, 2) }}
                                </dd>

                                <dt class="col-8 text-right font-weight-bold">
                                    Grand Total
                                </dt>
                                <dd class="col-4 text-right font-weight-bold">
                                    {{ number_format($purchase_order->grand_total ?? 0, 2) }}
                                </dd>

                            </dl>

                        </div>
                    </div>

                    <hr>

                    {{-- Purchase Order Actions --}}
                    <div class="mt-3">

                        {{-- Submit for Approval --}}
                        @if($purchase_order->status === 'Draft')

                            @can('purchase-order-create')

                                <form method="POST"
                                      action="{{ route('purchase-orders.submit-approval', $purchase_order) }}"
                                      class="d-inline">

                                    @csrf

                                    <button class="btn btn-primary" type="submit">
                                        <i class="ti-upload"></i> Submit for Approval
                                    </button>

                                </form>

                            @endcan

                        @endif

                        {{-- Approve Purchase Order --}}
                        @if($purchase_order->status === 'Pending Approval')

                            @can('purchase-order-approve')

                                @if(auth()->id() !== $purchase_order->created_by)

                                    <form method="POST"
                                          action="{{ route('purchase-orders.approve', $purchase_order) }}"
                                          class="d-inline">

                                        @csrf

                                        <button class="btn btn-success" type="submit">
                                            <i class="ti-check"></i> Approve
                                        </button>

                                    </form>

                                @endif

                            @endcan

                        @endif

                        {{-- Cancel Purchase Order --}}
                        @if(!in_array($purchase_order->status, [
                            'Completed',
                            'Cancelled',
                            'Partially Received'
                        ]))

                            @can('purchase-order-edit')

                                <form method="POST"
                                      action="{{ route('purchase-orders.cancel', $purchase_order) }}"
                                      class="d-inline"
                                      onsubmit="return confirm('Cancel this purchase order?')">

                                    @csrf

                                    <button class="btn btn-danger" type="submit">
                                        <i class="ti-close"></i> Cancel
                                    </button>

                                </form>

                            @endcan

                        @endif

                        {{-- Back Button --}}
                        <a href="{{ route('purchase-orders.index') }}"
                           class="btn btn-light">
                            <i class="ti-arrow-left"></i> Back
                        </a>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
