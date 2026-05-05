@extends('layouts.account-app') 
@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css"> 
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
  /* ===== MAP ===== */
#map {
    width: 100%;
    height: 100vh; /* responsive height */
}

/* ===== MAP INFO BAR ===== */
#mapInfoBar {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 900;
}
/* ===== ZIP LABEL ===== */
.zip-text-label {
    font-size: 10px;
    font-weight: 600;
    color: #6b7280;
    background: transparent;
    border: none;
    box-shadow: none;
    pointer-events: none;
    text-shadow: 0 0 2px #fff;
}

   
/* ===== DRAWER ===== */
#drawerOverlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 1000;
    display: none;
}

.map-drawer {
    position: fixed;
    top: 0;
    right: -100%;
    width: 400px;
    max-width: 100%;
    height: 100%;
    background: #fff;
    z-index: 1001;
    transition: right 0.3s ease;
    box-shadow: -4px 0 10px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
}

.map-drawer.open {
    right: 0;
}

/* ===== MOBILE ===== */
@media (max-width: 768px) {
    .map-drawer {
        width: 100%;
    }

    #map {
        height: 100vh;
    }
}

</style>
    <!-- Activities -->
<div class="card-box mb-4">
    <h5>Leaderboard</h5>
    <small class="text-muted">All rides synced from Strava</small>

    <!-- Filters -->
    <div class="row g-2 mt-3">

        <div class="col-12 col-md-3">
            <select class="form-select filter" id="sportFilter">
                <option value="">All Sports</option>
                @foreach($sportTypes as $type)
                <option value="{{$type}}">{{$type}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3">
            <select id="country" <select class="form-select filter"> 
                <option value="">Country</option>
                @foreach($countries as $key=> $county)
                <option value="{{$key}}">{{$county}}</option>
                @endforeach
            </select>
        </div>
          <div class="col-12 col-md-3">
             <select id="state" class="form-select">
                <option value="">State</option>
                @foreach($states as $state)
                <option value="{{$state}}">{{$state}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3">

            <input type="text" id="datepicker"  class="form-control filter" placeholder="Date range">
        </div>

    </div>
    <div class="row g-2 mt-3">

        <!-- My Rank -->
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="text-muted small">Total Athlets</div>
            <h4 class="fw-bold mb-0">{{$totalAthletes}}</h4>
        </div>

        <!-- My Best Time -->
        <div class="col-md-6">
            <div class="text-muted small">Total Zipcode</div>
            <h4 class="fw-bold mb-0"><span id="allCount">{{$allCount}}</span></h4>
        </div>
                                
                                
    </div>
    <!-- Table -->
    <div class="table-responsive mt-3">
        
        <table class="table table-hover" id="leaderboardTable">
                <thead class="">
                    <tr>
                        <th>Rank</th>
                        <th>Athlete</th>
                        <th>Total Distance</th>
                        <th>Total Passed</th>

                        <th></th>
                        <!-- <th>Action</th> -->
                    </tr>
                </thead>
        </table>
       
    </div>
</div>

@endsection

@section('script')
<!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Turf.js -->
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
<script>

$(document).ready(function () { 



    $('#activityDiv').hide();
    let startDate = '';
    let endDate = '';
    let name = '';

      var start = moment().startOf('year');
      var end = moment().endOf('year');
    $('#datepicker').daterangepicker({
        startDate: start,
        endDate: end,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                'month').endOf('month')],
            'This Month Last Year': [moment().subtract(1, 'year').startOf('month'), moment()
                .subtract(1, 'year').endOf('month')
            ],
            'This Year': [moment().startOf('year'), moment().endOf('year')],
            'Last Year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year')
                .endOf('year')
            ],
            // 'Current Financial Year': [moment().startOf('month'), moment().startOf('month')
            //     .subtract(1, 'days').add(1, 'year')
            // ],
            // 'Last Financial Year': [moment().month(3).startOf('month').subtract(1, 'year'), moment()
            //     .month(3).startOf('month').subtract(1, 'days')
            // ]
        }
    }, callbackSetDate);

    function callbackSetDate(start, end) {
        $('#datepicker').val(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
       
    }

    var table = $('#leaderboardTable').DataTable({
        processing: true,
        serverSide: true,
        info: false,       
        paging: false,   
        lengthChange: false,
        language: {
            processing:
            '<div class="dt-loading"><span class="spinner-border spinner-border-sm"></span><span class="ms-2">Loading leaderboard...</span></div>',
            emptyTable: "No data available"
        },

        ajax: {
            url: '{{ route("account.leaderboard") }}',
             data: function (d) {
                d.country = $('#country').val();
                d.state = $('#state').val();
                d.sport_type = $('#sport_type').val();
                // d.start_date = startDate;
                // d.end_date = endDate;
                d.start_date = $('#datepicker').data('daterangepicker').startDate.format('YYYY-MM-DD');
                d.end_date = $('#datepicker').data('daterangepicker').endDate.format('YYYY-MM-DD');
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        },
     
        order: [[2, 'desc']], 

        columns: [
            { data: 'rank', name: 'rank', orderable: false, searchable: false },

            { data: 'athlete_name', name: 'athlete.first_name' },
            { data: 'total_distance', name: 'total_distance' },

            { data: 'total_zips', name: 'total_zips' },
            // { data: 'percentage',name: 'percentage',orderable: false,
            //     render: function(data, type, row) {

            //         return `
            //         <div class="circular-progress" style="--value:${data}">
            //             <span>${data}%</span>
            //         </div>`;
            //     }
            // },

            { data: 'percentage', name: 'percentage' },

            // { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });
     $('#leaderboardTable').on('xhr.dt', function (e, settings, json) {
        if (json && json.allCount !== undefined) {
            $('#allCount').text(json.allCount);
        }
    });
        $('#sport_type').change(function () {
            table.draw();
        });
        $('#country').change(function () {
            table.draw();
        });
        $('#state').change(function () {
            table.draw();
        });
        $('#datepicker').change(function () {
            table.draw();
        });
        $("#leaderboardTable_filter").css({
            "display": "none"
        });
        $("#leaderboardTable_length").css({
            "display": "none"
        });

  
    $('#dateRange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });
      
   

});

</script>
@endsection

