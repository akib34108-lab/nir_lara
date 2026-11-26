@extends('layouts.master')
@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="card-title mb-0">Purchase Requests</h4>
                            <div class="d-flex align-items-center">
                                <form method="GET" id="materialFilterForm" class="d-flex align-items-center mb-0">
                                    <div class="mr-2">
                                        <input type="text"
                                            name="search"
                                            id="materialSearch"
                                            value="{{ request('search') }}"
                                            class="form-control"
                                            placeholder="Search code or name"
                                            style="width: 280px;">
                                    </div>
                                    <div class="mr-2">
                                        <select name="project"
                                                class="form-control"
                                                style="width: 180px;"
                                                onchange="document.getElementById('materialFilterForm').submit()">
                                            <option value="">All Projects</option>
                                            @foreach($projects as $project)
                                                <option value="{{ $project->id }}"
                                                    {{ request('project') == $project->id ? 'selected' : '' }}>
                                                    {{ $project->project_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </form>
                                @can('purchase-request-create')
                                <a href="{{ route('purchase-requests.create') }}" class="btn btn-primary"><i class="ti-plus"></i> New Request</a>
                                @endcan
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Request</th>
                                        <th>Project</th>
                                        <th>Requester</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $purchaseRequest)
                                    <tr>
                                        <td>{{ $purchaseRequest->request_number }}</td>
                                        <td>{{ $purchaseRequest->project->project_name }}</td>
                                        <td>{{ $purchaseRequest->requester->name }}</td>
                                        <td>{{ $purchaseRequest->items_count }}</td>
                                        <td>
                                            @if($purchaseRequest->status === 'Approved')
                                            <span class="badge badge-success">{{ $purchaseRequest->status }}</span>
                                            @elseif($purchaseRequest->status === 'Rejected')
                                            <span class="badge badge-danger">{{ $purchaseRequest->status }}</span>
                                            @elseif($purchaseRequest->status === 'Pending')
                                            <span class="badge badge-warning">{{ $purchaseRequest->status }}</span>
                                            @else
                                            <span class="badge badge-secondary">{{ $purchaseRequest->status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm" data-toggle="dropdown"><i class="ti-more-alt"></i></button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item" href="{{ route('purchase-requests.show', $purchaseRequest) }}"><i class="ti-eye mr-2"></i>View</a>
                                                    @can('purchase-request-edit')
                                                        @if($purchaseRequest->status === 'Pending')
                                                            <a class="dropdown-item" href="{{ route('purchase-requests.edit', $purchaseRequest) }}"><i class="ti-pencil mr-2"></i>Edit</a>
                                                        @endif
                                                    @endcan
                                                    @can('purchase-request-delete')
                                                        @if(in_array($purchaseRequest->status, ['Pending', 'Cancelled']))
                                                            <form method="POST" action="{{ route('purchase-requests.destroy', $purchaseRequest) }}" onsubmit="return confirm('Delete this purchase request?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                    <button class="dropdown-item text-danger"><i class="ti-trash mr-2"></i>Delete</button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No purchase requests found.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">{{ $requests->withQueryString()->links() }}</div>      
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
    <script>
        $(document).ready(function () {
            let searchTimer;
            $('#materialSearch').on('blur', function () {
            $('#materialFilterForm').submit();
            });
        });
    </script>
@endpush
@endsection