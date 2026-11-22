
@extends('layouts.master')

@section('content')

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

                    <div class="row">

                        <div class="col-md-8">
                            <h4 class="card-title">Project List</h4>
                        </div>

                        <div class="col-md-4 justify-content-end d-flex">

                            @can('projects-create')
                                <a href="{{ route('projects.create') }}"
                                   class="btn btn-primary btn-fw">

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
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach ($projects as $project)

                                    <tr>

                                        <td>{{ $project->project_code }}</td>

                                        <td>{{ $project->project_name }}</td>

                                        <td>{{ $project->site_address }}</td>

                                        {{-- Project Progress --}}
                                        <td style="min-width: 150px;">

                                            <div class="d-flex align-items-center">

                                                <div class="progress flex-grow-1"
                                                     style="height: 8px;">

                                                    <div class="progress-bar bg-success"
                                                         role="progressbar"
                                                         style="width: {{ $project->progress ?? 0 }}%;"
                                                         aria-valuenow="{{ $project->progress ?? 0 }}"
                                                         aria-valuemin="0"
                                                         aria-valuemax="100">
                                                    </div>

                                                </div>

                                            </div>

                                            <small class="text-muted">
                                                {{ $project->progress ?? 0 }}%
                                            </small>

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
                                                                class="dropdown-item updateProgressPopover"
                                                                data-project-id="{{ $project->id }}"
                                                                data-toggle="popover"
                                                                data-placement="left"
                                                                data-trigger="click"
                                                                data-html="true"
                                                                title="Update Project Progress">

                                                            <i class="ti-stats-up mr-2"></i>
                                                            Update Progress

                                                        </button>

                                                    @endcan

                                                    @can('projects-view')

                                                        <a href="{{ route('projects.show', $project->id) }}"
                                                           class="dropdown-item">

                                                            <i class="ti-eye mr-2"></i> View

                                                        </a>

                                                    @endcan

                                                    @can('projects-edit')

                                                        <a href="{{ route('projects.edit', $project) }}"
                                                           class="dropdown-item">

                                                            <i class="ti-pencil-alt mr-2"></i> Edit

                                                        </a>

                                                    @endcan

                                                    @can('projects-delete')

                                                        <form action="{{ route('projects.destroy', $project) }}"
                                                              method="POST">

                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="submit"
                                                                    class="dropdown-item text-danger"
                                                                    onclick="return confirm('Are you sure you want to delete {{ $project->project_name }}?')">

                                                                <i class="ti-trash mr-2"></i> Delete

                                                            </button>

                                                        </form>

                                                    @endcan

                                                </div>

                                            </div>


                                            {{-- ================================== --}}
                                            {{-- Milestone Popover Content --}}
                                            {{-- ================================== --}}

                                            @can('projects-edit')

                                                <div id="progressPopoverContent{{ $project->id }}"
                                                     style="display: none;">

                                                    <div style="min-width: 280px; max-width: 320px;">

                                                        <h6 class="mb-3">
                                                            Project Milestones
                                                        </h6>

                                                        {{-- Milestone List --}}
                                                        @forelse($project->milestones as $milestone)

                                                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">

                                                                <div class="mr-2">

                                                                    <strong class="d-block">
                                                                        {{ $milestone->milestone_name }}
                                                                    </strong>

                                                                    <small class="text-muted">
                                                                        {{ $milestone->percentage }}%
                                                                    </small>

                                                                    @if($milestone->status === 'completed')

                                                                        <small class="d-block text-success">

                                                                            Completed

                                                                            @if($milestone->completed_date)

                                                                                -
                                                                                {{ \Carbon\Carbon::parse($milestone->completed_date)->format('d M Y') }}

                                                                            @endif

                                                                        </small>

                                                                    @else

                                                                        <small class="d-block text-warning">
                                                                            Pending
                                                                        </small>

                                                                    @endif

                                                                </div>


                                                                {{-- Completed Button --}}
                                                                @if($milestone->status === 'completed')

                                                                    <button type="button"
                                                                            class="btn btn-success btn-sm"
                                                                            disabled>

                                                                        <i class="ti-check"></i>

                                                                    </button>

                                                                @else

                                                                    <form action="{{ route('projects.milestones.complete', [
                                                                        'project' => $project->id,
                                                                        'milestone' => $milestone->id
                                                                    ]) }}"
                                                                          method="POST">

                                                                        @csrf

                                                                        <button type="submit"
                                                                                class="btn btn-outline-success btn-sm"
                                                                                title="Mark as completed"
                                                                                onclick="return confirm('Are you sure you want to complete this milestone?')">

                                                                            <i class="ti-check"></i>

                                                                        </button>

                                                                    </form>

                                                                @endif

                                                            </div>

                                                        @empty

                                                            <div class="text-center text-muted py-3">

                                                                <i class="ti-info-alt"></i>

                                                                <p class="mb-0">
                                                                    No milestones found for this project.
                                                                </p>

                                                            </div>

                                                        @endforelse


                                                        {{-- Current Project Progress --}}
                                                        <div class="mt-3">

                                                            <strong>
                                                                Current Progress:
                                                                {{ $project->progress ?? 0 }}%
                                                            </strong>

                                                            <div class="progress mt-2"
                                                                 style="height: 8px;">

                                                                <div class="progress-bar bg-success"
                                                                     role="progressbar"
                                                                     style="width: {{ $project->progress ?? 0 }}%;">

                                                                </div>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>

                                            @endcan

                                        </td>

                                    </tr>

                                @endforeach

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

@endsection


@push('scripts')

<script>

    $(function () {

        // Initialize Progress Popover
        $('.updateProgressPopover').popover({

            container: 'body',
            html: true,
            sanitize: false,
            trigger: 'click',
            placement: 'left',

            content: function () {

                let projectId = $(this).data('project-id');

                return $('#progressPopoverContent' + projectId).html();

            }

        });


        // Close other popovers when opening another
        $('.updateProgressPopover').on('show.bs.popover', function () {

            $('.updateProgressPopover').not(this).popover('hide');

        });


        // Close popover when clicking outside
        $(document).on('click', function (event) {

            if (
                !$(event.target).closest(
                    '.updateProgressPopover, .popover'
                ).length
            ) {

                $('.updateProgressPopover').popover('hide');

            }

        });

    });

</script>

@endpush
