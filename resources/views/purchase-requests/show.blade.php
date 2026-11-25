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
                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title">
                            {{ $purchase_request->request_number }}
                        </h4>
                        <div>
                            @can('purchase-order-create')
                                @if(in_array($purchase_request->status, ['Approved', 'Partially Approved'], true))
                                    <a href="{{ route('purchase-requests.purchase-orders.create', $purchase_request) }}"
                                       class="btn btn-success">
                                        <i class="ti-shopping-cart"></i> Create Purchase Orders
                                    </a>
                                @endif
                            @endcan
                            @can('purchase-request-edit')
                                @if($purchase_request->status === 'Pending')
                                    <a href="{{ route('purchase-requests.edit', $purchase_request) }}"
                                       class="btn btn-primary">
                                        <i class="ti-pencil"></i> Edit
                                    </a>
                                @endif
                            @endcan
                        </div>
                    </div>
                    <hr>
                    {{-- Purchase Request Details --}}
                    <dl class="row">
                        <dt class="col-sm-3">Project</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_request->project->project_name ?? '-' }}
                        </dd>
                        <dt class="col-sm-3">Requester</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_request->requester->name ?? '-' }}
                        </dd>
                        <dt class="col-sm-3">Purpose</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_request->purpose }}
                        </dd>
                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-secondary">
                                {{ $purchase_request->status }}
                            </span>
                        </dd>
                        <dt class="col-sm-3">Notes</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_request->notes ?: '-' }}
                        </dd>
                        <dt class="col-sm-3">Approved By</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_request->approver?->name ?: '-' }}
                        </dd>
                        <dt class="col-sm-3">Approval Date</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_request->approved_at?->format('Y-m-d H:i') ?: '-' }}
                        </dd>
                        <dt class="col-sm-3">Remarks</dt>
                        <dd class="col-sm-9">
                            {{ $purchase_request->approval_remarks ?: '-' }}
                        </dd>
                    </dl>
                    <hr>
                    {{-- Requested Materials --}}
                    <h5 class="mb-3">Requested Materials</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Material</th>
                                    <th>Requested Quantity</th>
                                    <th>Approved Quantity</th>
                                    <th>Required Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchase_request->items as $item)
                                    <tr>
                                        <td>
                                            {{ $item->material->material_code ?? '-' }}
                                            -
                                            {{ $item->material->name ?? '-' }}
                                        </td>
                                        <td>
                                            {{ number_format($item->requested_quantity, 3) }}
                                            {{ $item->material->unit ?? '' }}
                                        </td>
                                        <td>
                                            {{ number_format($item->approved_quantity ?? 0, 3) }}
                                        </td>
                                        <td>
                                            {{ $item->required_date?->format('Y-m-d') ?? '-' }}
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
                    {{-- Approval / Rejection / Cancellation --}}
                    @if($purchase_request->status === 'Pending')
                        {{-- Requester Information --}}
                        @if(auth()->id() === $purchase_request->requester_id)
                            <div class="alert alert-info mt-4">
                                This request is pending approval. Another user with approval permission must review it.
                            </div>
                        @else
                            {{-- Approve / Reject Buttons --}}
                            <div class="text-center mt-4">
                                @can('purchase-request-approve')
                                    <button type="button"
                                            class="btn btn-success mr-2"
                                            id="show-approve">
                                        <i class="ti-check"></i> Approve
                                    </button>
                                @endcan
                                @can('purchase-request-reject')
                                    <button type="button"
                                            class="btn btn-danger"
                                            id="show-reject">
                                        <i class="ti-close"></i> Reject
                                    </button>
                                @endcan
                            </div>
                            {{-- Approve Form --}}
                            @can('purchase-request-approve')
                                <div id="approve-form" class="card mt-4 d-none">
                                    <div class="card-body">
                                        <h5 class="card-title text-success">
                                            Approve / Partially Approve
                                        </h5>
                                        <form method="POST"
                                              action="{{ route('purchase-requests.approve', $purchase_request) }}">
                                            @csrf
                                            {{-- Approved Quantities --}}
                                            @foreach($purchase_request->items as $item)
                                                <div class="form-group row">
                                                    <label class="col-md-5 col-form-label">
                                                        {{ $item->material->name ?? 'Unknown Material' }}
                                                        (Requested:
                                                        {{ number_format($item->requested_quantity, 3) }})
                                                    </label>
                                                    <div class="col-md-3">
                                                        <input
                                                            type="number"
                                                            name="approved_quantity[{{ $item->id }}]"
                                                            class="form-control"
                                                            value="{{ $item->requested_quantity }}"
                                                            min="0"
                                                            max="{{ $item->requested_quantity }}"
                                                            step="0.001"
                                                            required
                                                        >
                                                    </div>
                                                </div>
                                            @endforeach
                                            {{-- Approval Remarks --}}
                                            <div class="form-group">
                                                <label>Approval Remarks (Optional)</label>
                                                <textarea
                                                    name="approval_remarks"
                                                    class="form-control"
                                                    rows="3"
                                                    placeholder="Enter approval remarks..."
                                                ></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-success">
                                                <i class="ti-check"></i> Confirm Approval
                                            </button>
                                            <button type="button"
                                                    class="btn btn-light cancel-action">
                                                Cancel
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endcan
                            {{-- Reject Form --}}
                            @can('purchase-request-reject')
                                <div id="reject-form" class="card mt-4 d-none">
                                    <div class="card-body">
                                        <h5 class="card-title text-danger">
                                            Reject Purchase Request
                                        </h5>
                                        <form method="POST"
                                              action="{{ route('purchase-requests.reject', $purchase_request) }}">
                                            @csrf
                                            <div class="form-group">
                                                <label>Rejection Remarks (Optional)</label>
                                                <textarea
                                                    name="approval_remarks"
                                                    class="form-control"
                                                    rows="3"
                                                    placeholder="Enter rejection remarks..."
                                                ></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-danger">
                                                <i class="ti-close"></i> Confirm Rejection
                                            </button>
                                            <button type="button"
                                                    class="btn btn-light cancel-action">
                                                Cancel
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endcan
                        @endif
                        {{-- Cancel Purchase Request --}}
                        @if(auth()->id() === $purchase_request->requester_id)
                            <form
                                method="POST"
                                action="{{ route('purchase-requests.cancel', $purchase_request) }}"
                                onsubmit="return confirm('Cancel this purchase request?')"
                                class="mt-3"
                            >
                                @csrf
                                <button class="btn btn-warning" type="submit">
                                    <i class="ti-na"></i> Cancel Request
                                </button>
                            </form>
                        @endif
                    @endif
                    {{-- Back Button --}}
                    <a href="{{ route('purchase-requests.index') }}"
                       class="btn btn-light mt-3">
                        <i class="ti-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const approveForm = document.getElementById('approve-form');
    const rejectForm = document.getElementById('reject-form');
    const approveButton = document.getElementById('show-approve');
    const rejectButton = document.getElementById('show-reject');
    // Show Approve Form
    if (approveButton) {
        approveButton.addEventListener('click', function () {
            if (approveForm) {
                approveForm.classList.remove('d-none');
            }
            if (rejectForm) {
                rejectForm.classList.add('d-none');
            }
        });
    }
    // Show Reject Form
    if (rejectButton) {
        rejectButton.addEventListener('click', function () {
            if (rejectForm) {
                rejectForm.classList.remove('d-none');
            }
            if (approveForm) {
                approveForm.classList.add('d-none');
            }
        });
    }
    // Cancel Forms
    document.querySelectorAll('.cancel-action').forEach(function (button) {
        button.addEventListener('click', function () {
            if (approveForm) {
                approveForm.classList.add('d-none');
            }
            if (rejectForm) {
                rejectForm.classList.add('d-none');
            }
        });
    });
});
</script>
@endpush
@endsection
