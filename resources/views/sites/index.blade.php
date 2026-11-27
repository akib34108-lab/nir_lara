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
@endphp
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">{{ $errors->first() }}</div>
                        @endif

                        <div class="row mb-3 align-items-center">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center">

                                    <h4 class="card-title mb-0">Sites List</h4>

                                    <div class="d-flex align-items-center">
                                        <form method="GET" id="siteFilterForm" class="d-flex align-items-center mb-0 mr-2">
                                            <input type="text"
                                                name="search"
                                                id="siteSearch"
                                                value="{{ request('search') }}"
                                                class="form-control"
                                                placeholder="Search code or name"
                                                style="width: 280px;">
                                        </form>

                                        @can('sites-create')
                                            <a href="{{ route('sites.create') }}" class="btn btn-primary btn-fw">
                                                <i class="ti-plus"></i> Add Site
                                            </a>
                                        @endcan
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Site Code</th>
                                        <th>Project Name</th>
                                        <th>Location</th>
                                        <th>Land Area</th>
                                        <th>Status</th>
                                        <th>Planned Completion</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sites as $site)
                                        <tr>
                                            <td>{{ $site->project_code }}</td>
                                            <td>{{ $site->project_name }}</td>
                                            <td>
                                                {{ collect([$site->area, $site->city, $site->district])->filter()->implode(', ') ?: 'N/A' }}
                                            </td>
                                            <td>
                                                {{ number_format((float) $site->land_area, 2) }}
                                                {{ $landAreaUnits[$site->land_area_unit] ?? '' }}
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $statusColors[$site->project_status] ?? 'secondary' }}">
                                                    {{ $siteStatuses[$site->project_status] ?? 'Unknown' }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $site->planned_completion_date
                                                    ? \Carbon\Carbon::parse($site->planned_completion_date)->format('d M Y')
                                                    : 'N/A' }}
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-light btn-sm" type="button"
                                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                            aria-label="Actions for {{ $site->project_name }}">
                                                        <i class="ti-more-alt"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        @can('sites-view')
                                                            <a href="{{ route('sites.show', $site->id) }}" class="dropdown-item">
                                                                <i class="ti-eye mr-2"></i> View
                                                            </a>
                                                        @endcan
                                                        @can('sites-edit')
                                                            <a href="{{ route('sites.edit', $site->id) }}" class="dropdown-item">
                                                                <i class="ti-pencil mr-2"></i> Edit
                                                            </a>
                                                        @endcan
                                                        @can('sites-delete')
                                                            <form action="{{ route('sites.destroy', $site->id) }}" method="POST"
                                                                onsubmit="return confirm('Delete ' + '{{ $site->project_name}}?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="dropdown-item text-danger">
                                                                    <i class="ti-trash mr-2"></i> Delete
                                                                </button>
                                                            </form>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No sites found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 d-flex justify-content-center">
                            {{ $sites->withQueryString()->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@push('scripts')
    <script>
        $(document).ready(function () {
            let searchTimer;
            $('#materialSearch').on('blur', function () {
            $('#materialFilterForm').submit();
            });
        });
    </script>
@endpush
@include('layouts.partials.footer')
