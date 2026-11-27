<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Title</th>
                <th>Building</th>
                <th>Floor</th>
                <th>File Type</th>
                <th>Size</th>
                <th>Uploaded</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($floorPlans as $floorPlan)
                <tr>
                    <td>{{ $floorPlan->title }}</td>
                    <td>
                        @if($floorPlan->building)
                            <a href="{{ route('buildings.show', $floorPlan->building) }}">{{ $floorPlan->building->name }}</a>
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($floorPlan->floor)
                            <a href="{{ route('floors.show', $floorPlan->floor) }}">{{ $floorPlan->floor->name }} ({{ $floorPlan->floor->floor_number }})</a>
                        @else
                            N/A
                        @endif
                    </td>
                    <td><i class="{{ $floorPlan->fileTypeIcon() }} mr-1"></i>{{ $floorPlan->fileTypeLabel() }}</td>
                    <td>{{ $floorPlan->formattedFileSize() }}</td>
                    <td>{{ $floorPlan->created_at?->format('d M Y, h:i A') ?? 'N/A' }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" type="button" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false"
                                    aria-label="Actions for {{ $floorPlan->title }}">
                                <i class="ti-more-alt"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                @can('floor-plans-view')
                                    <a class="dropdown-item" href="{{ route('floor-plans.preview', $floorPlan) }}" target="_blank" rel="noopener noreferrer">
                                        <i class="ti-eye mr-2"></i> Preview
                                    </a>
                                    <a class="dropdown-item" href="{{ route('floor-plans.download', $floorPlan) }}">
                                        <i class="ti-download mr-2"></i> Download
                                    </a>
                                @endcan
                                @can('floor-plans-edit')
                                    <a class="dropdown-item" href="{{ route('floor-plans.edit', $floorPlan) }}">
                                        <i class="ti-pencil mr-2"></i> Edit
                                    </a>
                                @endcan
                                @can('floor-plans-delete')
                                    <button type="button" class="dropdown-item text-danger"
                                            data-toggle="modal" data-target="#deleteFloorPlanModal{{ $floorPlan->id }}">
                                        <i class="ti-trash mr-2"></i> Delete
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center">No floor plans found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@can('floor-plans-delete')
    @foreach($floorPlans as $floorPlan)
        <div class="modal fade" id="deleteFloorPlanModal{{ $floorPlan->id }}" tabindex="-1"
             role="dialog" aria-labelledby="deleteFloorPlanModalLabel{{ $floorPlan->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteFloorPlanModalLabel{{ $floorPlan->id }}">Delete Floor Plan</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">Delete <strong>{{ $floorPlan->title }}</strong> and its stored file?</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('floor-plans.destroy', $floorPlan) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete Floor Plan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endcan
