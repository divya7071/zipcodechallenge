@extends('layouts.account-app') 
@section('content')
 <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css"> 
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
#map {
    height:400px;
  }
  #activityMap {
    width: 100%;
    height: 100vh;
  }
.zip-label {
    text-align: center;
    color: #fc4c02;
    font-weight: bold;
    font-size: 14px;
    background: transparent;
}

.zip-text-label {
    font-size: 10px;
    font-weight: 600;
    color: #6b7280;          /* muted gray */
    background: transparent;
    border: none;
    box-shadow: none;
    padding: 0;
    pointer-events: none;
    text-shadow: 0 0 2px #ffffff;
}
.zip-label {
    text-align: center;
}

.zip-label span {
    font-size: 11px;
    font-weight: bold;
    color: #2d3436;
    background: rgba(255,255,255,0.7);
    padding: 2px 4px;
    border-radius: 3px;
    white-space: nowrap;
}
#mapWrapper {
    position: relative;
}

#mapLoadingOverlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.75);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 999;
    display: none;
}
</style>
    @if(!auth('athlete')->user()->activities()->count()&& auth('athlete')->user()->is_syncing==1)
    <div class="loader-container">
        <svg viewBox="0 0 400 100" class="path-svg">
            <path id="journeyPath" d="M10 50 Q 100 10, 200 50 T 390 50" />
            <circle id="progressDot" r="5" cx="10" cy="50" />
        </svg>

        <div class="status">
            <div id="step-title">Starting...</div>
            <div id="step-sub">Preparing your journey</div>
        </div>
    </div>
    @endif
    <!-- Metrics -->
<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <div class="metric-card border-blue">
            <div class="fs-6 pb-2">ZIP CODES EXPLORED</div>
            <h4>{{$totalZips}}</h4>
            <small>across {{ $summary['totalZips'] }} ZIP codes this week</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card border-orange">
            <div class="fs-6 pb-2">ACTIVITY COUNT</div>
            <h4>{{auth('athlete')->user()->activities->count()}}</h4>
            <small>total rides logged</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card border-green">
            <div class="fs-6 pb-2">THIS WEEK</div>
            <h4>{{ $summary['totalZips'] }}</h4>
            <small>ZIP codes this week</small>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card border-purple">
            <div class="fs-6 pb-2">FAVORITE ZIP</div>
            <h4>{{$summary['favouriteZip']?$summary['favouriteZip']->zip_code:''}}</h4>
            <small>{{$summary['favouriteZip']?$summary['favouriteZip']->total_visits:''}} visits</small>
        </div>
    </div>

</div>

<!-- Activities -->
<div class="card-box mb-4">
    <h5>Activities</h5>
    <small class="text-muted">All rides synced from Strava</small>

    <!-- Filters -->
    <div class="row g-2 mt-3 filters">

        <div class="col-12 col-md-3">
            <select class="form-select filter" id="sportFilter">
                <option value="">All Sports</option>
                @foreach($sportTypes as $type)
                <option value="{{$type}}">{{$type}}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-3">
            <input type="text" id="datepicker"  class="form-control filter" placeholder="Date range">
        </div>

        <div class="col-12 col-md-4">
            <input type="text" id="nameSearch" class="form-control filter" placeholder="Activity name">
        </div>

        <div class="col-12 col-md-2 d-flex gap-2">
            <button class="btn btn-strava w-100 filter">Filter</button>
            <button class="btn btn-outline-secondary w-100" id="reset">Reset</button>
        </div>

    </div>

    <!-- Table -->
    <div class="row">
    <div class="table-responsive mt-3">
        <table class="table table-hover" id="activitiesTable">
            <thead>
                <tr>
                    <th>Sport</th>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Passed ZIPs</th>
                    <th>Moving Time</th>
                    <th>Distance</th>
                    <th>Elevation</th>
                    <th width="10%">Action</th>
                </tr>
            </thead>
            <tbody>
                
            </tbody>
        </table>
    </div>
    </div>
</div>
<div class="card-box mb-4">
       <div class="d-flex justify-content-between mb-4">
        <div>
            <h5 class="mb-0">Your Map</h5>
            <small class="text-muted">Showing all zipcodes in map</small>
        </div>
    </div>
      <div id="mapWrapper">
        <div id="map"></div>

        <!-- Overlay INSIDE wrapper -->
        <div id="mapLoadingOverlay">
            <div class="loader"></div>
            <div class="text">Loading map data...</div>
        </div>
    </div>
</div>
<!-- Bottom Section -->
<div class="row g-3">

    <div class="col-12 col-lg-6">
        <div class="card-box">
            <div class="d-flex justify-content-between">
                <h5>Top ZIP codes</h5>
                <a href="{{route('account.passed-zips')}}"" class="small-text text-warning">View all →</a>
            </div>

            <table class="table mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ZIP</th>
                        <th>Visits</th>
                        <th>Distance</th>
                        <th>Top Speed</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($zipcodeRanks))
                        @foreach($zipcodeRanks as $key=>$zipcodeRank)
                        <tr>
                            <td><span class="rank-badge rank-1">{{$key+1}}</span></td>
                            <td class="fw-semibold">{{$zipcodeRank->zip_code}}</td>
                            <td>{{$zipcodeRank->total_attempts}}</td>
                            <td>{{$zipcodeRank->total_distance}}</td>
                            <td>{{$zipcodeRank->highest_speed}} mph</td>
                        </tr>
                        @endforeach
                        @else   
                    <tr>
                        <td colspan="5" class="text-center text-muted">No data</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card-box">
            <h5>Weekly ZIP activity</h5>
            <small class="text-muted">ZIP codes touched per day</small>
            <div class="text-muted mb-3">
                {{ $weeklyData['start']->format('M j') }} – {{ $weeklyData['end']->format('M j, Y') }}
            </div>
            <div class="mt-3 text-center text-muted" style="height:200px;">
                    <canvas id="zipChart" height="100"></canvas>
            </div>

            <div class="mt-3 text-end text-danger">
                    {{ $weeklyData['bestDay'] }} · {{ $weeklyData['max'] }} ZIPs
            </div>
        </div>
    </div>

</div>
<div id="mapDrawer" class="map-drawer">
    <button id="closeMapDrawer" class="drawer-close">
        <i class="bi bi-x-lg"></i>
    </button>
    <div class="row">
        <div id="mapInfoBar" class="w-100 z-3"></div>
        <div class="col-lg-12">
            <div id="activityMap"></div>
        </div>
    </div>
    
</div>

@endsection
@section('script')

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://unpkg.com/laravel-echo@2.3.4/dist/echo.iife.js"></script>
<script>
$(document).ready(function () { 
    const ctx = document.getElementById('zipChart');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($weeklyData['labels']),
            datasets: [{
                data: @json($weeklyData['counts']),
                borderWidth: 0,
                borderRadius: 6
            }]
        },
        options: {
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
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

   var table =$('#activitiesTable').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 5,
        drawCallback: function(settings) {
            var info = this.api().page.info();
            var showing = info.end; 
            var totalFiltered = info.recordsDisplay;
            var total = info.recordsTotal;

            $('#activityCountText').text(
                `All rides synced from Strava — showing ${showing} of ${total}`
            );
            $('#activitiesTable_processing').hide();
        },
        language: {
          processing: 
          '<div class="dt-loading"><span class="spinner"></span><span class="ms-2">Loading the activities...</span></div>'
        },
        ajax: {
            url: '{{ route('account.activity.index') }}',
            data: function(d) {
                d.start_date = $('#datepicker').data('daterangepicker').startDate.format('YYYY-MM-DD');
                d.end_date = $('#datepicker').data('daterangepicker').endDate.format('YYYY-MM-DD');
                d.sport_type = $('#sportFilter').val();
                d.name =$('#nameSearch').val();    
            },
             error: function (xhr) {
                console.error(xhr.responseText);
            }
        },
       
        order: [],
        columns: [
            { data: 'sport_type', orderable: false, searchable: false },
            { data: 'date' },
            { data: 'name' },
            { data: 'zips', orderable: false, searchable: false },
            { data: 'moving_time', className: 'text-center' },
            { data: 'distance', className: 'text-center' },
            { data: 'elevation', className: 'text-center' },
            { data: 'action', orderable: false, searchable: false }
        ]
       
    });
    $("#activitiesTable_info").css({
            "display": "none"
        });
    $("#activitiesTable_filter").css({
            "display": "none"
    });
    $("#activitiesTable_length").css({
        "display": "none"
    });

    $('.filter').change(function() {
        table.draw();
    });

    $('#nameSearch').on('keyup', function () {
        name=$(this).val();
        table.draw();
   });
    $('#reset').click(function() {
        $('.filters').find(':input').val('');
        $('.filters select').prop('selectedIndex', 0);
        $('.filters input:checkbox, .filters input:radio').prop('checked', false);
        table.draw();
    });
    
          //////////////// Start page loader/////////////////////
    let reloadHandled = false;

     

    // $('#activitiesTable').on('draw.dt', function () {

    //     let rowCount = table.rows().count();

    //     if (rowCount === 0 && !reloadHandled) {
    //         reloadHandled = true;

    //         startLoader();

    //         setTimeout(() => {
    //             location.reload();
    //         }, 5000);

    //     } else if (rowCount > 0) {
    //         $('.loader-container').hide();
    //     }
    // });

    /////////////////////////////////
    const $drawer  = $('#mapDrawer');
    const $overlay = $('#drawerOverlay');

    $(document).on('click', '.open-map-drawer', function (e) {
        e.preventDefault();
        e.stopPropagation();   
        const activityId = $(this).data('id');
        let activityResponse='';
         $.ajax({
            url: "{{ route('account.activity.map') }}",
            type: "POST",
            data: {
                activityId: activityId,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
              activityResponse=response.activity;
             $('#mapInfoBar').html(response.html);    
              loadActivityMap(activityResponse);
            }
        });
        $drawer.addClass('open');
        $overlay.show();
     
    });
    $(document).on('click','#closeMapDrawer, #drawerOverlay', function () {
    
            closeDrawer();
        });

    function closeDrawer() {
        $drawer.removeClass('open');
        $overlay.hide();
    }

    
 
//////////////Map code bigins///////////////////


    let activityMap = null;
    let routeLine = null;
    let zipLayer = null;
    let routeGeoJSON;
    let allZipData = null;
    const loadedZipIds = new Set();
    const zipMarkers = new Map();   
    const zipPolygons = new Map();  
    let zipSaved = false;
    let HAS_PASSED_ZIPS ='';
    let ACTIVITY_ID ='';
    const zipListDiv = document.getElementById('zipList');
    const zipCounttDiv = document.getElementById('zipCount');

    function loadActivityMap(activityResponse) {

        var mapData = JSON.parse(activityResponse.activity_map.map); 
        var summaryPolyline = mapData.summary_polyline;
      //  console.log("Your Polyline:", summaryPolyline);
        let ACTIVITY_ID = activityResponse.id;
        HAS_PASSED_ZIPS =activityResponse.passed_zips ;
        const ACTIVITY_POLYLINE =summaryPolyline;
   
          if (activityMap) {
              activityMap.remove();
              activityMap = null;
          }

        activityMap = L.map('activityMap').setView([39.8, -98.6], 5);
   
        
        // L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
        //     maxZoom: 20,
        //     attribution: '© OpenStreetMap contributors, CyclOSM'
        // }).addTo(map);
         L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }
   ).addTo(activityMap);


       zipLayer = L.geoJSON(null, {
            style: {
                color: '#9aa3ad',      
                weight: 1,
                fillOpacity: 0        
            }
        }).addTo(activityMap);


        const routeCoords = decodePolyline(ACTIVITY_POLYLINE);
        routeGeoJSON = turf.lineString(routeCoords);
        const leafletCoords = routeCoords.map(([lng, lat]) => [lat, lng]);
        const startPoint = leafletCoords[0];
        const endPoint   = leafletCoords[leafletCoords.length - 1];
        // White outline
        L.polyline(leafletCoords, {
            color: '#ffffff',
            weight: 6
        }).addTo(activityMap);

        // Orange route (Strava style)
        const routeLine = L.polyline(leafletCoords, {
            color: '#fc4c02',
            weight: 2,
            opacity: 0.95
        }).addTo(activityMap);
           const startIcon = L.icon({
            iconUrl: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
            iconSize: [32, 32],
            iconAnchor: [16, 32]
        });
 
        const endIcon = L.icon({
            iconUrl: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
            iconSize: [32, 32],
            iconAnchor: [16, 32]
        });
    
 
        L.marker(startPoint, { icon: startIcon })
            .addTo(activityMap)
            .bindTooltip("START", {
                permanent: true,     
                direction: "top",  
                offset: [0, -40],   
                className: "start-label"
            });


        L.marker(endPoint, { icon: endIcon })
            .addTo(activityMap)
             .bindTooltip("END", {
                permanent: true,     
                direction: "top",  
                offset: [0, -40],       
                className: "end-label"
            });
        activityMap.fitBounds(routeLine.getBounds(), { padding: [20, 20] });
      
        fetch('{{ asset("geo/us_zipcodes.json") }}')
            .then(res => res.json())
            .then(data => {
                allZipData = data;
                loadZipsInView();
            })
            .catch(console.error);

       
    }

      
function loadZipsInView(){
    if(!allZipData || !routeGeoJSON) return;
    const mapBBox = turf.bboxPolygon([
        map.getBounds().getWest(),
        map.getBounds().getSouth(),
        map.getBounds().getEast(),
        map.getBounds().getNorth()
    ]);

    allZipData.features.forEach(zip => {
        const zipId = zip.properties.postcode;
        if(!zip.geometry || loadedZipIds.has(zipId)) return;

        if(turf.booleanIntersects(mapBBox, zip)){
            zipLayer.addData(zip);
            loadedZipIds.add(zipId);
        }
    });

    findPassedZips();
}

map.on('moveend zoomend', loadZipsInView);


function zipPinIcon(zip) {
    return L.divIcon({
        className: 'zip-pin-icon',
        iconSize: [36, 46],
        iconAnchor: [18, 44],
        html: `
        <svg class="zip-pin-svg" width="36" height="46" viewBox="0 0 36 46">
            <path class="zip-pin-body"
                  d="M18 1C8.8 1 1 8.8 1 18
                     c0 12.3 17 27 17 27
                     s17-14.7 17-27C35 8.8 27.2 1 18 1z"
                  fill="#fc4c02"/>

            <circle cx="18" cy="17" r="12" fill="#ffffff"/>

            <text x="18" y="18"
                  text-anchor="middle"
                  dominant-baseline="middle"
                  font-size="8"
                  font-weight="700"
                  font-family="Arial, sans-serif"
                  fill="#1f2937"
                  stroke="#ffffff"
                  stroke-width="0.9"
                  paint-order="stroke">
                ${zip}
            </text>
        </svg>`
    });
}
function findPassedZips(){
 
    if(!routeGeoJSON) return;

    const passedZips = new Set();
    const zoom = map.getZoom();
  //  console.log(zipLayer);
    zipLayer.eachLayer(layer => {
        if(!layer.feature?.geometry) return;

        const zip = layer.feature.properties.postcode;
        zipPolygons.set(zip, layer);

        const isIntersected = turf.booleanIntersects(layer.feature, routeGeoJSON);

        if (isIntersected) {
            passedZips.add(zip);

            // intersected ZIP border
            layer.setStyle({
                color: '#2563eb',
                weight: 1,
                fillOpacity: 0
            });

            if (!zipMarkers.has(zip)) {
                const [lng, lat] = turf.centroid(layer.feature).geometry.coordinates;
                const marker = L.marker([lat, lng], {
                    icon: zipPinIcon(zip),
                    riseOnHover: true
                }).addTo(activityMap);

                zipMarkers.set(zip, marker);

                marker.on('mouseover', () => highlightZip(zip));
                marker.on('mouseout', () => resetZip(zip));
                layer.on('mouseover', () => highlightZip(zip));
                layer.on('mouseout', () => resetZip(zip));
            }

        } else {
            //  NON-INTERSECTED ZIP LABEL
            layer.setStyle({
                color: '#9ca3af',
                weight: 1,
                fillOpacity: 0
            });

            // show label only at higher zoom
            if (zoom >= 11 && !layer._zipLabel) {
                layer._zipLabel = L.tooltip({
                    permanent: true,
                    direction: 'center',
                    className: 'zip-text-label',
                    opacity: 0.9
                })
                .setContent(zip)
                .setLatLng(turf.centroid(layer.feature).geometry.coordinates.reverse())
                .addTo(activityMap);
            }

            if (zoom < 11 && layer._zipLabel) {
                activityMap.removeLayer(layer._zipLabel);
                layer._zipLabel = null;
            }
        }
    });

    const zipArray = [...passedZips];
    $('#zipList').html(zipArray.join(', '));
    zipListDiv.innerHTML = zipArray.join(', ');
    zipCounttDiv.innerHTML = "ZIPs Passed: " + zipArray.length;
    document.getElementById('zipCountDiv').style.display = 'block';

   
}
// function findPassedZips(){
 
//     if(!routeGeoJSON) return;

//     const passedZips = new Set();
//     const zoom = map.getZoom();
//   //  console.log(zipLayer);
//     zipLayer.eachLayer(layer => {
//         if(!layer.feature?.geometry) return;

//         const zip = layer.feature.properties.postcode;
//         zipPolygons.set(zip, layer);

//         const isIntersected = turf.booleanIntersects(layer.feature, routeGeoJSON);

//         if (isIntersected) {
//             passedZips.add(zip);

//             // intersected ZIP border
//             layer.setStyle({
//                 color: '#2563eb',
//                 weight: 1,
//                 fillOpacity: 0
//             });

//             if (!zipMarkers.has(zip)) {
//                 const [lng, lat] = turf.centroid(layer.feature).geometry.coordinates;
//                 const marker = L.marker([lat, lng], {
//                     icon: zipPinIcon(zip),
//                     riseOnHover: true
//                 }).addTo(activityMap);

//                 zipMarkers.set(zip, marker);

//                 marker.on('mouseover', () => highlightZip(zip));
//                 marker.on('mouseout', () => resetZip(zip));
//                 layer.on('mouseover', () => highlightZip(zip));
//                 layer.on('mouseout', () => resetZip(zip));
//             }

//         } else {
//             //  NON-INTERSECTED ZIP LABEL
//             layer.setStyle({
//                 color: '#9ca3af',
//                 weight: 1,
//                 fillOpacity: 0
//             });

//             // show label only at higher zoom
//             if (zoom >= 11 && !layer._zipLabel) {
//                 layer._zipLabel = L.tooltip({
//                     permanent: true,
//                     direction: 'center',
//                     className: 'zip-text-label',
//                     opacity: 0.9
//                 })
//                 .setContent(zip)
//                 .setLatLng(turf.centroid(layer.feature).geometry.coordinates.reverse())
//                 .addTo(activityMap);
//             }

//             if (zoom < 11 && layer._zipLabel) {
//                 map.removeLayer(layer._zipLabel);
//                 layer._zipLabel = null;
//             }
//         }
//     });

//     const zipArray = [...passedZips];
//     $('#zipList').html(zipArray.join(', '));
//     zipListDiv.innerHTML = zipArray.join(', ');
//     zipCounttDiv.innerHTML = "ZIPs Passed: " + zipArray.length;
//     document.getElementById('zipCountDiv').style.display = 'block';

//     savePassedZips(zipArray);
// }


function highlightZip(zip) {
    const layer = zipPolygons.get(zip);
    const marker = zipMarkers.get(zip);

    if (layer) {
        layer.setStyle({
            color: '#1e3a8a', 
            weight: 3,
            fillOpacity: 0
        });
        layer.bringToFront();
    }

    if (marker) {
        marker.getElement()?.classList.add('zip-pin-hover');
    }
}



function resetZip(zip) {
    const layer = zipPolygons.get(zip);
    const marker = zipMarkers.get(zip);

    if (layer) {
        layer.setStyle({
            color: '#2563eb',
            weight: 2,
            fillOpacity: 0
        });
    }

    if (marker) {
        marker.getElement()?.classList.remove('zip-pin-hover');
    }
}


});
function decodePolyline(str) {
    let index = 0, lat = 0, lng = 0, coordinates = [];

    while (index < str.length) {
        let result = 1, shift = 0, b;
        do {
            b = str.charCodeAt(index++) - 63 - 1;
            result += b << shift;
            shift += 5;
        } while (b >= 0x1f);
        lat += (result & 1) ? ~(result >> 1) : (result >> 1);

        result = 1; shift = 0;
        do {
            b = str.charCodeAt(index++) - 63 - 1;
            result += b << shift;
            shift += 5;
        } while (b >= 0x1f);
        lng += (result & 1) ? ~(result >> 1) : (result >> 1);

        coordinates.push([lng * 1e-5, lat * 1e-5]);
    }
    return coordinates;
}
///////////////////////

/* =======================
   GLOBAL VARIABLES
======================= */


let routeGeoJSON1;
let allZipData1 = null;
const loadedZipIds1 = new Set();
const zipMarkers = new Map();   // zip → marker
const zipPolygons = new Map();  // zip → polygon
let zipSaved1 = false;

const zipListDiv1 = document.getElementById('zipList');
const zipCounttDiv1 = document.getElementById('zipCount');


function getActivityPopup(activity) {
     return `
        <div class="activity-popup-content">
            <strong>${activity.name}</strong><br>
            <small>
                 <i class="fa-solid fa-bicycle me-1"></i> ${activity.type}<br>
                 <i class="fa-solid fa-ruler-horizontal me-1"></i> ${activity.distance}<br>
                  <i class="fa-regular fa-clock me-1"></i> ${activity.duration}<br>
                 ${activity.passedZips ? ` <i class="fa-solid fa-location-dot me-1 text-danger"></i> ${activity.passedZips}<br>` : ``}
                <i class="fa-regular fa-calendar me-1"></i>  ${activity.date}
            </small>
        </div>
    `;
}

/* =======================
   LOAD STRAVA ROUTE
======================= */
var polylines=[];
let activityPolylines = {};
let selectedPolyline = null;
function highlightRoute(activityId) {

    Object.values(activityPolylines).forEach(polyline => {
        polyline.setStyle({
            color: '#fc4c02',
            weight: 2,
            opacity: 0.95
        });
        polyline.closePopup();
    });

    const selected = activityPolylines[activityId];
    if (!selected) return;

    selected.setStyle({
        color: '#005eff',    
        weight: 2,
        opacity: 1
    });

    selected.bringToFront();
    selected.openPopup(selected.getBounds().getCenter());
}


$(document).on('click','.highlightRoute', function () {
     $('.highlightRoute').removeClass('active');
    $(this).addClass('active');
    var activityId=$(this).data('id');
        if (selectedPolyline) {
        selectedPolyline.setStyle({
            color: '#fc4c02',
            weight: 2,
            opacity: 0.95
        });
    }

    const polyline = activityPolylines[activityId];
    if (!polyline) return;

    // Highlight current route
    polyline.setStyle({
        color: '#8c00ff',
        weight: 4,
        opacity: 1
    });
    
    activitiesMap.fitBounds(polyline.getBounds());

    selectedPolyline = polyline;
    selectedPolyline.bringToFront();
    selectedPolyline.openPopup(selectedPolyline.getBounds().getCenter());

    setTimeout(() => {
        const popupEl = selectedPolyline.getPopup()?.getElement();
        if (popupEl) {
            popupEl.classList.add('highlighted-popup');
        }
    }, 50);
 
   });   
   $('#openactivityMap').on('click',function(){
    
            $.ajax({
            url: "{{ route('account.activity.polylines') }}",
            type: "GET",
            success: function (response) {

                const polylines = response.polylines;
            //  console.log(polylines);
            $('#activityList').html(response.activityListHtml)
                polylines.forEach(item => {
                    const encoded = item.summary_polyline;

                    const routeCoords1 = decodePolyline(encoded);
                        routeGeoJSON = turf.lineString(routeCoords1);
                        const leafletCoordsActivity = routeCoords1.map(([lng, lat]) => [lat, lng]);

                        // White outline
                        L.polyline(leafletCoordsActivity, {
                            color: '#ffffff',
                            weight: 6
                        }).addTo(activitiesMap);

                        // Orange route (Strava style)
                        const poly =L.polyline(leafletCoordsActivity, {
                            color: '#fc4c02',
                            weight: 2,
                            opacity: 0.95,
                            interactive: true
                        }).addTo(activitiesMap);
                        poly.bindPopup(getActivityPopup(item), {
                            closeButton: false,
                            offset: [0, -8],
                            className: 'activity-popup'
                        });
                        activityPolylines[item.activityId] = poly;
                        poly.on('click', () => highlightRoute(item.activityId));
                        activitiesMap.fitBounds(poly.getBounds(), { padding: [20, 20] });
                        });
                }
            });
        $('#activityDiv').toggle();
   
//////////////////////////////

});
  
</script>

<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://unpkg.com/laravel-echo@2.3.4/dist/echo.iife.js"></script>

 <script>
//////////////// Start page loader/////////////////////

// startLoader();

// function startLoader() {

//     const steps = [
//         { title: "Syncing activities", sub: "Talking to Strava..." },
//         { title: "Fetching the ride location", sub: "Fetching the ride location..." },
//         { title: "Syncing activity photos", sub: "Talking to Strava..." },
//         { title: "Mapping zipcodes", sub: "Mapping the zipcode with route..." },
//     ];

//     const stepTitle = document.getElementById("step-title");
//     const stepSub = document.getElementById("step-sub");
//     const dot = document.getElementById("progressDot");
//     const path = document.getElementById("journeyPath");

//     const pathLength = path.getTotalLength();
//     let currentStep = 0;

//     const totalDuration = 5000; 
//     const stepDuration = totalDuration / steps.length; 

//     function updateStep() {
//         if (currentStep >= steps.length) return;

//         const progress = currentStep / (steps.length - 1);
//         const point = path.getPointAtLength(progress * pathLength);

//         dot.setAttribute("cx", point.x);
//         dot.setAttribute("cy", point.y);

//         stepTitle.textContent = steps[currentStep].title;
//         stepSub.textContent = steps[currentStep].sub;

//         stepTitle.classList.add("pulse");

//         setTimeout(() => {
//             stepTitle.classList.remove("pulse");
//         }, 500);

//         currentStep++;
//     }

//     const interval = setInterval(updateStep, stepDuration);
//     updateStep();
   
//     setTimeout(() => {
//         clearInterval(interval);
//         location.reload();
//     }, totalDuration);
// }

  let waitingForEvent = false;
  let currentStep = 0;
    const EchoClass = window.Echo?.default || window.Echo;
       let eventReceived = localStorage.getItem('activity_fetch_completed') === '1';
        window.Echo = new EchoClass({
            broadcaster: 'pusher',
            key: "{{ config('broadcasting.connections.pusher.key') }}",
            cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
            forceTLS: true
       });
      
        window.Echo.connector.pusher.connection.bind('state_change', function(states) {
            console.log('State:', states.current);
        });
        const userId = {{ auth('athlete')->id() }};
        window.Echo.private('user.' + userId)
            .listen('.ActivityFetchCompleted', (e) => {
        console.log('Update received:', e);

        localStorage.setItem('activity_fetch_completed', '1'); 
     
        if (waitingForEvent) {
            waitingForEvent = false;
            currentStep++;
            updateStep();
        }
    });

    document.addEventListener("DOMContentLoaded", function () {
        const path = document.getElementById("journeyPath");
        const dot = document.getElementById("progressDot");
        const stepTitle = document.getElementById("step-title");
        const stepSub = document.getElementById("step-sub");
        if (!path || !dot) {
            console.error("SVG elements not found");
            return;
        }
        const pathLength = path.getTotalLength();

        const steps = [
            { title: "Syncing activities", sub: "Talking to Strava...", waitForEvent: true },
            { title: "Fetching the ride location", sub: "Fetching the ride location" },
            { title: "Syncing activity photos", sub: "Talking to Strava..." },
            { title: "Mapping zipcodes", sub: "Mapping the zipcode with route..." },
        ];

        
      
        function updateStep() {
         
             if (currentStep >= steps.length) {
                clearInterval(interval);
                localStorage.removeItem('activity_fetch_completed'); // reset
                 setTimeout(() => {
                     window.location.reload();
                     }, 1000);
                return;
            }       
            const step = steps[currentStep];

            const progress = currentStep / (steps.length - 1);
            const point = path.getPointAtLength(progress * pathLength);

            dot.setAttribute("cx", point.x);
            dot.setAttribute("cy", point.y);

            stepTitle.textContent = step.title;
            stepSub.textContent = step.sub;

            stepTitle.classList.add("pulse");
            setTimeout(() => stepTitle.classList.remove("pulse"), 800);

      
            if (step.waitForEvent) {
               
                if (eventReceived) {
                    currentStep++;
                    return updateStep();
                }

                waitingForEvent = true;
                stepSub.textContent = "Waiting for server response...";
                return;
            }

            currentStep++;
        }

        const interval = setInterval(() => {
            if (!waitingForEvent) {
                updateStep();
            }
        }, 2000);
        updateStep();
        
    });

</script>  
<script>

const PASSED_ZIPS = @json($passedZips);

let loadedZipIds = new Set();
let loadedBounds = new Set();
let globalBounds = L.latLngBounds();

let isLoading = false;
let hasFitted = false;
let selectedLayer = null;
let FAVOURITE_ZIP = null;

const map = L.map('map').setView([39.8283, -98.5795], 6);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19
}).addTo(map);
//   L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
//             maxZoom: 20,
//             attribution: '© OpenStreetMap contributors, CyclOSM'
//         }).addTo(map);
let zipLabelsLayer = L.layerGroup().addTo(map);

const popup = L.popup({
    closeButton: false,
    autoClose: false
});

// --------------------
// GEOJSON LAYER
// --------------------
let zipLayer = L.geoJSON(null, {
    style: function (feature) {

        const zip = feature.properties.postcode;
        const count = PASSED_ZIPS[zip] || 0;
        const isPassed = count > 0;
        const isFav = zip == FAVOURITE_ZIP;

        return {
            fillColor: isPassed ? '#fc5200' : '#dfe6e9',
            fillOpacity: isPassed ? Math.min(0.3 + count * 0.05, 0.8) : 0.2,
            color: isPassed ? '#636e72' : '#0b0108',
            weight: isFav ? 2 : 1
        };
    },

    onEachFeature: function (feature, layer) {

        const zip = feature.properties.postcode;
        const count = PASSED_ZIPS[zip] || 0;
        const isPassed = count > 0;
        const isFav = zip == FAVOURITE_ZIP;

        // --------------------
        // POPUP
        // --------------------
        layer.on('mousemove', function (e) {
            popup
                .setLatLng(e.latlng)
                .setContent(`
                    <b>ZIP:</b> ${zip}<br>
                    <b>Visits:</b> ${count}
                `)
                .openOn(map);
        });

        layer.on('mouseout', function () {
            map.closePopup(popup);
        });
        
          layer.on('click', function () {

            // reset previous
            if (selectedLayer) {
                zipLayer.resetStyle(selectedLayer);
            }

            // set new
            selectedLayer = layer;

            layer.setStyle(highlightStyle());

            // bring to front (important)
            layer.bringToFront();

            // optional: zoom to feature
            map.fitBounds(layer.getBounds(), {
                padding: [20, 20],
                maxZoom: 10
            });
        });
          layer.on('mouseout', function () {
                map.closePopup(popup);
            });
        // --------------------
        // LABEL (FIXED CENTER USING TURF)
        // --------------------
        if (isPassed) {

            const centroid = turf.centroid(feature);
            const [lng, lat] = centroid.geometry.coordinates;

            const label = L.marker([lat, lng], {
                interactive: false,
                icon: L.divIcon({
                    className: 'zip-label',
                    html: `<span>${isFav ? '⭐ ' : ''}${zip}</span>`
                })
            });

            zipLabelsLayer.addLayer(label);
        }
    }
}).addTo(map);


function loadPassedZips() {
    showMapLoader();
    $.ajax({
        url: "{{route('account.load-map-passed-zips')}}",
        type: "GET",
        data: { passedZips: PASSED_ZIPS },
        success: function (data) {

            FAVOURITE_ZIP = data.favouriteZip;

            let favBounds = null;

            zipLayer.addData(data.features);

            data.features.forEach(f => {

                const id = f.properties.postcode;
                loadedZipIds.add(id);

                const bounds = L.geoJSON(f).getBounds();

               console.log(id);
               console.log(FAVOURITE_ZIP);
                if (id == FAVOURITE_ZIP) {
                    favBounds = bounds;
                }

                globalBounds.extend(bounds);
            });

          
            if (favBounds) {
                map.fitBounds(favBounds, {
                    padding: [40, 40],
                    maxZoom: 11
                });

            } else if (globalBounds.isValid()) {

                // fallback to all passed zips
                map.fitBounds(globalBounds, {
                    padding: [40, 40],
                    maxZoom: 10
                });
            }

            hasFitted = true;
            loadZipsInView();
        },
         complete: function () {
            //  hideMapLoader();
          if(!FAVOURITE_ZIP)
                showMapLoader();
            else
                hideMapLoader();
         }
    });
}
// --------------------
// STEP 2: LOAD OTHERS
// --------------------

let hasFittedBounds = false; 
function loadZipsInView() {

    if (isLoading) return;

    const bounds = map.getBounds();
    const key = getBoundsKey(bounds);

    if (loadedBounds.has(key)) return;

    loadedBounds.add(key);
    isLoading = true;

    $.ajax({
        url: "{{route('account.zips-in-view')}}",
        type: "GET",
        data: {
            minLng: bounds.getWest(),
            minLat: bounds.getSouth(),
            maxLng: bounds.getEast(),
            maxLat: bounds.getNorth(),
            excludeZips: PASSED_ZIPS
        },
        success: function (data) {

            const newFeatures = data.features.filter(f => {
                const id = f.properties.postcode;

                if (loadedZipIds.has(id)) return false;

                loadedZipIds.add(id);
                return true;
            });

            if (newFeatures.length > 0) {
                zipLayer.addData(newFeatures);
                const layers = zipLayer.getLayers();
                if (!FAVOURITE_ZIP && !hasFittedBounds) {
                    if (layers.length > 0) {
                        const firstLayer = layers[0];
                        const bounds = firstLayer.getBounds();

                        map.fitBounds(bounds, {
                            padding: [30, 30]
                        });

                        hasFittedBounds = true; 
                    }
                }
            }
            hideMapLoader();
            isLoading = false;
        },
        error: function () {
            hideMapLoader();
            isLoading = false;
        }
    });
}

// --------------------
// EVENTS
// --------------------
let debounceTimer;

map.on('moveend', function () {
    clearTimeout(debounceTimer);

    debounceTimer = setTimeout(() => {
       loadZipsInView();
    }, 300);
});

// --------------------
// INIT
// --------------------
loadPassedZips();

// --------------------
// HELPERS
// --------------------
function getBoundsKey(bounds) {
    const precision = 1;

    return [
        bounds.getSouth().toFixed(precision),
        bounds.getWest().toFixed(precision),
        bounds.getNorth().toFixed(precision),
        bounds.getEast().toFixed(precision)
    ].join(',');
}
function highlightStyle() {
    return {
        fillColor: '#00b894',
        fillOpacity: 0.15,  
        color: '#00b894',   
        weight: 3,
        opacity: 0.9,       
        dashArray: '5, 5'   
    };
}
function showMapLoader() {
    $('#mapLoadingOverlay').css('display', 'flex');
}

function hideMapLoader() {
    $('#mapLoadingOverlay').hide();
}
</script>
@endsection


