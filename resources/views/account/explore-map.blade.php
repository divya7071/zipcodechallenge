@extends('layouts.account-app') 
   
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    #drawerOverlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    z-index: 1000;
    display: none;
}

/* Map Drawer */
.map-drawer {
    position: fixed;
    top: 0;
    right: -420px;
    width: 420px;
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
.zip-label {
    text-align: center;
    color: #fc4c02;
    font-weight: bold;
    font-size: 14px;
    background: transparent;
}


/* Mobile */
@media (max-width: 600px) {
    .map-drawer {
        width: 100%;
    }
}

#map {
    height: calc(100vh - 110px);
  }
   #mapInfoBar {
    position: absolute;
  
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

<div class="card-box mb-4">
    <div class="d-flex justify-content-between mb-4">
        <div>
            <h5 class="mb-0">Zipcodes</h5>
            <small class="text-muted">Showing all zipcodes in map</small>
        </div>
    </div>

    <!-- Map Wrapper -->
    <div id="mapWrapper">
        <div id="map"></div>

        <!-- Overlay INSIDE wrapper -->
        <div id="mapLoadingOverlay">
            <div class="loader"></div>
            <div class="text">Loading map data...</div>
        </div>
    </div>
</div>
     
@endsection


@section('script')

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!--<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>-->
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script src="https://unpkg.com/@mapbox/polyline@1.1.1/src/polyline.js"></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>

<script src="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.js"></script>

<!-- <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script> -->
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

