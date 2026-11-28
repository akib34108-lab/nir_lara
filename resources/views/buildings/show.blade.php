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
        @if($errors->any())
            <div class="alert alert-danger" role="alert">
                <strong>Please correct the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row mb-4 align-items-center">
            <div class="col-md-8">
                <div class="d-flex align-items-center flex-wrap">
                    <h3 class="mb-1 mr-3">{{ $building->name }}</h3>
                    <span class="badge badge-primary mr-2">{{ $building->code }}</span>
                    <span class="badge badge-{{ $building->statusBadgeColor() }}">{{ $building->statusLabel() }}</span>
                </div>
                <p class="text-muted mt-2 mb-0">
                    {{ $building->site?->project_name ?? 'Site unavailable' }}
                </p>
            </div>
            <div class="col-md-4 d-flex justify-content-md-end mt-3 mt-md-0">
                <a href="{{ route('buildings.index') }}" class="btn btn-light mr-2">
                    <i class="ti-arrow-left"></i> Back
                </a>
                @can('buildings-edit')
                    <a href="{{ route('buildings.edit', $building) }}" class="btn btn-primary">
                        <i class="ti-pencil"></i> Edit Building
                    </a>
                @endcan
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs mb-4" id="buildingDetailsTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="building-information-tab" data-toggle="tab"
                           href="#building-information" role="tab" aria-controls="building-information" aria-selected="true">
                            Building Information
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="building-floors-tab" data-toggle="tab"
                           href="#building-floors" role="tab" aria-controls="building-floors" aria-selected="false">
                            Floors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="building-amenities-tab" data-toggle="tab"
                           href="#building-amenities" role="tab" aria-controls="building-amenities" aria-selected="false">
                            Amenities &amp; Facilities
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="building-floor-plans-tab" data-toggle="tab"
                           href="#building-floor-plans" role="tab" aria-controls="building-floor-plans" aria-selected="false">
                            Floor Plans
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="buildingDetailsTabContent">
                    <div class="tab-pane fade show active" id="building-information" role="tabpanel"
                         aria-labelledby="building-information-tab">
                        <div class="row">
                            <div class="col-lg-6">
                                <h4 class="card-title">Building Information</h4>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr><th>Name</th><td>{{ $building->name }}</td></tr>
                                            <tr><th>Code</th><td>{{ $building->code }}</td></tr>
                                            <tr><th>Building Type</th><td>{{ $building->building_type }}</td></tr>
                                            <tr><th>Structure Type</th><td>{{ $building->structure_type }}</td></tr>
                                            <tr>
                                                <th>Status</th>
                                                <td><span class="badge badge-{{ $building->statusBadgeColor() }}">{{ $building->statusLabel() }}</span></td>
                                            </tr>
                                            <tr>
                                                <th style="white-space: nowrap;">Description</th>
                                                <td style="white-space: normal; overflow: visible; word-break: break-word;">
                                                    @if($building->description)
                                                        {!! nl2br(e($building->description)) !!}
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
                                <h4 class="card-title">Site Information</h4>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tbody>
                                            <tr><th>Site Name</th><td>{{ $building->site?->project_name ?? 'N/A' }}</td></tr>
                                            <tr><th>Site Code</th><td>{{ $building->site?->project_code ?? 'N/A' }}</td></tr>
                                            <tr><th>Address</th><td>{{ $building->site?->address ?? 'N/A' }}</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <h4 class="card-title mt-3">Building Configuration</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Total Floors</th>
                                        <th>Total Flats</th>
                                        <th>Basements</th>
                                        <th>Elevators</th>
                                        <th>Parking Capacity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ number_format($building->total_floors) }}</td>
                                        <td>{{ number_format($building->total_flats) }}</td>
                                        <td>{{ number_format($building->basement_count) }}</td>
                                        <td>{{ number_format($building->elevator_count) }}</td>
                                        <td>{{ number_format($building->parking_capacity) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <small class="text-muted"><i class="ti-calendar mr-1"></i>Created At</small>
                                <p class="font-weight-bold mb-0">{{ $building->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mt-3 mt-md-0">
                                <small class="text-muted"><i class="ti-reload mr-1"></i>Last Updated</small>
                                <p class="font-weight-bold mb-0">{{ $building->updated_at?->format('d M Y, h:i A') ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="building-floors" role="tabpanel" aria-labelledby="building-floors-tab">
                        @php
                            $aboveGroundFloorCount = $building->floors->where('floor_number', '>=', 0)->count();
                            $basementFloorCount = $building->floors->where('floor_number', '<', 0)->count();
                        @endphp
                        @if($aboveGroundFloorCount > $building->total_floors)
                            <div class="alert alert-warning" role="alert">
                                Configured total floors: {{ $building->total_floors }}. Existing floor records numbered 0 or above: {{ $aboveGroundFloorCount }}.
                            </div>
                        @endif
                        @if($basementFloorCount > $building->basement_count)
                            <div class="alert alert-warning" role="alert">
                                Configured basement count: {{ $building->basement_count }}. Existing basement floor records: {{ $basementFloorCount }}.
                            </div>
                        @endif
                        @can('floors-list')
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Floor Number</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Area</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($building->floors as $floor)
                                            <tr>
                                                <td>{{ $floor->floor_number }}</td>
                                                <td>{{ $floor->name }}</td>
                                                <td>{{ $floor->floorTypeLabel() }}</td>
                                                <td>
                                                    {{ $floor->floor_area !== null ? number_format((float) $floor->floor_area, 2).' '.$floor->floor_area_unit : 'N/A' }}
                                                </td>
                                                <td><span class="badge badge-{{ $floor->statusBadgeColor() }}">{{ $floor->statusLabel() }}</span></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-center">No floors have been added to this building.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            @can('floors-delete')
                                @foreach($building->floors as $floor)
                                    <div class="modal fade" id="deleteFloorModal{{ $floor->id }}" tabindex="-1"
                                         role="dialog" aria-labelledby="deleteFloorModalLabel{{ $floor->id }}" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteFloorModalLabel{{ $floor->id }}">Delete Floor</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">Delete <strong>{{ $floor->name }}</strong>?</div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('floors.destroy', $floor) }}" method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Delete Floor</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endcan
                        @else
                            <div class="alert alert-info">You do not have permission to view floor records.</div>
                        @endcan
                    </div>
                    <div class="tab-pane fade" id="building-amenities" role="tabpanel" aria-labelledby="building-amenities-tab">
                        @include('buildings.partials.amenities')
                    </div>
                    <div class="tab-pane fade" id="building-floor-plans" role="tabpanel" aria-labelledby="building-floor-plans-tab">
                        @can('floor-plans-list')
                            @include('floor-plans._table', ['floorPlans' => $building->floorPlans])
                        @else
                            <div class="alert alert-info">You do not have permission to view floor plans.</div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

@include('layouts.partials.footer')
