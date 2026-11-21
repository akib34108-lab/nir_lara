@extends('layouts.master')
@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
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
                                        <td style="min-width: 150px;">
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar bg-success"
                                                        role="progressbar"
                                                        style="width: {{ $project->progress }}%;"
                                                        aria-valuenow="{{ $project->progress }}"
                                                        aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $project->start_date }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="ti-more-alt"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @can('projects-view')
                                                    <a href="{{ route('projects.show', $project->id) }}" class="dropdown-item">
                                                        <i class="ti-eye mr-2"></i> View
                                                    </a>
                                                    @endcan
                                                    @can('projects-edit')
                                                    <a href="{{ route('projects.edit', $project) }}" class="dropdown-item">
                                                        <i class="ti-pencil-alt mr-2"></i> Edit
                                                    </a>
                                                    @endcan
                                                    @can('projects-delete')
                                                    <form action="{{ route('projects.destroy', $project) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete {{ $project->project_name }}?')">
                                                            <i class="ti-trash mr-2"></i> Delete
                                                        </button>
                                                    </form>
                                                    @endcan
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $projects->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection