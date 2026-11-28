@if($building->amenities->isEmpty())
    <div class="alert alert-info">No amenities have been assigned to this building.</div>
@else
    @foreach($building->amenities->groupBy('category') as $category => $amenities)
        <h5 class="mt-4">{{ $category }}</h5>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Amenity</th>
                        <th>Status</th>
                        <th>Quantity</th>
                        <th>Notes</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($amenities as $amenity)
                        <tr>
                            <td>
                                @if($amenity->icon)<i class="{{ $amenity->icon }} mr-2"></i>@endif
                                <a href="{{ route('amenities.show', $amenity) }}">{{ $amenity->name }}</a>
                            </td>
                            <td><span class="badge badge-{{ $amenity->statusBadgeColor() }}">{{ $amenity->statusLabel() }}</span></td>
                            <td colspan="2">
                                @can('buildings-edit')
                                    <form action="{{ route('buildings.amenities.update', [$building, $amenity]) }}" method="POST"
                                          class="form-row align-items-center">
                                        @csrf
                                        @method('PUT')
                                        <div class="form-group col-md-3 mb-0">
                                            <input type="number" min="0" step="1" name="quantity" class="form-control form-control-sm"
                                                   aria-label="Quantity for {{ $amenity->name }}"
                                           value="{{ is_array(old('quantity')) ? old('quantity.'.$amenity->id, $amenity->pivot->quantity) : old('quantity', $amenity->pivot->quantity) }}" placeholder="Quantity">
                                        </div>
                                        <div class="form-group col-md-6 mb-0">
                                            <input type="text" name="notes" maxlength="255" class="form-control form-control-sm"
                                                   aria-label="Notes for {{ $amenity->name }}"
                                                   value="{{ is_array(old('notes')) ? old('notes.'.$amenity->id, $amenity->pivot->notes) : old('notes', $amenity->pivot->notes) }}" placeholder="Notes">
                                        </div>
                                    </form>
                                @else
                                    {{ $amenity->pivot->quantity ?? 'N/A' }}
                                    <span class="text-muted">{{ $amenity->pivot->notes }}</span>
                                @endcan
                            </td>
                            <td class="text-nowrap">
                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Update">
                                    <i class="ti-save"></i>
                                </button>

                                @can('buildings-edit')
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-toggle="modal"
                                            data-target="#removeBuildingAmenityModal{{ $building->id }}{{ $amenity->id }}"
                                            title="Remove">
                                        <i class="ti-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
@endif

@can('buildings-edit')
    @php($selectedAmenityIds = old('amenity_ids', []))
    <div class="card border mt-4">
        <div class="card-body">
            <h5 class="card-title">Assign Active Amenities</h5>
            @if($assignableAmenities->isEmpty())
                <p class="text-muted mb-0">There are no unassigned active amenities available.</p>
            @else
                <form action="{{ route('buildings.amenities.store', $building) }}" method="POST">
                    @csrf
                    @foreach($assignableAmenities as $category => $amenities)
                        <h6 class="mt-3">{{ $category }}</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead><tr><th>Amenity</th><th>Quantity</th><th>Notes</th></tr></thead>
                                <tbody>
                                    @foreach($amenities as $amenity)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <label class="form-check-label" for="amenity-{{ $amenity->id }}">
                                                        <input id="amenity-{{ $amenity->id }}" class="form-check-input"
                                                               type="checkbox" name="amenity_ids[]" value="{{ $amenity->id }}"
                                                               {{ is_array($selectedAmenityIds) && in_array($amenity->id, $selectedAmenityIds) ? 'checked' : '' }}>
                                                        @if($amenity->icon)<i class="{{ $amenity->icon }} mr-1"></i>@endif
                                                        {{ $amenity->name }}
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="1" name="quantity[{{ $amenity->id }}]"
                                                       class="form-control form-control-sm" aria-label="Quantity for {{ $amenity->name }}"
                                                       value="{{ old('quantity.'.$amenity->id) }}">
                                            </td>
                                            <td>
                                                <input type="text" maxlength="255" name="notes[{{ $amenity->id }}]"
                                                       class="form-control form-control-sm" aria-label="Notes for {{ $amenity->name }}"
                                                       value="{{ old('notes.'.$amenity->id) }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                    @error('amenity_ids')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                    @error('amenity_ids.*')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                    @error('quantity.*')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                    @error('notes.*')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                    <button type="submit" class="btn btn-primary">Assign Selected Amenities</button>
                </form>
            @endif
        </div>
    </div>

    @foreach($building->amenities as $amenity)
        <div class="modal fade" id="removeBuildingAmenityModal{{ $building->id }}{{ $amenity->id }}" tabindex="-1"
             role="dialog" aria-labelledby="removeBuildingAmenityModalLabel{{ $building->id }}{{ $amenity->id }}" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="removeBuildingAmenityModalLabel{{ $building->id }}{{ $amenity->id }}">Remove Amenity</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">Remove <strong>{{ $amenity->name }}</strong> from {{ $building->name }}?</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('buildings.amenities.destroy', [$building, $amenity]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Remove Amenity</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endcan
