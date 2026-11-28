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
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <h4 class="card-title mb-2">Building List</h4>
                    @can('buildings-create')
                        <a href="{{ route('buildings.create') }}" class="btn btn-primary mb-2">
                            <i class="ti-plus"></i> Add Building
                        </a>
                    @endcan
                </div>

                <form method="GET" action="{{ route('buildings.index') }}" class="mb-4">
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-3">
                            <label for="search">Search name or code</label>
                            <input id="search" name="search" type="text" class="form-control"
                                   value="{{ request('search') }}" placeholder="Building name or code">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="site_id">Site</label>
                            <select id="site_id" name="site_id" class="form-control">
                                <option value="">All sites</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}" {{ (string) request('site_id') === (string) $site->id ? 'selected' : '' }}>
                                        {{ $site->project_name }} ({{ $site->project_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="building_type">Type</label>
                            <select id="building_type" name="building_type" class="form-control">
                                <option value="">All types</option>
                                @foreach($buildingTypes as $type)
                                    <option value="{{ $type }}" {{ request('building_type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">All statuses</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2 d-flex">
                            <button type="submit" class="btn btn-primary mr-2">Filter</button>
                            <a href="{{ route('buildings.index') }}" class="btn btn-light">Clear</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Site Name</th>
                                <th>Type</th>
                                <th>Structure</th>
                                <th>Total Floors</th>
                                <th>Total Flats</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buildings as $building)
                                <tr>
                                    <td>{{ $building->code }}</td>
                                    <td>{{ $building->name }}</td>
                                    <td>{{ $building->site?->project_name ?? 'N/A' }}</td>
                                    <td>{{ $building->building_type }}</td>
                                    <td>{{ $building->structure_type }}</td>
                                    <td>{{ number_format($building->total_floors) }}</td>
                                    <td>{{ number_format($building->total_flats) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $building->statusBadgeColor() }}">
                                            {{ $building->statusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm" type="button"
                                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                    aria-label="Actions for {{ $building->name }}">
                                                <i class="ti-more-alt"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @can('buildings-view')
                                                    <a href="{{ route('buildings.show', $building) }}" class="dropdown-item">
                                                        <i class="ti-eye mr-2"></i> View
                                                    </a>
                                                @endcan
                                                @can('buildings-edit')
                                                    <a href="{{ route('buildings.edit', $building) }}" class="dropdown-item">
                                                        <i class="ti-pencil mr-2"></i> Edit
                                                    </a>
                                                @endcan
                                                @can('buildings-delete')
                                                    <button type="button" class="dropdown-item text-danger"
                                                            data-toggle="modal" data-target="#deleteBuildingModal{{ $building->id }}">
                                                        <i class="ti-trash mr-2"></i> Delete
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No buildings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-center">
                    {{ $buildings->withQueryString()->links() }}
                </div>

                @can('buildings-delete')
                    @foreach($buildings as $building)
                        <div class="modal fade" id="deleteBuildingModal{{ $building->id }}" tabindex="-1"
                             role="dialog" aria-labelledby="deleteBuildingModalLabel{{ $building->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteBuildingModalLabel{{ $building->id }}">Delete Building</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete <strong>{{ $building->name }}</strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                        <form action="{{ route('buildings.destroy', $building) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete Building</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endcan
            </div>
        </div>
    </div>

@include('layouts.partials.footer')
