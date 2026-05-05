@extends('layouts.account-app') 
@section('content')
<link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.css"/>
<link href="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.css" rel="stylesheet">
<style>

.activity-card {
    background: #fff;
    border-radius: 12px;
    /* padding: 16px; */
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,.06);
}
.stat-value {
    font-weight: 600;
    font-size: 18px;
}
.stat-label {
    font-size: 13px;
    color: #777;
}
.map-box {
    height: 260px;
    background: #e9ecef;
    border-radius: 8px;
    overflow: hidden;
}

    .map-placeholder {
    height: 260px;
    border-radius: 10px;
    background: #f2f2f2;
    overflow: hidden;
}
.photo-grid img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}
.media-thumb {
    position: relative;
    overflow: hidden;
    border-radius: 12px;
}

.media-thumb::after {
    content: '';
    display: block;
    padding-bottom: 100%; /* square */
}

/* Apply to both image and video */
.media-thumb img,
.media-thumb video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
}

.stats-table {
    font-size: 0.7rem;
    text-align: left;
}
.segments-box {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.segments-table thead th {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    border-bottom: 1px solid #eaeaea;
}

.segments-table tbody tr {
    transition: background 0.2s ease;
}

.segments-table tbody tr:hover {
    background: #f8f9fa;
}

.zip-cell {
    white-space: nowrap;      /* prevents line break */
}

.trophy-badge {
    display: inline-flex;     /* important */
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    margin-left: 6px;         /* space between zip & badge */
    border-radius: 50%;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    vertical-align: middle;   /* align properly */
}

/* Trophy stem */
.trophy-badge::after {
    content: "";
    position: absolute;
    bottom: -6px;
    width: 14px;
    height: 6px;
    background: inherit;
    border-radius: 0 0 6px 6px;
}

/* Gold */
.trophy-1 {
    background: #f7b500;
}

/* Silver */
.trophy-2 {
    background: #9e9e9e;
}

/* Bronze */
.trophy-3 {
    background: #cd7f32;
}
.segment-expanded-container {
    background: #f8f9fa;
    border-top: 1px solid #ddd;
}

.segment-graph,
.segment-map {
    height: 120px;
}
 #map {
    height: 400px;  
    width: 100%;
}    
.zip-pin-hover{
    animation: pulse 1s ease-out;
}

@keyframes pulse{
    0%{ transform:scale(1); }
    50%{ transform:scale(1.3); }
    100%{ transform:scale(1); }
} 

.zip-pin-hover{
    animation: zipPulse 1.5s ease-out;
}

@keyframes zipPulse{
    0% { transform: scale(1); }
    50% { transform: scale(1.4); }
    100% { transform: scale(1); }
}
.flyover-btn{
    position:absolute;
    bottom:12px;
    right:26px;
    background:#fc4c02;
    color:white;
    width:38px;
    height:38px;
    border-radius:6px;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    font-size:18px;
    z-index:10;
    box-shadow:0 2px 6px rgba(0,0,0,0.4);
}

</style>
<style>
    .metric-card {
        border-left: 0;
        text-align: center;
    }
    .metric-card .stat-value {
        font-size: 22px;
        margin-bottom: 3px;
    }

</style>
<style>
.route-wrapper {
    width: 100%;
}

/* Block */
.route-block {
    margin-bottom: 25px;
}

.route-header {
    font-weight: 600;
    margin-bottom: 12px;
}

/* Timeline row */
.route-line {
    display: flex;
    align-items: center;
    overflow-x: auto;
    padding-bottom: 10px;
}

/* Step */
.step {
    display: flex;
    align-items: center;
    position: relative;
}

/* Dot */
.dot {
    width: 14px;
    height: 14px;
    background: #3498db;
    border-radius: 50%;
    flex-shrink: 0;
}

/* Arrow line */
.step:not(:last-child)::after {
    content: "➝";
    font-size: 16px;
    margin: 0 12px;
    color: #999;
}

/* Zip label */
.zip {
    margin-left: 8px;
    font-size: 13px;
    white-space: nowrap;
}

/* Colors for direction */
.route-block.up .dot {
    background: #2ecc71;
}

.route-block.down .dot {
    background: #e74c3c;
}

.route-block.up .step:not(:last-child)::after {
    color: #2ecc71;
}

.route-block.down .step:not(:last-child)::after {
    color: #e74c3c;
}

</style>

<!-- Activities -->
<div class="card-box mb-4">
    <h5 class="mb-1">{{$activity->name}}</h5>
    <small class="text-muted"> @php
        $date = $activity->local_date
        @endphp

        @if ($date->isToday())
        Today at {{ $date->format('g:i A') }}
        @elseif ($date->isYesterday())
        Yesterday at {{ $date->format('g:i A') }}
        @else
        {{ $date->format('F j, Y \a\t g:i A') }}
        @endif</small>

    <div class="row mt-3">
        <div class="col-lg-12">
            <div class="row g-2 text-center mb-4">
                <div class="col-md-2 col-6">
                    <div class="metric-card bg-light">
                        <h4 class="stat-value text-success">{{number_format($activity->distance / 1609.344, 2)}} <small class="text-muted">mi</small></h4>
                        <div class="text-muted fs-6">Distance</div>
                    </div>
                </div>
                @php
                $seconds = $activity->moving_time;
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $secs = $seconds % 60;
                @endphp
                <div class="col-md-2 col-6 ">
                    <div class="metric-card bg-light">
                        <h4 class="stat-value text-warning">{{ gmdate('H:i:s',  $activity->moving_time) }}</h4>
                        <div class="text-muted fs-6">Moving Time</div>
                    </div>
                </div>

                <div class="col-md-2 col-6 ">
                    <div class="metric-card bg-light">
                    <h4 class="stat-value text-info">{{$activity->elevation }} <small class="text-muted">ft</small></h4>
                    <div class="text-muted fs-6">Elevation</div>
                    </div>
                </div>

                <div class="col-md-2 col-6 ">
                    <div class="metric-card bg-light">
                    <h4 class="stat-value text-primary">{{$activity->average_speed }} <small class="text-muted">mi/h</small></h4>
                    <div class="text-muted fs-6">Average Speed</div>
                    </div>
                </div>
                <div class="col-md-2 col-6 ">
                    <div class="metric-card bg-light">
                    <h4 class="stat-value text-dark">{{$activity->max_speed }} <small class="text-muted">mi/h</small></h4>
                    <div class="text-muted fs-6">Maximum Speed</div>
                    </div>
                </div>
                @php
                    $seconds = $activity->elapsed_time;
                    $hours = floor($seconds / 3600);
                    $minutes = floor(($seconds % 3600) / 60);
                    $secs = $seconds % 60;
                @endphp
                <div class="col-md-2 col-6 ">
                    <div class="metric-card bg-light">
                    <h4 class="stat-value text-danger">{{ gmdate('H:i:s',  $activity->elapsed_time) }}</h4>
                    <div class="text-muted fs-6">Elapsed Time</div>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-lg-12 d-none d-lg-block mb-4">
            <div class="row">
                <div class="col-lg-12 position-relative">
                    <div id="map"></div>
                    <button id="playBtn" class="flyover-btn"> ▶</button>

                </div>
            </div>

        </div>
        <div class="col-lg-12 d-none d-lg-block">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Passed Zip segments</h5>

                <button class="btn btn-sm btn-primary"
                        data-bs-toggle="modal"
                        data-bs-target="#routeModal">
                    View Route
                </button>
            </div>

            <table class="table table-responsive table-sm align-middle mb-0 segments-table">
                <thead>
                    <tr>
                        <th>Zip</th>
                        <th>Distance</th>
                        <th>Time</th>
                        <th>Speed</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($activity->passedZips->filter(fn($row) => !is_null($row->sort_order))->sortBy('sort_order') as $zipCode => $zip)

                <tr class="segment-row" data-zip="{{ $zip->zip_code }}" data-id="{{ $zip->id }}" style="cursor:pointer;">
                    <td class="zip-cell">
                        {{ $zip->zip_code }}

                        @if($zip->rank && $zip->rank <= 3)
                            <span class="trophy-badge trophy-{{ $zip->rank }}">
                            @if($zip->rank == 1)
                            PR
                            @else
                            {{ $zip->rank }}
                            @endif
                            </span>
                            @endif
                    </td>
                    <td>{{ number_format($zip->distance_mi,2) }} mi</td>
                    <td>{{ gmdate('H:i:s', $zip->moving_sec) }}</td>
                    <td>{{ number_format($zip->speed_mph,2) }} mph</td>

                </tr>

                @endforeach
                @foreach($activity->passedZips->filter(fn($row) => !is_null($row->sort_order_down))->sortBy('sort_order_down') as $zipCode => $zip)

                <tr class="segment-row" data-zip="{{ $zip->zip_code }}" data-id="{{ $zip->id }}" style="cursor:pointer;">
                    <td class="zip-cell">
                        {{ $zip->zip_code }}

                        @if($zip->rank && $zip->rank <= 3)
                            <span class="trophy-badge trophy-{{ $zip->rank }}">
                            @if($zip->rank == 1)
                            PR
                            @else
                            {{ $zip->rank }}
                            @endif
                            </span>
                            @endif
                    </td>
                    <td>{{ number_format($zip->distance_mi,2) }} mi</td>
                    <td>{{ gmdate('H:i:s', $zip->moving_sec) }}</td>
                    <td>{{ number_format($zip->speed_mph,2) }} mph</td>

                </tr>

                @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="routeModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Activity Route Breakdown</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="route-wrapper">

                    <!-- GOING OUT -->
                    <div class="route-block up mb-2">
                        <div class="route-header">⬆ Going Out</div>

                        <div class="route-line">
                            @foreach($upSegments as $row)
                               <div class="step">
                                    <div class="dot"></div>
                                    <div class="zip">{{ $row['zip'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- RETURNING -->
                    <div class="route-block down">
                        <div class="route-header">⬇ Returning</div>

                        <div class="route-line">
                            <!-- turn point -->
                            <div class="step">
                                <div class="dot"></div>
                                <div class="zip">{{ $turnZip }}</div>
                            </div>

                            @foreach($downSegments as $row)
                               <div class="step">
                                    <div class="dot"></div>
                                    <div class="zip">{{ $row['zip'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
                <!-- <div class="route-wrapper">

                    <div class="row mb-2">

                        
                        <div class="col-md-12">
                            <div class="route-block up">

                                <div class="route-header">
                                    ⬆ Going Out
                                </div>
                               
                                <div class="timeline up-line pt-2">
                                     @foreach($upSegments as $row)
                                        <span class="zip">{{ $row['zip'] }}</span>

                                        @if(!$loop->last)
                                            <span class="arrow">→</span>
                                        @endif
                                    @endforeach
                                                               
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="row mb-2">
                            <div class="col-md-12 mb-2">
                                <div class="route-block down">

                                    <div class="route-header">
                                        ⬇ Returning
                                    </div>
                                
                                    <div class="timeline down-line pt-2">
                                        <span class="zip">{{ $turnZip }}</span>
                                        <span class="arrow">→</span>
                                        @foreach($downSegments as $row)
                                            <span class="zip">{{ $row['zip'] }}</span>

                                            @if(!$loop->last)
                                                <span class="arrow">→</span>
                                            @endif
                                        @endforeach
                                                                
                                    </div>

                                </div>
                            </div>

                    </div>

                </div> -->
           
            </div>
                
        </div>

                    <!-- END -->
                

                </div>
                <!-- Start -->
                <!-- <p><strong>🚩 Start ZIP:</strong> {{ $startZip }}</p>

                <h6 class="text-warning">⬆️ Going Out</h6>
                <ul>
                    @foreach($upSegments as $row)
               
                        <li>
                            {{ $row['sort_order'] }}. 
                            {{ $row['zip'] }} |
                            {{ $row['distance_mi'] }} mi |
                            {{ gmdate('H:i:s', $row['elapsed_sec']) }} |
                            {{ $row['speed_mph'] }} mph
                        </li>
                    @endforeach
                </ul>

                <div class="text-center my-3">
                    <strong>🔁 Turnaround at {{ $turnZip }}</strong>
                </div>

                <h6 class="text-primary">⬇️ Returning</h6>
                <ul>
                    @foreach($downSegments as $row)
                        <li>
                            {{ $row['sort_order_down'] }}. 
                            {{ $row['zip'] }} |
                            {{ $row['distance_mi'] }} mi |
                            {{ gmdate('H:i:s', $row['elapsed_sec']) }} |
                            {{ $row['speed_mph'] }} mph
                        </li>
                    @endforeach
                </ul>

              
                <p><strong>🏁 End ZIP:</strong> {{ $endZip }}</p> -->

            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Turf.js -->
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
<script src="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.js"></script>
<script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script>
<script>

$(document).on('click', '.segment-row', function () {

    let row = $(this);
    let id = row.data('id');
    const zip = String(row.data('zip'));

    // Prevent double click while loading
    if (row.data('loading')) return;

    
     highlightZipWithRoute(zip);

    // If already open → close it
    if (row.next().hasClass('expanded-row')) {
        row.next().remove();
        return;
    }

    // Remove any open row
    $('.expanded-row').remove();

    // Mark as loading
    row.data('loading', true);

    $.ajax({
        url: '/segment-details/' + id,
        type: 'GET',
        success: function (response) {

            let newRow = `
                <tr class="expanded-row">
                    <td colspan="5">${response}</td>
                </tr>
            `;

            row.after(newRow);
        },
        complete: function () {
            // Unlock after request finishes
            row.data('loading', false);
        }
    });

});



</script>
<script>
let mediaItems = [];
let currentIndex = 0;

$(document).on("click", ".media-preview", function () {

    let activityId = $(this).data("activity");

    $.ajax({
        url: "/activity-media/" + activityId,
        type: "GET",
        success: function (response) {

            mediaItems = response;
            currentIndex = 0;

            showMedia();

            let modal = new bootstrap.Modal(document.getElementById('mediaModal'));
            modal.show();
        },
        error: function () {
            alert("Failed to load media.");
        }
    });

});


function showMedia() {

    let container = $("#modalContent");
    container.html("");

    let media = mediaItems[currentIndex];

    if (media.type == 1 && media.url) {
        container.html(`
            <img src="${media.url}" class="img-fluid rounded">
        `);
    }

    if (media.type == 2 && media.video_url) {
        container.html(`
            <video controls autoplay class="img-fluid rounded">
                <source src="${media.video_url}" type="video/mp4">
            </video>
        `);
    }
}


// Next Button
$("#modalNext").on("click", function () {
    currentIndex = (currentIndex + 1) % mediaItems.length;
    showMedia();
});

// Prev Button
$("#modalPrev").on("click", function () {
    currentIndex = (currentIndex - 1 + mediaItems.length) % mediaItems.length;
    showMedia();
});

function savePassedZips(zips){
    if (zipSaved || HAS_PASSED_ZIPS) return;

    zipSaved = true;
    const SAVE_ZIPS_URL ="{{ route('account.activity.savePassedZips', ['activity' => '__ID__']) }}";
    const url = SAVE_ZIPS_URL.replace('__ID__', ACTIVITY_ID);

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            passed_zips: zips
        })
    }).catch(() => {
        zipSaved = false; 
    });
    
    if(zipSaved || HAS_PASSED_ZIPS) return;
    zipSaved = true;

  fetch(`/strava/activities/${ACTIVITY_ID}/passed-zips`, {
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({ passed_zips: zips })
    });
    
}


</script>

<script>


/* =======================
   GLOBAL VARIABLES
======================= */
const ACTIVITY_ID = {{ $activity->id }};
const HAS_PASSED_ZIPS = {!! $activity->passed_zips ? 'true' : 'false' !!};
const ACTIVITY_POLYLINE = {!! json_encode(json_decode($activity->activity_map->map)->summary_polyline) !!};

let routeGeoJSON;
let allZipData = null;
const loadedZipIds = new Set();
const zipMarkers = new Map();
const zipPolygons = new Map();
let zipSaved = false;
let activeZipRouteLayer = null;
let passedZipSet = new Set();
let selectedZipCode = null;
let flyoverMarker = null;
let animationIndex = 0;
let animationFrameId = null;
let isPaused = false;
const flySpeed = 200; // milliseconds per step

/* =======================
   INIT MAP (MAPLIBRE)
======================= */

const map = new maplibregl.Map({
    container: 'map',
    style: {
        version: 8,
        sources: {
            osm: {
                type: "raster",
                tiles: [
                    "https://a.tile.openstreetmap.org/{z}/{x}/{y}.png",
                    "https://b.tile.openstreetmap.org/{z}/{x}/{y}.png",
                    "https://c.tile.openstreetmap.org/{z}/{x}/{y}.png"
                ],
                tileSize: 256
            }
        },
        layers: [{
            id: "osm-layer",
            type: "raster",
            source: "osm"
        }]
    },
    center: [-98.6,39.8],
    zoom:5
});
// const map = new maplibregl.Map({
//     container: 'map',
//     style: {
//         version: 8,
//         sources: {
//             cyclosm: {
//                 type: "raster",
//                 tiles: [
//                     "https://a.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png",
//                     "https://b.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png",
//                     "https://c.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png"
//                 ],
//                 tileSize: 256,
//                 attribution: "© OpenStreetMap contributors, CyclOSM"
//             }
//         },
//         layers: [{
//             id: "cyclosm-layer",
//             type: "raster",
//             source: "cyclosm"
//         }]
//     },
//     center: [-98.6, 39.8],
//     zoom: 5
// });
map.addControl(new maplibregl.NavigationControl(), 'top-left');
map.addControl(new maplibregl.FullscreenControl(), 'top-left');


/* =======================
   MAP LOAD
======================= */

map.on("load", function () {

    const routeCoords = decodePolyline(ACTIVITY_POLYLINE);
    const startCoord = routeCoords[0];
    const endCoord   = routeCoords[routeCoords.length - 1];
    routeGeoJSON = turf.lineString(routeCoords);
    allZipData = @json($zips);
    console.log(allZipData);
    map.addSource("route",{
        type:"geojson",
        data:routeGeoJSON
    });
    map.addLayer({
        id: "route-line",
        type: "line",
        source: "route",
        paint: {
            "line-color": "#fc4c02",
            "line-width": 2
        }
    });
    const $startEl = $('<div>', {
        css: {
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center'
        }
    }).append(
    $('<img>', {
        src: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
        css: { width: '32px' }
    }),
    $('<span>', {
        text: 'START',
        css: {
            fontSize: '12px',
            fontWeight: 'bold',
            color: '#ffffff',              
            background: '#16a34a',       
            padding: '2px 6px',
            borderRadius: '6px',
            marginTop: '2px',
            boxShadow: '0 1px 4px rgba(0,0,0,0.4)',
            zIndex: 10                    
        }
    })
);

    new maplibregl.Marker({
        element: $startEl[0] 
    })
    .setLngLat(startCoord)
    .setPopup(new maplibregl.Popup().setText("Start"))
    .addTo(map);

 const $endEl = $('<div>', {
        css: {
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center'
        }
    }).append(
    $('<img>', {
        src: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
        css: { width: '32px' }
    }),
    $('<span>', {
    text: 'END',
    css: {
        fontSize: '12px',
        fontWeight: 'bold',
        color: '#ffffff',
        background: '#dc2626',
        padding: '2px 6px',
        borderRadius: '6px',
        marginTop: '2px',
        boxShadow: '0 1px 4px rgba(0,0,0,0.4)',
        zIndex: 10
    }
})
);


new maplibregl.Marker({
    element: $endEl[0]
})
.setLngLat(endCoord)
.setPopup(new maplibregl.Popup().setText("End"))
.addTo(map);
    const bounds = new maplibregl.LngLatBounds();

    routeCoords.forEach(c=>{
        bounds.extend(c);
    });

    map.fitBounds(bounds,{padding:20});

    allZipData.features.forEach(feature => {

        const zip = feature.properties.postcode;

        zipPolygons.set(zip, feature);

    });

    if(allZipData && allZipData.features.length>0){

                map.addSource("zips",{
                    type:"geojson",
                    data:allZipData
                });
                map.addSource("zipsData",{
                        type:"geojson",
                        data:allZipData
                    });
                map.addLayer({
                    id:"zip-border",
                    type:"line",
                    source:"zips",
                    paint:{
                        "line-color":"#ada5a9",
                        "line-width":1
                    }
                });

                map.addLayer({
                    id: 'zip-passed',
                    type: 'line',
                    source: 'zips',
                    paint: {
                        'line-color': '#2563eb',
                        'line-width': 2
                    },
                    filter: ['in', ['get','postcode'], ['literal', []]]
                }, 'zip-border');

                map.addLayer({
                    id: 'zip-selected',
                    type: 'line',
                    source: 'zips',
                    paint: {
                        'line-color': '#16a34a',
                        'line-width': 3
                    },
                    filter: ['==', ['get','postcode'], '']
                });

                map.addLayer({
                    id: "zip-active",
                    type: "line",
                    source: "zips",
                    paint: {
                        "line-color": "#ff0000",
                        "line-width": 5,
                        "line-opacity": 0.9
                    },
                    filter: ['==', ['get','postcode'], '']
                });
            findPassedZips();
    }

});


/* =======================
   ZIP MARKER ICON
======================= */

function createZipMarker(zip,lng,lat){

    const el = document.createElement("div");

    // Important: give container size
    el.style.width = "36px";
    el.style.height = "46px";
    el.style.cursor = "pointer";

    el.innerHTML = `
    <svg width="36" height="46" viewBox="0 0 36 46">
        <path d="M18 1C8.8 1 1 8.8 1 18
        c0 12.3 17 27 17 27
        s17-14.7 17-27C35 8.8 27.2 1 18 1z"
        fill="#fc4c02"/>

        <circle cx="18" cy="17" r="12" fill="#ffffff"/>

        <text x="18" y="18"
        text-anchor="middle"
        dominant-baseline="middle"
        font-size="8"
        font-weight="700">
        ${zip}
        </text>
    </svg>`;

    const marker = new maplibregl.Marker({
        element: el,
        anchor: "bottom"
    })
    .setLngLat([lng,lat])
    .addTo(map);

    zipMarkers.set(zip, marker);

    highlightZip(zip);

}

/* =======================
   FIND PASSED ZIPS
======================= */

function findPassedZips(){

    if(!routeGeoJSON || !allZipData) return;

    const passedZips=new Set();

    allZipData.features.forEach(feature=>{

        if(!feature.geometry) return;

        const zip=
        feature.properties.postcode ||
        feature.properties.ZCTA5CE20 ||
        feature.properties.GEOID20;

        if(!zip) return;

        const isIntersected=turf.booleanIntersects(feature,routeGeoJSON);

        if(isIntersected){

            passedZips.add(zip);
            passedZipSet.add(zip);

            const clipped=turf.lineIntersect(routeGeoJSON,feature);

            if(clipped.features.length>0){

                const lineInside=turf.lineSplit(routeGeoJSON,feature);

                let totalDistance=0;

                lineInside.features.forEach(segment=>{

                    if(
                        turf.booleanWithin(segment,feature) ||
                        turf.booleanIntersects(segment,feature)
                    ){

                        totalDistance+=turf.length(segment,{units:"miles"});

                    }

                });

                feature.properties.distance=totalDistance.toFixed(4);

            }


            /* =======================
               CREATE MARKER
            ======================= */

            if(!zipMarkers.has(zip)){

                const centroid=turf.centroid(feature).geometry.coordinates;

                const lng=centroid[0];
                const lat=centroid[1];

                createZipMarker(zip,lng,lat);

            }

        }

    });

}



/* =======================
   LOAD ZIP BY BBOX
======================= */

function loadZipsByBounds(bbox){

    $.ajax({

        url:"/zipcodes/map-bbox",
        type:"POST",
        data:JSON.stringify({bbox:bbox}),
        contentType:"application/json",

        headers:{
            "X-CSRF-TOKEN":"{{ csrf_token() }}"
        },

        success:function(data){

            if(!data.features) return;

            data.features.forEach(feature=>{

                const zip=
                feature.properties.postcode ||
                feature.properties.ZCTA5CE20 ||
                feature.properties.GEOID20;

                if(!zip || loadedZipIds.has(zip)) return;

                loadedZipIds.add(zip);

                allZipData.features.push(feature);

            });

            findPassedZips(); 
            updatePassedZipsLayer();

        }

    });

}



/* =======================
   MAP MOVE EVENT
======================= */

let moveTimer=null;

map.on("moveend",function(){

    clearTimeout(moveTimer);

    moveTimer=setTimeout(function(){

        if(map.getZoom()<10) return;

        const bounds=map.getBounds();

        const bbox=[
            bounds.getWest(),
            bounds.getSouth(),
            bounds.getEast(),
            bounds.getNorth()
        ];

      loadZipsByBounds(bbox);

    },300);

});

// Highlight a ZIP in MapLibre
function highlightZip(zip) {
    zip = String(zip);



if (map.getLayer('zip-passed')) {
    const passedZips = Array.from(passedZipSet).map(String);
  //  map.setFilter('zip-passed', ['in', ['get', 'ZCTA5CE20'], ['literal', passedZips]]);
    map.setFilter(
        'zip-passed',
        ['in', ['get','postcode'], ['literal', passedZips]]
    );
    console.log("zip-passed filter:", map.getFilter('zip-passed'));
    }
//   if (map.getLayer('zip-selected')) {
//     map.setFilter('zip-selected', ['==', ['get', 'postcode'], zip]);
//     console.log("zip-selected filter:", map.getFilter('zip-selected'));
//     }

    // Zoom to ZIP bounds
    const feature = zipPolygons.get(zip);
    // if (feature) {
    //     const bbox = turf.bbox(feature);
    //     map.fitBounds([[bbox[0], bbox[1]], [bbox[2], bbox[3]]], {
    //         padding: 20,
    //         duration: 800
    //     });
    // }

    // Highlight marker
    const marker = zipMarkers.get(zip);
    if (marker) {
        marker.getElement().classList.add('zip-pin-hover');
    }
}

// Reset a ZIP highlight
function resetZip(zip) {
    zip = String(zip);

    // Clear selected ZIP filter
    if (map.getLayer('zip-selected')) {
        map.setFilter('zip-selected', ['==', ['get', 'ZCTA5CE20'], '']);
    }

    // Reset passed ZIPs
    if (map.getLayer('zip-passed')) {
        const passedZips = Array.from(passedZipSet).map(String);
        map.setFilter('zip-passed', ['in', ['get', 'ZCTA5CE20'], ['literal', passedZips]]);
    }

    // Remove marker hover
    const marker = zipMarkers.get(zip);
    if (marker) {
        marker.getElement().classList.remove('zip-pin-hover');
    }
}

/* =======================
   POLYLINE DECODER
======================= */
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

function highlightZipWithRoute(zip) {

    zip = String(zip);
    selectedZipCode = zip;

    const feature = zipPolygons.get(zip);
    if (!feature) return;


  if (map.getLayer('zip-selected')) {
    map.setFilter('zip-selected', ['==', ['get', 'postcode'], zip]);
    console.log("zip-selected filter:", map.getFilter('zip-selected'));
    }

    const bbox = turf.bbox(feature);

    map.fitBounds(
        [
            [bbox[0], bbox[1]],
            [bbox[2], bbox[3]]
        ],
        { padding: 20, duration: 800 }
    );
}

function updatePassedZipsLayer() {
    if (!map.getLayer('zip-passed')) return;
    const passedZips = Array.from(passedZipSet).map(String);
    console.log("Updating passed ZIPs:", passedZips);

        map.setFilter(
        'zip-passed',
        ['in', ['get','postcode'], ['literal', passedZips]]
    );
}
function startFlyover() {

    if (!routeGeoJSON) return;

    const coords = routeGeoJSON.geometry.coordinates; // move here

    /* ========================
       STRAVA STYLE INTRO
    ======================== */

    const bounds = new maplibregl.LngLatBounds();

    coords.forEach(c => bounds.extend(c));

    map.fitBounds(bounds, {
        padding: 60,
        duration: 1500
    });

    setTimeout(() => {

        map.easeTo({
            center: coords[0],
            zoom: 15,
            pitch: 60,
            bearing: getBearing(coords[0], coords[1]),
            duration: 2000
        });

    }, 1500);

    /* ========================
       CREATE MARKER
    ======================== */

    if (!flyoverMarker) {

        const markerEl = document.createElement("div");

        markerEl.style.width = "14px";
        markerEl.style.height = "14px";
        markerEl.style.background = "#fc4c02";
        markerEl.style.borderRadius = "50%";
        markerEl.style.border = "2px solid white";
        markerEl.style.boxShadow = "0 0 6px rgba(0,0,0,0.4)";

        flyoverMarker = new maplibregl.Marker({
            element: markerEl,
            anchor: "center"
        })
        .setLngLat(coords[0])
        .addTo(map);
    }

    isPaused = false;

    function animate() {

    if (isPaused) return;

    if (animationIndex >= coords.length) {

        flyoverMarker.setLngLat(coords[0]);

        if (map.getSource("route-progress")) {
            map.getSource("route-progress").setData(
                turf.lineString([coords[0]])
            );
        }

        animationIndex = 0;
        selectedZipCode = null;

        console.log("Flyover finished. Reset to start.");

        return;
    }

 const current = coords[Math.floor(animationIndex)];
const next = coords[Math.min(Math.floor(animationIndex) + 1, coords.length - 1)];



    /* MOVE MARKER */
    flyoverMarker.setLngLat(current);

    let zoomLevel = 16;

    if (animationIndex < 30) zoomLevel = 15;
    if (animationIndex > coords.length - 30) zoomLevel = 15;

    // Update camera only every 5 frames
    if (animationIndex % 5 === 0) {

        map.easeTo({
            center: current,
            zoom: zoomLevel,
            pitch: 60,
            bearing: getBearing(current, next),
            duration: flySpeed * 6,
            easing: (t) => t
        });

    }

    animationIndex ++;

    animationFrameId = setTimeout(animate, flySpeed);
}

    animate();
}
function getBearing(start, end) {

    const startLng = start[0] * Math.PI / 180;
    const startLat = start[1] * Math.PI / 180;
    const endLng = end[0] * Math.PI / 180;
    const endLat = end[1] * Math.PI / 180;

    const y = Math.sin(endLng - startLng) * Math.cos(endLat);
    const x = Math.cos(startLat) * Math.sin(endLat) -
        Math.sin(startLat) * Math.cos(endLat) * Math.cos(endLng - startLng);

    const brng = Math.atan2(y, x);

    return (brng * 180 / Math.PI + 360) % 360;
}
    function pauseFlyover() {
        isPaused = true;
        if (animationFrameId) clearTimeout(animationFrameId);
    }

    const flyBtn = document.getElementById("playBtn");

    flyBtn.addEventListener("click", function () {
        if (isPaused || animationIndex === 0) {
            startFlyover();
            flyBtn.innerText = "⏸";
        } else {
            pauseFlyover();
            flyBtn.innerText = " ▶";
        }

    });
   
    if (!map.getSource("route-progress")) {
        map.addSource("route-progress", {
            type: "geojson",
            data: turf.lineString([])
        });
        map.addLayer({
            id: "route-progress-line",
            type: "line",
            source: "route-progress",
            paint: {
                "line-color": "#fc4c02",
                "line-width": 4
            }
        });
    }

// In animate loop:
const progressCoords = coords.slice(0, animationIndex + 1);
map.getSource("route-progress").setData(turf.lineString(progressCoords));
</script>

@endsection