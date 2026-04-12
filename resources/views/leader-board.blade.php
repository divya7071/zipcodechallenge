@extends('layouts.app') 
   
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<main>
    
    <!-- breadcrumb-area -->
    <section class="breadcrumb-area d-flex align-items-center" style="background-image:url({{ url('front/img/bg/bdrc-bg.jpg')}})">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-12 col-lg-12">
                    <div class="breadcrumb-wrap text-left">
                        <div class="breadcrumb-title">
                            <h2>Leaderboard</h2>    
                            <div class="breadcrumb-wrap">
                        
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Leaderboard </li>
                            </ol>
                        </nav>
                    </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
    <!-- breadcrumb-area-end -->
        <!-- faq-area -->
    <section id="faq" class="faq-area pt-120 pb-120">             
        <div class="container">   
            <div class="row">
                <div class="col-lg-12 col-md-6">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-body py-3">
                            <div class="row mb-3">

                                <div class="col-md-3">
                                    <div class="contact-field p-relative c-name mb-20">                                    
                                    <select id="sport_type" class="form-select">
                                        <option value="">All Sports</option>
                                        @foreach($sportTypes as $type)
                                        <option value="{{$type}}">{{$type}}</option>
                                        @endforeach
                                    </select>
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="contact-field p-relative c-name mb-20">          
                                    <select id="country" class="form-select">
                                        <option value="">Country</option>
                                        @foreach($countries as $key=> $county)
                                        <option value="{{$key}}">{{$county}}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="contact-field p-relative c-name mb-20">          
                                    <select id="state" class="form-select">
                                        <option value="">State</option>
                                        @foreach($states as $state)
                                        <option value="{{$state}}">{{$state}}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="contact-field p-relative c-name mb-20">          
                                    <input class="form-control" placeholder="Date Range" name="datepicker"
                                                            type="text" id="datepicker" style="height: calc(3.0em + 0.30rem + 2px) !important;">
                                    </div>
                                    <!-- <input type="text" id="dateRange" class="form-control" placeholder="Select date range"> -->
                                </div>
                                
                                
                                <!-- <div class="col-md-3">
                                    <button id="filterBtn" class="btn btn-primary">Filter</button>
                                    <button id="resetBtn" class="btn btn-secondary">Reset</button>
                                </div> -->

                            </div>
                            <div class="row text-center text-md-start align-items-center">

                                <!-- My Rank -->
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="text-muted small">Total Athlets</div>
                                    <h4 class="fw-bold mb-0">2</h4>
                                </div>

                                <!-- My Best Time -->
                                <div class="col-md-6">
                                    <div class="text-muted small">Total Zipcode</div>
                                    <h4 class="fw-bold mb-0"><span id="allCount">{{$allCount}}</span></h4>
                                </div>
                                                        
                                                        
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-lg-12 col-md-6">
                     <div class="table-responsive">
                        <table id="leaderboardTable" class="table align-middle table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Rank</th>
                                        <th>Athlete</th>
                                        <th>Total Passed</th>
                                        <th></th>
                                        <!-- <th>Action</th> -->
                                    </tr>
                                </thead>
                        </table>
               
                    </div>
                </div>
                
                
            </div>
        </div>
    </section>

</main>
     
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
    /*
    $('#dateRange').on('apply.daterangepicker', function(ev, picker) {

        startDate = picker.startDate.format('YYYY-MM-DD');
        endDate = picker.endDate.format('YYYY-MM-DD');

        $(this).val(
            picker.startDate.format('YYYY-MM-DD') +
            ' - ' +
            picker.endDate.format('YYYY-MM-DD')
        );

        table.draw();

    });

    $('#dateRange').on('cancel.daterangepicker', function() {

        $(this).val('');
        startDate = '';
        endDate = '';
        table.draw();

    });
    */
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
            url: '{{ route("leaderboard") }}',
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
