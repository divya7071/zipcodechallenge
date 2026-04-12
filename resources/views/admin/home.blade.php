@extends('admin.layouts.app')

@section('page-title', 'Strava')

@section('head')
    <link rel="stylesheet" href="{{ url('libs/dataTable/datatables.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ url('libs/range-slider/css/ion.rangeSlider.min.css') }}" type="text/css">

    <style>
        .my-3 {
            color: #172f3d !important;
        }
        .bg-package {
            background-color: rgba(155, 201, 221, 1) !important;
        }
    </style>
@endsection

@section('content')
  <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col-lg-12 col-md-12">
            <div class="card widget">
                <div class="card-header">
                    <h5 class="card-title"></h5>
                </div>
            </div>
            
        </div>

</div>
<div class="row row-cols-1 row-cols-md-3 g-4">
    
            <div class="col-md-6">
                <div class="card border-0">
                    <a href="{{ route('admin.athlete.index') }}">
                    <div class="card-body text-center">
                        <div class="display-5">
                            <i class="bi bi-person text-secondary"></i>
                        </div>
                        <h5 class="my-3">Athletes</h5>
                        <div class="text-muted">{{ $athleteCount }} Athletes</div>
                    </div>
                     </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card border-0">
                    <a href="{{ route('admin.athlete-activity.index') }}">
                    <div class="card-body text-center">
                        <div class="display-5">
                            <i class="bi bi-receipt text-warning"></i>
                        </div>
                        <h5 class="my-3">Athlete Activities</h5>
                        <div class="text-muted">{{$activityCount}} Activities</div>
                    </div>
                    </a>
                </div>
            </div>
   
</div>


@endsection

@section('script')
    <script src="{{ url('libs/dataTable/datatables.min.js') }}"></script>

@endsection