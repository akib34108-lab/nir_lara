
@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')

@php
    $siteStatuses = [
        1 => 'Planning',
        2 => 'Active',
        3 => 'On Hold',
        4 => 'Completed',
        5 => 'Cancelled',
    ];

    $statusColors = [
        1 => 'info',
        2 => 'primary',
        3 => 'warning',
        4 => 'success',
        5 => 'danger',
    ];

    $landAreaUnits = [
        1 => 'Katha',
        2 => 'Decimal',
        3 => 'Acre',
        4 => 'Bigha',
        5 => 'Square Feet',
        6 => 'Square Meter',
    ];

    $siteStatus = $siteStatuses[$site->project_status] ?? 'Unknown';
    $statusColor = $statusColors[$site->project_status] ?? 'secondary';
@endphp

<div class="main-panel">
    <div class="content-wrapper">

        {{-- Page Header --}}
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="d-flex align-items-center flex-wrap">
                    <h3 class="mb-1 mr-3">{{ $site->project_name }}</h3>

                    <span class="badge badge-primary mr-2">
                        {{ $site->project_code }}
                    </span>
                </div>

                <p class="text-muted mt-2 mb-0">
                    Site Overview &amp; Details
                </p>
            </div>

            <div class="col-md-4 d-flex justify-content-md-end align-items-center mt-3 mt-md-0">
                <a href="{{ route('sites.index') }}" class="btn btn-light mr-2">
                    <i class="ti-arrow-left"></i> Back
                </a>

                @can('sites-edit')
                    <a href="{{ route('sites.edit', $site->id) }}" class="btn btn-primary">
                        <i class="ti-pencil"></i> Edit Site
                    </a>
                @endcan
            </div>
        </div>
        <div class="row">
            <div class="col-lg-7">
                <div class="row">
                    <div class="col-md-6 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <p class="text-muted mb-2">Land Area</p>
                                <h3 class="font-weight-bold">
                                    {{ number_format((float) $site->land_area, 2) }}

                                    <small class="font-weight-normal">
                                        {{ $landAreaUnits[$site->land_area_unit] ?? '' }}
                                    </small>
                                </h3>
                                <p class="text-muted mb-0">Total site land</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <p class="text-muted mb-2">Estimated Budget</p>
                                <h3 class="font-weight-bold">
                                    ৳ {{ $site->estimated_project_budget !== null
                                        ? number_format((float) $site->estimated_project_budget, 2)
                                        : 'N/A' }}
                                </h3>
                                <p class="text-muted mb-0">Project budget</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <p class="text-muted mb-2">Planned Start</p>
                                <h4 class="font-weight-bold">
                                    {{ $site->planned_start_date
                                        ? \Carbon\Carbon::parse($site->planned_start_date)->format('d M Y')
                                        : 'N/A' }}
                                </h4>
                                <p class="text-muted mb-0">Project commencement</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 grid-margin stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <p class="text-muted mb-2">Planned Completion</p>
                                <h4 class="font-weight-bold">
                                    {{ $site->planned_completion_date
                                        ? \Carbon\Carbon::parse($site->planned_completion_date)->format('d M Y')
                                        : 'N/A' }}
                                </h4>
                                <p class="text-muted mb-0">Target completion</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card grid-margin">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="ti-agenda mr-2"></i>
                            Project Description
                        </h4>
                        <div class="border rounded p-3">
                            @if($site->description)
                                {!! nl2br(e($site->description)) !!}
                            @else
                                <span class="text-muted">
                                    No project description available.
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card grid-margin">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="ti-map-alt mr-2"></i>
                            Land Description
                        </h4>
                        <div class="border rounded p-3">
                            @if($site->land_description)
                                {!! nl2br(e($site->land_description)) !!}
                            @else
                                <span class="text-muted">
                                    No land description available.
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card grid-margin">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">
                                    <i class="ti-calendar mr-1"></i>
                                    Created At
                                </small>
                                <p class="font-weight-bold mb-0">
                                    {{ $site->created_at
                                        ? $site->created_at->format('d M Y, h:i A')
                                        : 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <small class="text-muted">
                                    <i class="ti-reload mr-1"></i>
                                    Last Updated
                                </small>
                                <p class="font-weight-bold mb-0">
                                    {{ $site->updated_at
                                        ? $site->updated_at->format('d M Y, h:i A')
                                        : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card grid-margin stretch-card">
                    <div class="card-body">
                        <ul class="nav nav-tabs mb-4"
                            id="siteDetailsTab"
                            role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active"
                                   id="site-info-tab"
                                   data-toggle="tab"
                                   href="#site-info"
                                   role="tab">
                                    <i class="ti-location-pin"></i>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"
                                   id="land-tab"
                                   data-toggle="tab"
                                   href="#land"
                                   role="tab">
                                    <i class="ti-map-alt"></i>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"
                                   id="timeline-tab"
                                   data-toggle="tab"
                                   href="#timeline"
                                   role="tab">
                                    <i class="ti-calendar"></i>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link"
                                   id="finance-tab"
                                   data-toggle="tab"
                                   href="#finance"
                                   role="tab">
                                    <i class="ti-money"></i>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content" id="siteDetailsTabContent">
                            <div class="tab-pane fade show active"
                                 id="site-info"
                                 role="tabpanel">
                                <h4 class="card-title">Site Information</h4>
                                <p class="card-description">
                                    Project and location details
                                </p>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <th>Site Code</th>
                                                <td>{{ $site->project_code }}</td>
                                            </tr>

                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    <span class="badge badge-{{ $statusColor }}">
                                                        {{ $siteStatus }}
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Address</th>
                                                <td>{{ $site->address ?: 'N/A' }}</td>
                                            </tr>

                                            <tr>
                                                <th>Area / City</th>
                                                <td>
                                                    {{ collect([$site->area, $site->city])->filter()->implode(', ') ?: 'N/A' }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>District / Division</th>
                                                <td>
                                                    {{ collect([$site->district, $site->division])->filter()->implode(', ') ?: 'N/A' }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Country</th>
                                                <td>{{ $site->country ?: 'N/A' }}</td>
                                            </tr>

                                            <tr>
                                                <th>Postal Code</th>
                                                <td>{{ $site->postal_code ?: 'N/A' }}</td>
                                            </tr>

                                            <tr>
                                                <th>Google Maps</th>
                                                <td>
                                                    @if($site->google_maps_location)
                                                        <a href="{{ $site->google_maps_location }}"
                                                           target="_blank"
                                                           rel="noopener noreferrer">
                                                            Open map
                                                            <i class="ti-new-window"></i>
                                                        </a>
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            {{-- Land Details --}}
                            <div class="tab-pane fade"
                                 id="land"
                                 role="tabpanel">

                                <h4 class="card-title">Land Details</h4>
                                <p class="card-description">Parcel information</p>

                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>

                                            <tr>
                                                <th width="40%">Land Area</th>
                                                <td>
                                                    {{ number_format((float) $site->land_area, 2) }}
                                                    {{ $landAreaUnits[$site->land_area_unit] ?? '' }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Plot Number</th>
                                                <td>{{ $site->plot_number ?: 'N/A' }}</td>
                                            </tr>

                                            <tr>
                                                <th>Block</th>
                                                <td>{{ $site->block ?: 'N/A' }}</td>
                                            </tr>

                                            <tr>
                                                <th>Road Number</th>
                                                <td>{{ $site->road_number ?: 'N/A' }}</td>
                                            </tr>

                                            <tr>
                                                <th>Sector</th>
                                                <td>{{ $site->sector ?: 'N/A' }}</td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            {{-- Project Timeline --}}
                            <div class="tab-pane fade"
                                 id="timeline"
                                 role="tabpanel">

                                <h4 class="card-title">Project Timeline</h4>
                                <p class="card-description">
                                    Project planning and execution dates
                                </p>

                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>

                                            <tr>
                                                <th width="40%">Planned Start</th>
                                                <td>
                                                    {{ $site->planned_start_date
                                                        ? \Carbon\Carbon::parse($site->planned_start_date)->format('d M Y')
                                                        : 'N/A' }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Planned Completion</th>
                                                <td>
                                                    {{ $site->planned_completion_date
                                                        ? \Carbon\Carbon::parse($site->planned_completion_date)->format('d M Y')
                                                        : 'N/A' }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Actual Start</th>
                                                <td>
                                                    {{ $site->actual_start_date
                                                        ? \Carbon\Carbon::parse($site->actual_start_date)->format('d M Y')
                                                        : 'N/A' }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Actual Completion</th>
                                                <td>
                                                    {{ $site->actual_completion_date
                                                        ? \Carbon\Carbon::parse($site->actual_completion_date)->format('d M Y')
                                                        : 'N/A' }}
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            {{-- Shares & Budget --}}
                            <div class="tab-pane fade"
                                 id="finance"
                                 role="tabpanel">

                                <h4 class="card-title">Shares & Budget</h4>
                                <p class="card-description">
                                    Project share and financial information
                                </p>

                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>

                                            <tr>
                                                <th width="40%">Total Shares</th>
                                                <td>
                                                    {{ $site->total_shares !== null
                                                        ? number_format($site->total_shares)
                                                        : 'N/A' }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Value per Share</th>
                                                <td>
                                                    {{ $site->per_share_value !== null
                                                        ? '৳ ' . number_format((float) $site->per_share_value, 2)
                                                        : 'N/A' }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Estimated Project Budget</th>
                                                <td>
                                                    {{ $site->estimated_project_budget !== null
                                                        ? '৳ ' . number_format((float) $site->estimated_project_budget, 2)
                                                        : 'N/A' }}
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>


@include('layouts.partials.footer')
