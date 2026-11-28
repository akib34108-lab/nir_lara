@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
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
                        <i class="ti-save"></i> Update Configuration 
                    </button>
                    <a href="{{ route('floors.show', $floor) }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@include('layouts.partials.footer')
