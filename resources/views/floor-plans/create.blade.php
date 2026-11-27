@include('layouts.partials.header')
@include('layouts.partials.navbar')
@include('layouts.partials.sidebar')
<div class="main-panel">
    <div class="content-wrapper">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('floor-plans.index') }}">Floor Plans</a></li>
                <li class="breadcrumb-item active" aria-current="page">Upload</li>
            </ol>
        </nav>
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
</div>
@include('layouts.partials.footer')
