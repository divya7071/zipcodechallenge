@extends('layouts.account-app') 
   
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    .metric-card {
        border-left: 0;
        text-align: center;
    }
    .metric-card .stat-value {
        font-size: 22px;
        margin-bottom: 3px;
    }
    .zip-label {
    text-align: center;
    color: #fc4c02;
    font-weight: bold;
    font-size: 16px;
    background: transparent;
}
</style>
<!-- Activities -->
<div class="card-box mb-4">
    <div class="d-flex justify-content-between">
        <div class="header">
            <h5 class="mb-1">Zipcode: {{$zipcode}}</h5>
            <small class="text-muted">Showing Zipcode {{$zipcode}} Leader board</small>
        </div>
        <!-- Filters -->
        <div class="actions">
            <span class="text-warning">{{$totalAttempts}}</span> {{ $totalAttempts > 1 ? Str::plural('Attempt') : 'Attempt' }}
        </div>
    </div>

    <!-- Table -->
    <div class="row mt-4">
        <div class="col-lg-12">
            <!-- Stats Row -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="metric-card bg-light">
                        <h4 class="stat-value text-success">{{ !empty($myBest) ? number_format($myBest->distance_mi, 2) : '0.00' }} mi</h4>
                        <div class="stat-label">Highest Distance</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="metric-card bg-light">
                        <h4 class="stat-value text-info">{{ !empty($myBest) ? $myBest->elevation_gain_ft : '0' }} ft</h4>
                        <div class="stat-label">Elevation Gain</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="metric-card bg-light">
                        <h4 class="stat-value text-warning">{{$lowestElevation}} ft</h4>
                        <div class="stat-label">Lowest Elev</div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="metric-card bg-light">
                        <h4 class="stat-value text-danger">{{$highestElevation}} ft</h4>
                        <div class="stat-label">Highest Elev</div>
                    </div>
                </div>
            </div>
            <div class="row mb-4">
                <!-- Map -->
                <div class="map-box rounded-4">
                    <div
                        class="map" id="map"
                        data-polyline=""
                        data-loaded="false"></div>
                </div>

                <!-- Elevation Chart -->
                <!-- <div class="elevation-chart bg-light rounded-3 p-3">
                    <canvas id="elevationChart" height="120"></canvas>
                </div> -->

            </div>
            <div class="segments-box mt-2">
                <h5 class="mb-2 fw-semibold">Leaderboards</h5>
                <!-- Summary Card -->
                <div class="row mb-3">
                    <!-- My Rank -->
                    <div class="col-md-4 mb-3 mb-md-0">
                        <div class="metric-card bg-light">
                            <div class="text-muted small">My Attempts</div>
                            <h4 class="fw-bold mb-0">{{ $totalAttempts ?? '1' }}</h4>
                        </div>
                    </div>

                    <!-- My Best Time -->
                    <div class="col-md-4">
                        <div class="metric-card bg-light">
                            <div class="text-muted small">My Best Time</div>
                            <h4 class="fw-bold mb-0">{{ !empty($myBest) ? $myBestTime : '00:00:00' }}</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="metric-card bg-light">
                            <div class="text-muted small">Distance</div>
                            <h4 class="fw-bold mb-0">{{ !empty($myBest) ? $myBest->distance_mi .' mi' : '0.00 mi' }}</h4>
                        </div>
                    </div>
                </div>
                <!-- Leaderboard Table -->
                <div class="mb-3">
                    <table id="zipLeaderboard" class="table table-responsive">
                        <thead class="table-light">
                            <tr>
                                <th width="60">#</th>
                                <th>Athlete</th>
                                <th>Date</th>
                                <th>Distance</th>
                                <th>Time</th>
                                <th>Speed</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        {{-- <div class="col-lg-3">
            <!-- Your Stats -->
            <div class="bg-white rounded-4 shadow-sm p-3 mb-4">
                <h5 class="fw-semibold mb-3">Your Stats</h5>

                <div class="d-flex align-items-center mb-3">
                    <!-- <img
                        src="{{ auth('athlete')->user()->profile_medium ?? asset('front/img/general/account.jpg') }}"
                        class="rounded-circle mb-2"
                        width="40" height="40"> -->
                    <div>
                        <div class="fw-semibold p-2">{{ auth('athlete')->user()->first_name }}
                            {{ auth('athlete')->user()->last_name }}
                        </div>
                        <small class="text-muted">All-Time PR -- {{$myBestTime}}</small>
                    </div>
                </div>


            </div>

            <!-- Fastest Times -->
            <div class="bg-white rounded-4 shadow-sm p-3 mb-4">
                <h5 class="fw-semibold mb-3">Fastest Times</h5>

                @foreach($topThree as $index => $athlete)


                <div class="d-flex align-items-center mb-3">

                    <!-- Athlete Avatar -->
                    <!-- <img src="{{ $athlete->athlete->profile ?? 'https://via.placeholder.com/40' }}"
                        class="rounded-circle me-3"
                        width="40" height="40"> -->

                    <div class="flex-grow-1">

                        <!-- Medal + Name + Time -->
                        <div class="small fw-semibold">

                            {{ $athlete->athlete->first_name }}
                            {{ $athlete->athlete->last_name }}

                        </div>

                        <!-- Distance + Speed -->
                        <small class="text-muted">

                            {{ number_format($athlete->distance_mi, 2) }} mi
                            {{ number_format($athlete->speed_mph, 2) }} mph
                            {{ gmdate('H:i:s', $athlete->moving_sec) }}
                        </small>

                    </div>

                </div>

                @endforeach

            </div>
            <div class="bg-white rounded-4 shadow-sm p-3 mb-4">
                <h5 class="fw-semibold mb-3">Local Legend</h5>

                <div class="d-flex align-items-center mb-3">
                    <!-- <img
                        src="{{ auth('athlete')->user()->profile_medium ?? asset('front/img/general/account.jpg') }}"
                        class="rounded-circle mb-2  me-3"
                        width="40" height="40"> -->
                    <div class="flex-grow-1">

                        <!-- Medal + Name + Time -->
                        <div class="small fw-semibold">

                            {{ $localLegend->athlete->first_name }}
                            {{ $localLegend->athlete->last_name }}

                        </div>

                        <!-- Distance + Speed -->
                        <small class="text-muted">
                            Local Legend - {{$localLegend->total_attempts}} efforts

                        </small>

                    </div>
                </div>

            </div>
        </div> --}}

    </div>
</div>

@endsection
@section('script')

<script src="https://unpkg.com/@mapbox/polyline@1.1.1/src/polyline.js"></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>

<script src="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.js"></script>



<script>
const ZIP_CODE = "{{ $zipcode }}"; 

</script>


<script>
document.addEventListener("DOMContentLoaded", async function () {

    /* ===============================
       INIT MAP (Canvas Enabled)
    ================================ */
    const map = L.map('map', {
        zoomControl: true,
        preferCanvas: true   
    }).setView([39.8, -98.6], 5);

    map.addControl(L.control.fullscreen({
        position: 'topleft'
    }));


    /* ===============================
       BASE LAYERS
    ================================ */
   const cycleLayer = L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
            maxZoom: 20,
            attribution: '© OpenStreetMap contributors, CyclOSM'
        }).addTo(map);
    const standardLayer = L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }
    );

    const satelliteLayer = L.tileLayer(
        'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        {
            attribution: 'Tiles © Esri',
            maxZoom: 19
        }
    );

    const labelsLayer = L.tileLayer(
        'https://services.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
        {
            attribution: 'Labels © Esri'
        }
    );

    const hybridLayer = L.layerGroup([
        satelliteLayer,
        labelsLayer
    ]);

    standardLayer.addTo(map);

    L.control.layers(
        {
            "Cycle Map": cycleLayer,
            "Standard Map": standardLayer,
            "Satellite Map": satelliteLayer,
            "Hybrid Map": hybridLayer
        },
        null,
        {
            position: 'topright',
            collapsed: true
        }
    ).addTo(map);



    try {
       const selectedZip = @json($selectedZip);
        if (!selectedZip) {
            console.warn("ZIP not found:", ZIP_CODE);
        } else {

            requestAnimationFrame(() => {

                const zipLayer = L.geoJSON(selectedZip, {
                    renderer: L.canvas(),
                    style: {
                        color: '#fc4c02',
                        weight: 4,
                        opacity: 1,
                        fillOpacity: 0
                    }
                }).addTo(map);
                const center = zipLayer.getBounds().getCenter();
                const zipLabel = L.marker(center, {
                    icon: L.divIcon({
                        className: 'zip-label',
                        html: ZIP_CODE,
                        iconSize: [100, 20], 
                        iconAnchor: [50, 10] 
                    }),
                    interactive: false // makes it non-clickable (like label)
                }).addTo(map);
                map.fitBounds(zipLayer.getBounds(), {
                    padding: [30, 30]
                });

            });
        }
    } catch (error) {
        console.error("Error loading ZIP data:", error);
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
</script>
<script>
$(document).ready(function() {
    $('#zipLeaderboard').DataTable({
        processing: true,
        serverSide: true,
        language: {
          processing: 
          '<div class="dt-loading"><span class="spinner"></span><span class="ms-2">Loading ...</span></div>',
          emptyTable: "No data available"
        },
        ajax: "{{ route('account.zip.leaderboard', $zipcode) }}",
        order: [[1, 'desc'], [3, 'desc']], 

        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'athlete_name', name: 'athlete.first_name' },
            { data: 'date', name: 'date' },
            { data: 'distance_mi', name: 'distance_mi' },
            { data: 'moving_sec', name: 'moving_sec' },
            { data: 'speed_mph', name: 'speed_mph' }
        ]
    });
});
</script>
@endsection


