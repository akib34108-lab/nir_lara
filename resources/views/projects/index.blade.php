@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class='main-panel'>
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">

            <div class="card">
                <div class="card-body">

                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    {{-- Error Message --}}
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    {{-- Validation Error --}}
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <h4 class="card-title">Project List</h4>
                        </div>

                        <div class="col-md-4 justify-content-end d-flex">
                            @can('projects-create')
                                <a href="{{ route('projects.create') }}" class="btn btn-primary btn-fw">
                                    <i class="ti-plus"></i> Add Project
                                </a>
                            @endcan
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Progress</th>
                                    <th>Start Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($projects as $project)
                                    <tr>
                                        <td>{{ $project->project_code }}</td>
                                        <td>{{ $project->project_name }}</td>
                                        <td>{{ $project->site_address }}</td>

                                        {{-- Project Progress --}}
                                        <td style="min-width: 150px;">
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success"
                                                     role="progressbar"
                                                     style="width: {{ $project->progress ?? 0 }}%;"
                                                     aria-valuenow="{{ $project->progress ?? 0 }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ $project->progress ?? 0 }}%</small>
                                        </td>

                                        {{-- Start Date --}}
                                        <td>
                                            {{ $project->start_date
                                                ? \Carbon\Carbon::parse($project->start_date)->format('d M Y')
                                                : 'N/A' }}
                                        </td>

                                        {{-- Action Dropdown --}}
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm"
                                                        type="button"
                                                        data-toggle="dropdown"
                                                        aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <i class="ti-more-alt"></i>
                                                </button>

                                                <div class="dropdown-menu dropdown-menu-right">

                                                    @can('projects-edit')
                                                        <button type="button"
                                                                class="dropdown-item"
                                                                data-toggle="modal"
                                                                data-target="#milestoneModal{{ $project->id }}">
                                                            <i class="ti-stats-up mr-2"></i> Update Progress
                                                        </button>
                                                    @endcan

                                                    @can('projects-view')
                                                        <a href="{{ route('projects.show', $project->id) }}"
                                                           class="dropdown-item">
                                                            <i class="ti-eye mr-2"></i> View
                                                        </a>
                                                    @endcan

                                                    @can('projects-edit')
                                                        <a href="{{ route('projects.edit', $project->id) }}"
                                                           class="dropdown-item">
                                                            <i class="ti-pencil-alt mr-2"></i> Edit
                                                        </a>
                                                    @endcan

                                                    @can('projects-delete')
                                                        <form action="{{ route('projects.destroy', $project->id) }}"
                                                              method="POST"
                                                              onsubmit="return confirm('Are you sure you want to delete {{ addslashes($project->project_name) }}?')">
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
                                        <td colspan="6" class="text-center">No projects found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $projects->withQueryString()->links() }}
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>
@can('projects-edit')
    @foreach ($projects as $project)
        <div class="modal fade"
             id="milestoneModal{{ $project->id }}"
             tabindex="-1"
             role="dialog"
             aria-hidden="true">

            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            Project Milestones
                            <small class="d-block text-muted">{{ $project->project_name }}</small>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        @forelse($project->milestones as $milestone)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">

                                <div class="mr-2">
                                    <strong class="d-block">{{ $milestone->milestone_name }}</strong>
                                    <small class="text-muted">{{ $milestone->percentage }}%</small>

                                    @if($milestone->status === 'completed')
                                        <small class="d-block text-success">
                                            Completed
                                            @if($milestone->completed_date)
                                                - {{ \Carbon\Carbon::parse($milestone->completed_date)->format('d M Y') }}
                                            @endif
                                        </small>
                                    @else
                                        <small class="d-block text-warning">Pending</small>
                                    @endif
                                </div>

                                @if($milestone->status === 'completed')
                                    <button type="button" class="btn btn-success btn-sm" disabled>
                                        <i class="ti-check"></i>
                                    </button>
                                @else
                                    <form action="{{ route('projects.milestones.update', ['project' => $project->id, 'milestone' => $milestone->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Are you sure you want to complete this milestone?')">
                                        @csrf
                                        @method('PUT')

                                        <input type="hidden" name="status" value="completed">

                                        <button type="submit"
                                                class="btn btn-outline-success btn-sm"
                                                title="Mark as completed">
                                            <i class="ti-check"></i>
                                        </button>
                                    </form>
                                @endif

                            </div>
                        @empty
                            <div class="text-center text-muted py-3">
                                <i class="ti-info-alt"></i>
                                <p class="mb-0">No milestones found for this project.</p>
                            </div>
                        @endforelse

                        {{-- Current Project Progress --}}
                        <div class="mt-3">
                            <strong>Current Progress: {{ $project->progress ?? 0 }}%</strong>
                            <div class="progress mt-2" style="height: 8px;">
                                <div class="progress-bar bg-success"
                                     role="progressbar"
                                     style="width: {{ $project->progress ?? 0 }}%;">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>
    @endforeach
@endcan
@include('layouts.partials.footer')