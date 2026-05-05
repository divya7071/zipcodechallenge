@extends('layouts.account-app') 
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
                                        🚴 {{$activity->name}}
                                    </span>
                                </div>

                                <!-- Created Time -->
                                <div class="col-auto text-muted small">
                                    <i class="bi bi-clock"></i>
                                    {{ \Carbon\Carbon::parse($activity->date)->format('D, m/d/Y') }}
                                </div>

                                <!-- Divider -->
                                <div class="col-auto text-muted">|</div>

                                <!-- ZIP Count -->
                                @if(!empty($activity->passed_zips))
                                    @php
                                        $zips = is_array($activity->passed_zips) ? $activity->passed_zips : json_decode($activity->passed_zips, true);
                                    @endphp

                                @endif
                                <div class="col-auto" id="zipCountDiv">
                                    <span class="badge bg-primary" id="zipCount">
                                        ZIPs Passed: {{count($zips)}}
                                    </span>
                                </div>

                                <!-- ZIP Codes -->
                                <div class="col text-truncate">
                                    <span class="small fw-semibold">ZIPs:</span>
                                    <span class="small text-secondary" id="zipList">
                                        @if(!empty($activity->passed_zips))
                                        @php
                                        $zips = is_array($activity->passed_zips) ? $activity->passed_zips : json_decode($activity->passed_zips, true);
                                        @endphp
                                        {{ implode(',',$zips) }}
                                        @endif
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
const zipMarkers = new Map();   // zip → marker
const zipPolygons = new Map();  // zip → polygon
let zipSaved = false;

const zipListDiv = document.getElementById('zipList');
const zipCounttDiv = document.getElementById('zipCount');

/* =======================
   INIT MAP (FREE STRAVA STYLE)
======================= */
const map = L.map('map').setView([39.8, -98.6], 5);

L.tileLayer(
  'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
  {
    attribution: '© OpenStreetMap © CARTO',
    subdomains: 'abcd',
    maxZoom: 20
  }
).addTo(map);

/* =======================
   ZIP LAYER
======================= */
const zipLayer = L.geoJSON(null, {
    style: {
        color: '#9aa3ad',      
        weight: 1,
        fillOpacity: 0        
    }
}).addTo(map);

/* =======================
   LOAD STRAVA ROUTE
======================= */
const routeCoords = decodePolyline(ACTIVITY_POLYLINE);
routeGeoJSON = turf.lineString(routeCoords);
const leafletCoords = routeCoords.map(([lng, lat]) => [lat, lng]);

// White outline
L.polyline(leafletCoords, {
    color: '#ffffff',
    weight: 6
}).addTo(map);

// Orange route (Strava style)
const routeLine = L.polyline(leafletCoords, {
    color: '#fc4c02',
    weight: 4,
    opacity: 0.95
}).addTo(map);

map.fitBounds(routeLine.getBounds(), { padding: [20, 20] });


fetch('{{ asset("geo/us_zipcodes.json") }}')
.then(res => res.json())
.then(data => {
    allZipData = data;
    loadZipsInView();
})
.catch(console.error);

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
                weight: 2,
                fillOpacity: 0
            });

            if (!zipMarkers.has(zip)) {
                const [lng, lat] = turf.centroid(layer.feature).geometry.coordinates;
                const marker = L.marker([lat, lng], {
                    icon: zipPinIcon(zip),
                    riseOnHover: true
                }).addTo(map);

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
                .addTo(map);
            }

            if (zoom < 11 && layer._zipLabel) {
                map.removeLayer(layer._zipLabel);
                layer._zipLabel = null;
            }
        }
    });

    const zipArray = [...passedZips];
    zipListDiv.innerHTML = zipArray.join(', ');
    zipCounttDiv.innerHTML = "ZIPs Passed: " + zipArray.length;
    document.getElementById('zipCountDiv').style.display = 'block';

    savePassedZips(zipArray);
}


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



/* =======================
   SAVE TO LARAVEL
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
</script>
@endsection
