@extends('layouts.account-app') 
@section('content')
    <!-- Activities -->
<div class="card-box mb-4">
    <div class="d-flex justify-content-between">
        <div class="header">
            <h5 class="mb-1">Completed Zipcodes</h5>
            <small class="text-muted">Showing all Passed Zipcodes</small>
        </div>

    <!-- Filters -->
        <div class="actions">
            <a class="btn btn-sm btn-outline-dark" href="{{route('account.explore-map')}}">Explore on Map</a> 
        </div>
    </div>
       <!-- Table -->
    <div class="table table-responsive mt-3">
        
             <table class="table table-hover" id="passedZipTable">
                <thead class="">
                    <tr>
                        <th width="">Zipcode</th>
                        <th width="">Total Attempts</th>
                        <th width="">Total Distance (mi)</th>
                        <th width="">Highest Elevation (ft)</th>
                        <th width="">Highest Speed (mph)</th>
                        <th width="5%">Action</th>
                    </tr>
                </thead>
            </table>
       
       
    </div>
</div>

@endsection

@section('script')

<script>
$(document).ready(function () { 

    $('#activityDiv').hide();

    var table = $('#passedZipTable').DataTable({
        processing: true,
        serverSide: true,
        scrollY: false,
        scrollX: false,
        language: {
            processing: 
            '<div class="dt-loading"><span class="spinner"></span><span class="ms-2">Loading zipcodes...</span></div>',
            emptyTable: "No data available"
        },
        ajax: {
            url: '{{ route('account.segment.index') }}',
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        },
        order: [],
        columns: [
            { data: 'zip_code', name: 'zip_code' },
            { data: 'total_attempts', name: 'total_attempts' },
            { data: 'total_distance', name: 'total_distance' },
            { data: 'highest_elevation', name: 'highest_elevation' },
            { data: 'highest_speed', name: 'highest_speed' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

});
</script>
@endsection