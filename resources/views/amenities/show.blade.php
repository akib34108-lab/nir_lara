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

        <div class="card">
            <div class="card-body">
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

@include('layouts.partials.footer')
