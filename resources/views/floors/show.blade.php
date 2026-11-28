@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
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
                        <i class="ti-arrow-left"></i> Back
                    </a>
                @endif
                @can('floors-edit')
                    <a href="{{ route('floors.edit', $floor) }}" class="btn btn-primary">
                        <i class="ti-pencil"></i> Edit
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
                                                <th style="white-space: nowrap;">Description</th>
                                                <td style="white-space: normal; overflow: visible; word-break: break-word;">
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
                                                <th style="white-space: nowrap;">Notes</th>
                                                <td style="white-space: normal; overflow: visible; word-break: break-word;">
                                                    @if($floor->notes)
                                                        {!! nl2br(e($floor->notes)) !!}
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @php
                            $totalFlatArea = $floor->flats->sum(fn ($flat) => (float) ($flat->area ?? 0));
                            $totalCommonArea = $floor->commonAreas->sum(fn ($area) => (float) ($area->area ?? 0));
                        @endphp
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h4 class="card-title">Flat &amp; Common Area Summary</h4>
                                <div class="row mb-3">
                                    <div class="col-md-3"><small class="text-muted">Total Flats</small><h5>{{ $floor->flats->count() }}</h5></div>
                                    <div class="col-md-3"><small class="text-muted">Total Flat Area</small><h5>{{ number_format($totalFlatArea, 2) }} {{ $floor->floor_area_unit }}</h5></div>
                                    <div class="col-md-3"><small class="text-muted">Total Common Area</small><h5>{{ number_format($totalCommonArea, 2) }} {{ $floor->floor_area_unit }}</h5></div>
                                    <div class="col-md-3"><small class="text-muted">Remaining Area</small><h5>{{ $floor->floor_area !== null ? number_format((float) $floor->floor_area - $totalFlatArea - $totalCommonArea, 2).' '.$floor->floor_area_unit : 'N/A' }}</h5></div>
                                </div>
                            </div>
                        </div>

                        @foreach($floor->flats as $flat)
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between">
                                    <strong>{{ $flat->flat_number }}{{ $flat->name ? ' - '.$flat->name : '' }}</strong>
                                    <span>{{ number_format((float) $flat->area, 2) }} {{ $flat->area_unit }}</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Type:</strong> {{ $flat->flat_type ?: 'N/A' }}</p>
                                            <p class="mb-1"><strong>Status:</strong> {{ $flat->status }}</p>
                                            <p class="mb-0"><strong>Description:</strong> {{ $flat->description ?: 'N/A' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Bedrooms:</strong> {{ $flat->bedroom_count }} (Master: {{ $flat->master_bedroom_count }})</p>
                                            <p class="mb-1"><strong>Bathrooms:</strong> {{ $flat->bathroom_count }} | <strong>Balconies:</strong> {{ $flat->balcony_count }}</p>
                                            <p class="mb-0"><strong>Kitchen/Living/Dining:</strong> {{ $flat->kitchen_count }}/{{ $flat->living_room_count }}/{{ $flat->dining_room_count }}</p>
                                        </div>
                                    </div>
                                    @if($flat->rooms->isNotEmpty())
                                        <div class="table-responsive mt-3">
                                            <table class="table table-sm table-bordered">
                                                <thead><tr><th>Room</th><th>Type</th><th>Dimensions</th><th>Area</th><th>Features</th></tr></thead>
                                                <tbody>
                                                @foreach($flat->rooms as $room)
                                                    <tr>
                                                        <td>{{ $room->name ?: 'Room' }}</td>
                                                        <td>{{ str_replace('_', ' ', ucfirst($room->room_type)) }}</td>
                                                        <td>{{ $room->length && $room->width ? $room->length.' x '.$room->width : 'N/A' }}</td>
                                                        <td>{{ $room->area !== null ? number_format((float) $room->area, 2).' '.$room->area_unit : 'N/A' }}</td>
                                                        <td>Attached Bath: {{ $room->attached_bathroom ? 'Yes' : 'No' }}; Doors: {{ $room->door_count }}; Windows: {{ $room->window_count }}</td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if($floor->commonAreas->isNotEmpty())
                            <h4 class="card-title mt-3">Common Areas</h4>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead><tr><th>Name</th><th>Type</th><th>Dimensions</th><th>Area</th><th>Description</th></tr></thead>
                                    <tbody>
                                    @foreach($floor->commonAreas as $area)
                                        <tr><td>{{ $area->name }}</td><td>{{ $area->area_type }}</td><td>{{ $area->length && $area->width ? $area->length.' x '.$area->width : 'N/A' }}</td><td>{{ number_format((float) $area->area, 2).' '.$area->area_unit }}</td><td>{{ $area->description ?: 'N/A' }}</td></tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

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

@include('layouts.partials.footer')
