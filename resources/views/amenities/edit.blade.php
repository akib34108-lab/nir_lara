@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Edit Amenity</h4>
                <form action="{{ route('amenities.update', $amenity) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @include('amenities._form')
                    <button type="submit" class="btn btn-primary mr-2">Update Amenity</button>
                    <a href="{{ route('amenities.show', $amenity) }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@include('layouts.partials.footer')
