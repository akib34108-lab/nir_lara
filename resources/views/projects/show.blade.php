@extends('layouts.master')
@section('content')
<div class="content-wrapper">
    <div class="row mb-3">
        <div class="col-md-8">
            <div class="d-flex align-items-center flex-wrap">
                <h3 class="mb-1 mr-3">{{ $project->project_name }}</h3>
                <span class="badge badge-primary mr-2">
                    {{ $project->project_code }}
                </span>
            </div>
            <p class="text-muted mt-2 mb-0">
                Project Overview & Details
            </p>
        </div>
        <div class="col-md-4 d-flex justify-content-md-end align-items-center mt-3 mt-md-0">
            @can('projects-edit')
                <a href="{{ route('projects.edit', $project->id) }}"
                   class="btn btn-primary">
                    <i class="ti-pencil"></i> Edit Project
                </a>
            @endcan
        </div>
    </div>
    <div class="row">
        <div class="col-md-6 col-lg-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Estimated Budget</p>
                    <h3 class="font-weight-bold">
                        ৳ {{ number_format($project->estimated_budget, 2) }}
                    </h3>
                    <p class="text-muted mb-0">
                        Total Project Budget
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Project Progress</p>
                    <h3 class="font-weight-bold">
                        {{ $project->progress }}%
                    </h3>
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar bg-success"
                             role="progressbar"
                             style="width: {{ $project->progress }}%"
                             aria-valuenow="{{ $project->progress }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                    <p class="text-muted mt-2 mb-0">
                        Overall completion
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Start Date</p>
                    <h4 class="font-weight-bold">
                        {{ $project->start_date
                            ? \Carbon\Carbon::parse($project->start_date)->format('d M Y')
                            : 'N/A' }}
                    </h4>
                    <p class="text-muted mb-0">
                        Project commencement
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-lg-3 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-2">Expected Completion</p>
                    <h4 class="font-weight-bold">
                        {{ $project->expected_end_date
                            ? \Carbon\Carbon::parse($project->expected_end_date)->format('d M Y')
                            : 'N/A' }}
                    </h4>
                    <p class="text-muted mb-0">
                        Target completion date
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        Project Information
                    </h4>
                    <p class="card-description">
                        Basic information about this project
                    </p>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th width="35%">Project Name</th>
                                    <td>{{ $project->project_name }}</td>
                                </tr>
                                <tr>
                                    <th>Project Code</th>
                                    <td>{{ $project->project_code }}</td>
                                </tr>
                                <tr>
                                    <th>Client</th>
                                    <td>
                                        {{ $client->name ?? 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Project Manager</th>
                                    <td>
                                        {{ $projectManager->name ?? 'Not Assigned' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>City</th>
                                    <td>{{ $project->city ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Site Address</th>
                                    <td>{{ $project->site_address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Estimated Budget</th>
                                    <td>
                                        ৳ {{ number_format($project->estimated_budget, 2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        Project Status
                    </h4>
                    <p class="card-description">
                        Current project condition
                    </p>
                    @php
                        $statuses = [
                            1 => 'Planning',
                            2 => 'Pending In Progress',
                            3 => 'Active',
                            4 => 'On Hold',
                            5 => 'Completed',
                            6 => 'Cancelled',
                        ];
                        $statusName = $statuses[$project->status] ?? 'Unknown';
                        $statusColors = [
                            1 => 'badge-info',       // Planning - Light Blue
                            2 => 'badge-warning',    // Pending In Progress - Yellow
                            3 => 'badge-primary',    // Active - Blue
                            4 => 'badge-secondary',  // On Hold - Gray
                            5 => 'badge-success',    // Completed - Green
                            6 => 'badge-danger',     // Cancelled - Red
                        ];
                        $statusColor = $statusColors[$project->status] ?? 'badge-dark';
                    @endphp
                    <div class="text-center py-4">
                        <h1 class="display-4 text-primary">
                            {{ $project->progress }}%
                        </h1>
                        <p class="text-muted">
                            Project Progress
                        </p>
                        <span class="badge {{ $statusColor }} p-2">
                            {{ $statusName }}
                        </span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-primary"
                             style="width: {{ $project->progress }}%">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">0%</small>
                        <small class="text-muted">100%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Progress History</h4>
                    <p class="card-description">Previous progress updates for this project.</p>
                    @forelse($progressHistory as $progress)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <strong>{{ \Carbon\Carbon::parse($progress->progress_date)->format('d M Y') }}</strong>
                                <span class="badge badge-success">{{ $progress->progress_percentage }}%</span>
                            </div>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                     style="width: {{ $progress->progress_percentage }}%;"
                                     aria-valuenow="{{ $progress->progress_percentage }}"
                                     aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <p class="mt-2 mb-1">{{ $progress->remarks ?: 'No remarks provided.' }}</p>
                            <small class="text-muted">Updated by: {{ $progress->creator->name ?? 'N/A' }}</small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No progress history available yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        Project Timeline
                    </h4>
                    <p class="card-description">
                        Important dates for this project
                    </p>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <small class="text-muted">Start Date</small>
                                <h5 class="mt-2">
                                    {{ $project->start_date
                                        ? \Carbon\Carbon::parse($project->start_date)->format('d M Y')
                                        : 'N/A' }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <small class="text-muted">Expected End Date</small>
                                <h5 class="mt-2">
                                    {{ $project->expected_end_date
                                        ? \Carbon\Carbon::parse($project->expected_end_date)->format('d M Y')
                                        : 'N/A' }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="border rounded p-3">
                                <small class="text-muted">Actual End Date</small>
                                <h5 class="mt-2">
                                    {{ $project->actual_end_date
                                        ? \Carbon\Carbon::parse($project->actual_end_date)->format('d M Y')
                                        : 'Not Completed' }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">
                        Project Description
                    </h4>
                    <p class="card-description">
                        Detailed description of the project
                    </p>
                    <div class="border rounded p-3">
                        @if($project->description)
                            {!! nl2br(e($project->description)) !!}
                        @else
                            <span class="text-muted">
                                No project description available.
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                Created At
                            </small>
                            <p class="mb-0">
                                {{ $project->created_at
                                    ? $project->created_at->format('d M Y, h:i A')
                                    : 'N/A' }}
                            </p>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <small class="text-muted">
                                Last Updated
                            </small>
                            <p class="mb-0">
                                {{ $project->updated_at
                                    ? $project->updated_at->format('d M Y, h:i A')
                                    : 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
