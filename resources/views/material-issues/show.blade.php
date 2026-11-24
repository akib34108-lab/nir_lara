
@extends('layouts.master')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Error Message --}}
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <h4 class="card-title">
                        Material Issue: {{ $material_issue->issue_number }}
                    </h4>

                    <hr>

                    {{-- Material Issue Information --}}
                    <dl class="row">
                        <dt class="col-sm-3">Project</dt>
                        <dd class="col-sm-9">
                            {{ $material_issue->project?->project_name ?? '-' }}
                        </dd>

                        <dt class="col-sm-3">Site</dt>
                        <dd class="col-sm-9">
                            {{ $material_issue->site ?: '-' }}
                        </dd>

                        <dt class="col-sm-3">Warehouse</dt>
                        <dd class="col-sm-9">
                            {{ $material_issue->warehouse?->name ?? '-' }}
                        </dd>

                        <dt class="col-sm-3">Requested By</dt>
                        <dd class="col-sm-9">
                            {{ $material_issue->requester?->name ?? '-' }}
                        </dd>

                        <dt class="col-sm-3">Issued By</dt>
                        <dd class="col-sm-9">
                            {{ $material_issue->issuer?->name ?? '-' }}
                        </dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            <span class="badge badge-secondary">
                                {{ $material_issue->status }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Notes</dt>
                        <dd class="col-sm-9">
                            {{ $material_issue->notes ?: '-' }}
                        </dd>
                    </dl>

                    {{-- Material Items --}}
                    <h5 class="mt-4">Issued Materials</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Material</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($material_issue->items as $item)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            {{ $item->material?->material_code ?? '-' }}
                                            -
                                            {{ $item->material?->name ?? '-' }}
                                        </td>

                                        <td>
                                            {{ number_format($item->quantity, 3) }}
                                            {{ $item->material?->unit ?? '' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            No materials found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Confirm Material Issue --}}
                    @if($material_issue->status === 'Draft')
                        @can('material-issue-approve')
                            <form
                                method="POST"
                                action="{{ route('material-issues.confirm', $material_issue) }}"
                                onsubmit="return confirm('Confirm issue and reduce warehouse stock?')"
                            >
                                @csrf

                                <button type="submit" class="btn btn-success">
                                    Confirm Issue
                                </button>
                            </form>
                        @endcan
                    @endif

                    <a href="{{ route('material-issues.index') }}"
                       class="btn btn-light mt-3">
                        Back
                    </a>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
