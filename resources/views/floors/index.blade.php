
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
                    <h4 class="card-title mb-2">Floor Management</h4>
                </div>

                {{-- Search and Filter Section --}}
                <form method="GET" action="{{ route('floors.index') }}" class="mb-4">

                    <div class="form-row align-items-end">

                        <div class="form-group col-md-2">
                            <label for="search">Search Floor</label>
                            <input id="search"
                                   name="search"
                                   type="text"
                                   class="form-control"
                                   value="{{ request('search') }}"
                                   placeholder="Floor name">
                        </div>

                        <div class="form-group col-md-2">
                            <label for="building_id">Building</label>
                            <select id="building_id" name="building_id" class="form-control">
                                <option value="">All buildings</option>
                                @foreach($buildings as $building)
                                    <option value="{{ $building->id }}"
                                        {{ (string) request('building_id') === (string) $building->id ? 'selected' : '' }}>
                                        {{ $building->name }} ({{ $building->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label for="site_id">Site</label>
                            <select id="site_id" name="site_id" class="form-control">
                                <option value="">All sites</option>
                                @foreach($sites as $site)
                                    <option value="{{ $site->id }}"
                                        {{ (string) request('site_id') === (string) $site->id ? 'selected' : '' }}>
                                        {{ $site->project_name }} ({{ $site->project_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label for="floor_type">Floor Type</label>
                            <select id="floor_type" name="floor_type" class="form-control">
                                <option value="">All types</option>
                                @foreach($floorTypes as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ request('floor_type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-2">
                            <label for="status">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="">All statuses</option>
                                @foreach($statuses as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ request('status') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="ti-filter"></i>
                            </button>

                            <a href="{{ route('floors.index') }}"
                               class="btn btn-light mr-2"
                               title="Clear Filters">
                                <i class="ti-reload"></i>
                            </a>

                            @can('floors-create')
                                <a href="{{ route('buildings.floors.create', $building) }}"
                                   class="btn btn-primary"
                                   title="Add Floor">
                                    <i class="ti-plus"></i>
                                </a>
                            @endcan
                        </div>

                    </div>
                </form>

                {{-- Floor Table --}}
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Floor Number</th>
                                <th>Name</th>
                                <th>Building</th>
                                <th>Site</th>
                                <th>Floor Type</th>
                                <th>Area</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($floors as $floor)
                                <tr>
                                    <td>{{ $floor->floor_number }}</td>

                                    <td>{{ $floor->name }}</td>

                                    <td>
                                        @if($floor->building)
                                            <a href="{{ route('buildings.show', $floor->building) }}">
                                                {{ $floor->building->name }}
                                            </a>
                                        @else
                                            N/A
                                        @endif
                                    </td>

                                    <td>{{ $floor->building?->site?->project_name ?? 'N/A' }}</td>

                                    <td>{{ $floor->floorTypeLabel() }}</td>

                                    <td>
                                        {{ $floor->floor_area !== null ? number_format((float) $floor->floor_area, 2).' '.$floor->floor_area_unit : 'N/A' }}
                                    </td>

                                    <td>
                                        <span class="badge badge-{{ $floor->statusBadgeColor() }}">
                                            {{ $floor->statusLabel() }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm"
                                                    type="button"
                                                    data-toggle="dropdown"
                                                    aria-haspopup="true"
                                                    aria-expanded="false"
                                                    aria-label="Actions for {{ $floor->name }}">
                                                <i class="ti-more-alt"></i>
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-right">

                                                @can('floors-view')
                                                    <a href="{{ route('floors.show', $floor) }}"
                                                       class="dropdown-item">
                                                        <i class="ti-eye mr-2"></i> View
                                                    </a>
                                                @endcan

                                                @can('floors-edit')
                                                    <a href="{{ route('floors.edit', $floor) }}"
                                                       class="dropdown-item">
                                                        <i class="ti-pencil mr-2"></i> Edit
                                                    </a>
                                                @endcan

                                                @can('floors-delete')
                                                    <button type="button"
                                                            class="dropdown-item text-danger"
                                                            data-toggle="modal"
                                                            data-target="#deleteFloorModal{{ $floor->id }}">
                                                        <i class="ti-trash mr-2"></i> Delete
                                                    </button>
                                                @endcan

                                            </div>
                                        </div>
                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        No floors found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-3 d-flex justify-content-center">
                    {{ $floors->withQueryString()->links() }}
                </div>

                {{-- Delete Confirmation Modals --}}
                @can('floors-delete')
                    @foreach($floors as $floor)

                        <div class="modal fade"
                             id="deleteFloorModal{{ $floor->id }}"
                             tabindex="-1"
                             role="dialog"
                             aria-labelledby="deleteFloorModalLabel{{ $floor->id }}"
                             aria-hidden="true">

                            <div class="modal-dialog" role="document">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title"
                                            id="deleteFloorModalLabel{{ $floor->id }}">
                                            Delete Floor
                                        </h5>

                                        <button type="button"
                                                class="close"
                                                data-dismiss="modal"
                                                aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        Delete <strong>{{ $floor->name }}</strong>?
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button"
                                                class="btn btn-light"
                                                data-dismiss="modal">
                                            Cancel
                                        </button>

                                        <form action="{{ route('floors.destroy', $floor) }}"
                                              method="POST">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-danger">
                                                Delete Floor
                                            </button>
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
