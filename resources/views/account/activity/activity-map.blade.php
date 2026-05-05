@extends('layouts.app') 
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    
 #map {
    height: calc(100vh - 110px);
    top: 70px; 
}
   #mapInfoBar {
    position: absolute;
    top: 110px; /* adjust once if header height changes */
    left: 0;
    width: 100%;
    z-index: 900;
}

</style>
<section id="hero" class="bg_wave bg_position_bottom bg_image overflow-hidden pt-12">
        <div class="container mt-12">
            <div class="row">
               <div class="col-lg-12 position-relative">
                     <div id="mapInfoBar" class="position-absolute top-0 start-0 w-100 z-3">
                        <div class="container-fluid">
                            <div class="row g-2 align-items-center bg-white py-2 px-3 border-bottom">

                                <!-- Route Title -->
                                <div class="col-auto">
                                    <span class="fw-semibold text-dark">
                                        🚴 Morning Ride
                                    </span>
                                </div>

                                <!-- Created Time -->
                                <div class="col-auto text-muted small">
                                    <i class="bi bi-clock"></i>
                                    12 Jan 2026 · 06:45 AM
                                </div>

                                <!-- Divider -->
                                <div class="col-auto text-muted">|</div>

                                <!-- ZIP Count -->
                                <div class="col-auto" style="display:none" id="zipCountDiv">
                                    <span class="badge bg-primary" id="zipCount">
                                        
                                    </span>
                                </div>

                                <!-- ZIP Codes -->
                                <div class="col text-truncate">
                                    <span class="small fw-semibold">ZIPs:</span>
                                    <span class="small text-secondary" id="zipList">
                                       
                                    </span>
                                </div>

                            </div>
                        </div>
                    </div>
               </div>
            </div>
             <div class="row">
               <div class="col-lg-12 position-relative">
                    <div id="map"></div>
                    <!-- <div id="zipList"><b>ZIPs Passed</b></div> -->
                </div>
            </div>
        </div>
</section>

    
@endsection

@section('script')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Turf.js -->
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
<script>
/* =======================
   GLOBAL VARIABLES
======================= */
const ACTIVITY_ID = {{ $activity->id }};
const HAS_PASSED_ZIPS = {!! $activity->passed_zips ? 'true' : 'false' !!};
const ACTIVITY_POLYLINE = {!! json_encode(json_decode($activity->map)->polyline) !!};
let routeGeoJSON;
let allZipData = null;
const loadedZipIds = new Set();
const zipListDiv = document.getElementById('zipList');
const zipCounttDiv = document.getElementById('zipCount');
let zipSaved = false;

/* =======================
   INIT MAP
======================= */
const map = L.map('map').setView([39.8, -98.6], 5);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
}).addTo(map);

const zipLayer = L.geoJSON(null, {
    style: { color:'#5f46ceff', weight:2, fillOpacity:0 },
    onEachFeature: function(feature, layer) {
        if(feature.properties?.postcode){
            layer.bindTooltip(feature.properties.postcode, { permanent: false, direction: "center" });
        }
    }
}).addTo(map);

/* =======================
   LOAD STRAVA ROUTE GEOJSON
======================= */
const routeCoords = decodePolyline(ACTIVITY_POLYLINE);
routeGeoJSON = turf.lineString(routeCoords);

// Draw route on Leaflet map
const leafletCoords = routeCoords.map(([lng, lat]) => [lat, lng]);
const routeLine = L.polyline(leafletCoords, { color: '#006400', weight: 4 }).addTo(map);
map.fitBounds(routeLine.getBounds(), { padding: [20,20] });

fetch('{{asset("geo/us_zipcodes.json")}}')
.then(res => res.json())
.then(data => {
    allZipData = data;
    loadZipsInView();
})
.catch(console.error);

/* =======================
   LAZY LOAD ZIPs
======================= */
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

/* =======================
   CALCULATE PASSED ZIPs
======================= */
function findPassedZips(){
    if(!routeGeoJSON) return;

    const passedZips = new Set();

    zipLayer.eachLayer(layer => {
        if(!layer.feature?.geometry) return;

        if(turf.booleanIntersects(layer.feature, routeGeoJSON)){
            passedZips.add(layer.feature.properties.postcode);

            layer.setStyle({ color: '#cc2e33ff', weight:3, fillOpacity:0.5 });

            // show label
            const centroid = turf.centroid(layer.feature).geometry.coordinates;
            L.marker([centroid[1], centroid[0]], {
                icon: L.divIcon({ className:'zip-label', html: layer.feature.properties.postcode, iconSize:[50,20], iconAnchor:[25,10] }),
                interactive: false
            }).addTo(map);
        } 
        // else {
        //     layer.setStyle({ color:'#888888', weight:1, fillOpacity:0 });
        // }
    });

    const zipArray = [...passedZips];
    zipListDiv.innerHTML = zipArray.join(', ');
    zipCounttDiv.innerHTML = "ZIPs Passed: "+zipArray.length;
    document.getElementById('zipCountDiv').style.display = 'block';

    // Save to Laravel once
    savePassedZips(zipArray);
}

/* =======================
   SAVE PASSED ZIPs TO LARAVEL
======================= */
function savePassedZips(zips){
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

        result = 1;
        shift = 0;
        do {
            b = str.charCodeAt(index++) - 63 - 1;
            result += b << shift;
            shift += 5;
        } while (b >= 0x1f);
        lng += (result & 1) ? ~(result >> 1) : (result >> 1);

        coordinates.push([lng * 1e-5, lat * 1e-5]); // [lng, lat] for Turf
    }

    return coordinates;
}

</script>
@endsection
