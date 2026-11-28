@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('floor-plans.index') }}">Floor Plans</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit {{ $floorPlan->title }}</li>
            </ol>
        </nav>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Floor Plan</h4>
                @if($errors->any())
                    <div class="alert alert-danger">Please correct the highlighted fields.</div>
                @endif
                <form method="POST" action="{{ route('floor-plans.update', $floorPlan) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('floor-plans._form')
                    <button type="submit" class="btn btn-primary mr-2"><i class="ti-save"></i> Save Changes</button>
                    <a href="{{ route('floors.show', $selectedFloorId ?? $floorPlan->floor_id) }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@include('layouts.partials.footer')
