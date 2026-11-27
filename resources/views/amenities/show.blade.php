@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('amenities.index') }}">Amenities &amp; Facilities</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $amenity->name }}</li>
            </ol>
        </nav>
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
                    <div>
                        <h4 class="card-title mb-2">
                            @if($amenity->icon)<i class="{{ $amenity->icon }} mr-2"></i>@endif
                            {{ $amenity->name }}
                        </h4>
                        <span class="badge badge-info">{{ $amenity->category }}</span>
                        <span class="badge badge-{{ $amenity->statusBadgeColor() }}">{{ $amenity->statusLabel() }}</span>
                    </div>
                    <div>
                        <a href="{{ route('amenities.index') }}" class="btn btn-light mr-2"><i class="ti-arrow-left"></i> Back</a>
                        @can('amenities-edit')
                            <a href="{{ route('amenities.edit', $amenity) }}" class="btn btn-primary">Edit Amenity</a>
                        @endcan
                    </div>
                </div>

                <h5 class="card-title mt-4">Description</h5>
                <p>{{ $amenity->description ?: 'No description available.' }}</p>

                <h5 class="card-title mt-4">Assigned Buildings</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Building</th><th>Site</th><th>Quantity</th><th>Notes</th></tr></thead>
                        <tbody>
                            @forelse($amenity->buildings as $building)
                                <tr>
                                    <td><a href="{{ route('buildings.show', $building) }}">{{ $building->name }} ({{ $building->code }})</a></td>
                                    <td>{{ $building->site?->project_name ?? 'N/A' }}</td>
                                    <td>{{ $building->pivot->quantity ?? 'N/A' }}</td>
                                    <td>{{ $building->pivot->notes ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">This amenity is not assigned to any buildings.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('layouts.partials.footer')
