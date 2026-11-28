@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <h4 class="card-title mb-2">Floor Plans</h4>
                    @can('floor-plans-create')
                        <a href="{{ route('floor-plans.create') }}" class="btn btn-primary mb-2">
                            <i class="ti-plus"></i> Upload Floor Plan
                        </a>
                    @endcan
                </div>

                <form method="GET" action="{{ route('floor-plans.index') }}" class="mb-4">
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-3">
                            <label for="search">Search title</label>
                            <input id="search" name="search" type="text" class="form-control"
                                   value="{{ request('search') }}" placeholder="Floor plan title">
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
                            <label for="building_id">Building</label>
                            <select id="building_id" name="building_id" class="form-control">
                                <option value="">All buildings</option>
                                @foreach($buildings as $building)
                                    <option value="{{ $building->id }}" {{ (string) request('building_id') === (string) $building->id ? 'selected' : '' }}>
                                        {{ $building->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <label for="floor_id">Floor</label>
                            <select id="floor_id" name="floor_id" class="form-control">
                                <option value="">All floors</option>
                                @foreach($floors as $floor)
                                    <option value="{{ $floor->id }}" {{ (string) request('floor_id') === (string) $floor->id ? 'selected' : '' }}>
                                        {{ $floor->building?->name }} - {{ $floor->name }} ({{ $floor->floor_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-2 d-flex">
                            <button type="submit" class="btn btn-primary mr-2">Filter</button>
                            <a href="{{ route('floor-plans.index') }}" class="btn btn-light">Clear</a>
                        </div>
                    </div>
                </form>

                @include('floor-plans._table', ['floorPlans' => $floorPlans])
                <div class="mt-3 d-flex justify-content-center">{{ $floorPlans->links() }}</div>
            </div>
        </div>
    </div>

@include('layouts.partials.footer')
