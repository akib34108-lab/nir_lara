@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Upload Floor Plan</h4>
                @if($errors->any())
                    <div class="alert alert-danger">Please correct the highlighted fields.</div>
                @endif
                <form method="POST" action="{{ route('floor-plans.store') }}" enctype="multipart/form-data">
                    @csrf
                    @include('floor-plans._form')
                    <button type="submit" class="btn btn-primary mr-2"><i class="ti-upload"></i> Upload Floor Plan</button>
                    <a href="{{ route('floor-plans.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
@include('layouts.partials.footer')
