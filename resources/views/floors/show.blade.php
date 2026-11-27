@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('sites.index') }}">Sites</a></li>
                @if($floor->building?->site)
                    <li class="breadcrumb-item">
                        <a href="{{ route('sites.show', $floor->building->site) }}">{{ $floor->building->site->project_name }}</a>
                    </li>
                @endif
                @if($floor->building)
                    <li class="breadcrumb-item"><a href="{{ route('buildings.show', $floor->building) }}">{{ $floor->building->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('buildings.show', $floor->building) }}#building-floors">Floors</a></li>
                @endif
                <li class="breadcrumb-item active" aria-current="page">{{ $floor->name }}</li>
            </ol>
        </nav>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center flex-wrap">
                    <h3 class="mb-1 mr-3">{{ $floor->name }}</h3>
                    <span class="badge badge-primary mr-2">Floor {{ $floor->floor_number }}</span>
                    <span class="badge badge-{{ $floor->statusBadgeColor() }}">{{ $floor->statusLabel() }}</span>
                </div>
                <p class="text-muted mt-2 mb-0">
                    {{ $floor->building?->name ?? 'Building unavailable' }}
                    @if($floor->building?->site)
                        &middot; {{ $floor->building->site->project_name }}
                    @endif
                </p>
            </div>
            <div class="col-md-4 d-flex justify-content-md-end mt-3 mt-md-0">
                @if($floor->building)
                    <a href="{{ route('buildings.show', $floor->building) }}#building-floors" class="btn btn-light mr-2">
                        <i class="ti-arrow-left"></i> Back to Floors
                    </a>
                @endif
                @can('floors-edit')
                    <a href="{{ route('floors.edit', $floor) }}" class="btn btn-primary">
                        <i class="ti-settings"></i> Edit Floor Configuration
                    </a>
                @endcan
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs mb-4" id="floorDetailsTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="floor-summary-tab" data-toggle="tab" href="#floor-summary"
                           role="tab" aria-controls="floor-summary" aria-selected="true">Summary &amp; Configuration</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="floor-plans-tab" data-toggle="tab" href="#floor-plans"
                           role="tab" aria-controls="floor-plans" aria-selected="false">Floor Plans</a>
                    </li>
                </ul>

                <div class="tab-content" id="floorDetailsTabContent">
                    <div class="tab-pane fade show active" id="floor-summary" role="tabpanel" aria-labelledby="floor-summary-tab">
                        <div class="row">
                            <div class="col-lg-6">
                                <h4 class="card-title">Floor Summary</h4>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr><th>Floor Name</th><td>{{ $floor->name }}</td></tr>
                                            <tr><th>Floor Number</th><td>{{ $floor->floor_number }}</td></tr>
                                            <tr><th>Floor Type</th><td>{{ $floor->floorTypeLabel() }}</td></tr>
                                            <tr>
                                                <th>Status</th>
                                                <td><span class="badge badge-{{ $floor->statusBadgeColor() }}">{{ $floor->statusLabel() }}</span></td>
                                            </tr>
                                            <tr>
                                                <th>Description</th>
                                                <td>
                                                    @if($floor->description)
                                                        {!! nl2br(e($floor->description)) !!}
                                                    @else
                                                        <span class="text-muted">No description available.</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <h4 class="card-title">Floor Configuration</h4>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr>
                                                <th>Floor Area</th>
                                                <td>
                                                    {{ $floor->floor_area !== null ? number_format((float) $floor->floor_area, 2).' '.$floor->floor_area_unit : 'N/A' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Common Area</th>
                                                <td>{{ $floor->common_area !== null ? number_format((float) $floor->common_area, 2).' '.$floor->floor_area_unit : 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Notes</th>
                                                <td>{{ $floor->notes ?: 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                @if($floor->configuration)
                                    <h5 class="mt-3">Configuration Details</h5>
                                    <pre class="border rounded p-3">{{ json_encode($floor->configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                @else
                                    <p class="text-muted">No additional configuration details.</p>
                                @endif
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <small class="text-muted"><i class="ti-calendar mr-1"></i>Created At</small>
                                <p class="font-weight-bold mb-0">{{ $floor->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <small class="text-muted"><i class="ti-reload mr-1"></i>Last Updated</small>
                                <p class="font-weight-bold mb-0">{{ $floor->updated_at?->format('d M Y, h:i A') ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="floor-plans" role="tabpanel" aria-labelledby="floor-plans-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="card-title mb-1">Floor Plans</h4>
                                <p class="card-description mb-0">Plans uploaded for {{ $floor->name }}.</p>
                            </div>
                            @can('floor-plans-create')
                                <a href="{{ route('floor-plans.create', ['building_id' => $floor->building_id, 'floor_id' => $floor->id]) }}"
                                   class="btn btn-primary">
                                    <i class="ti-plus"></i> Upload Floor Plan
                                </a>
                            @endcan
                        </div>
                        @can('floor-plans-list')
                            @include('floor-plans._table', ['floorPlans' => $floor->floorPlans])
                        @else
                            <div class="alert alert-info">You do not have permission to view floor plans.</div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.partials.footer')
