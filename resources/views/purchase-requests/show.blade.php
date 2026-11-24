
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
                    @if($purchase_request->status === 'Pending' && auth()->id() === $purchase_request->requester_id)
                        <div class="alert alert-info mt-4" role="alert">
                            This request is pending approval. To keep approvals independent, you cannot approve or reject your own request.
                            Another user with purchase request approval permission must review it.
                        </div>
                    @endif

                    @if($purchase_request->status === 'Pending')

                        {{-- Approve Purchase Request --}}
                        @can('purchase-request-approve')

                            @if(auth()->id() !== $purchase_request->requester_id)

                                <div class="card mt-4">
                                    <div class="card-body">

                                        <h5 class="card-title">
                                            Approve / Partially Approve
                                        </h5>

                                        <form method="POST"
                                              action="{{ route('purchase-requests.approve', $purchase_request) }}">

                                            @csrf

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

                                            <div class="form-group">
                                                <label>Approval Remarks</label>

                                                <textarea
                                                    name="approval_remarks"
                                                    class="form-control"
                                                    rows="2"
                                                    placeholder="Enter approval remarks..."
                                                ></textarea>
                                            </div>

                                            <button class="btn btn-success" type="submit">
                                                <i class="ti-check"></i> Approve
                                            </button>

                                        </form>

                                    </div>
                                </div>

                            @endif

                        @endcan

                        {{-- Reject Purchase Request --}}
                        @can('purchase-request-reject')

                            @if(auth()->id() !== $purchase_request->requester_id)

                                <form method="POST"
                                      action="{{ route('purchase-requests.reject', $purchase_request) }}"
                                      class="mt-3">

                                    @csrf

                                    <div class="form-group">
                                        <label>Rejection Remarks</label>

                                        <textarea
                                            name="approval_remarks"
                                            class="form-control"
                                            rows="2"
                                            placeholder="Enter rejection remarks..."
                                        ></textarea>
                                    </div>

                                    <button class="btn btn-danger" type="submit">
                                        <i class="ti-close"></i> Reject
                                    </button>

                                </form>

                            @endif

                        @endcan

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
@endsection
