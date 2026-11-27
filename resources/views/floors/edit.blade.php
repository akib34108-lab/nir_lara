@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('sites.index') }}">Sites</a></li>
                @if($building->site)
                    <li class="breadcrumb-item"><a href="{{ route('sites.show', $building->site) }}">{{ $building->site->project_name }}</a></li>
                @endif
                <li class="breadcrumb-item"><a href="{{ route('buildings.show', $building) }}">{{ $building->name }}</a></li>
                <li class="breadcrumb-item"><a href="{{ route('buildings.show', $building) }}#building-floors">Floors</a></li>
                <li class="breadcrumb-item"><a href="{{ route('floors.show', $floor) }}">{{ $floor->name }}</a></li>
                <li class="breadcrumb-item active" aria-current="page">Configuration</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Floor Configuration</h4>
                <p class="card-description">Update configuration for {{ $floor->name }} in {{ $building->name }}.</p>
                @if($errors->any())
                    <div class="alert alert-danger">Please correct the highlighted fields.</div>
                @endif
                <form method="POST" action="{{ route('floors.update', $floor) }}">
                    @csrf
                    @method('PUT')
                    @include('floors._form')
                    <button type="submit" class="btn btn-primary">
                        <i class="ti-save"></i> Save Configuration
                    </button>
                    <a href="{{ route('floors.show', $floor) }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@include('layouts.partials.footer')
