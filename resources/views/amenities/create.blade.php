@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('amenities.index') }}">Amenities &amp; Facilities</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add Amenity</li>
            </ol>
        </nav>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Add Amenity</h4>
                <form action="{{ route('amenities.store') }}" method="POST">
                    @csrf
                    @include('amenities._form')
                    <button type="submit" class="btn btn-primary mr-2">Save Amenity</button>
                    <a href="{{ route('amenities.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@include('layouts.partials.footer')
