@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Add Floor</h4>
                <p class="card-description">Add a floor to {{ $building->name }} ({{ $building->code }}).</p>
                @if($errors->any())
                    <div class="alert alert-danger">Please correct the highlighted fields.</div>
                @endif
                <form method="POST" action="{{ route('buildings.floors.store', $building) }}">
                    @csrf
                    @include('floors._form')
                    <button type="submit" class="btn btn-primary">
                        <i class="ti-save"></i> Save Floor
                    </button>
                    <a href="{{ route('buildings.show', $building) }}#building-floors" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@include('layouts.partials.footer')
