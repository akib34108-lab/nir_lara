@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
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
@include('layouts.partials.footer')
