@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item">Property Management</li>
                <li class="breadcrumb-item active" aria-current="page">Amenities &amp; Facilities</li>
            </ol>
        </nav>

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
                    <h4 class="card-title mb-2">Amenities &amp; Facilities</h4>
                    @can('amenities-create')
                        <a href="{{ route('amenities.create') }}" class="btn btn-primary mb-2">
                            <i class="ti-plus"></i> Add Amenity
                        </a>
                    @endcan
                </div>

                <form method="GET" action="{{ route('amenities.index') }}" class="mb-4">
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-4">
                            <label for="search">Search name</label>
                            <input id="search" name="search" type="text" class="form-control"
                                   value="{{ request('search') }}" placeholder="Amenity name">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="category">Category</label>
                            <select id="category" name="category" class="form-control">
                                <option value="">All categories</option>
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}" {{ request('category') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
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
                            <a href="{{ route('amenities.index') }}" class="btn btn-light">Clear</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Buildings</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($amenities as $amenity)
                                <tr>
                                    <td>
                                        @if($amenity->icon)<i class="{{ $amenity->icon }} mr-2"></i>@endif
                                        {{ $amenity->name }}
                                    </td>
                                    <td>{{ $amenity->category }}</td>
                                    <td>{{ $amenity->buildings_count }}</td>
                                    <td><span class="badge badge-{{ $amenity->statusBadgeColor() }}">{{ $amenity->statusLabel() }}</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-light btn-sm" type="button" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false"
                                                    aria-label="Actions for {{ $amenity->name }}">
                                                <i class="ti-more-alt"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @can('amenities-view')
                                                    <a href="{{ route('amenities.show', $amenity) }}" class="dropdown-item"><i class="ti-eye mr-2"></i> View</a>
                                                @endcan
                                                @can('amenities-edit')
                                                    <a href="{{ route('amenities.edit', $amenity) }}" class="dropdown-item"><i class="ti-pencil mr-2"></i> Edit</a>
                                                @endcan
                                                @can('amenities-delete')
                                                    <button type="button" class="dropdown-item text-danger"
                                                            data-toggle="modal" data-target="#deleteAmenityModal{{ $amenity->id }}">
                                                        <i class="ti-trash mr-2"></i> Delete
                                                    </button>
                                                @endcan
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No amenities found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-center">{{ $amenities->links() }}</div>

                @can('amenities-delete')
                    @foreach($amenities as $amenity)
                        <div class="modal fade" id="deleteAmenityModal{{ $amenity->id }}" tabindex="-1"
                             role="dialog" aria-labelledby="deleteAmenityModalLabel{{ $amenity->id }}" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="deleteAmenityModalLabel{{ $amenity->id }}">Delete Amenity</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    </div>
                                    <div class="modal-body">Delete <strong>{{ $amenity->name }}</strong>? Assigned amenities cannot be deleted; set them inactive instead.</div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                                        <form action="{{ route('amenities.destroy', $amenity) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Delete Amenity</button>
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
</div>
@include('layouts.partials.footer')
