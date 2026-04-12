@extends('layouts.app') 
   
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
</style>
<main>
    
    <!-- breadcrumb-area -->
    <section class="breadcrumb-area d-flex align-items-center" style="background-image:url({{ url('front/img/bg/bdrc-bg.jpg')}})">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-12 col-lg-12">
                    <div class="breadcrumb-wrap text-left">
                        <div class="breadcrumb-title">
                            <h2>Explore Map</h2>    
                            <div class="breadcrumb-wrap">
                        
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Explore Map </li>
                            </ol>
                        </nav>
                    </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>

    <section class="product-desc-area py-5 pb-55">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div id="map"></div>
                    </div>
            </div>
    </section>
   <div id="map-loader" style="
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%, -50%);
    background:rgba(255,255,255,0.9);
    padding:10px 20px;
    border-radius:8px;
    font-weight:600;
    z-index:999;
    display:none;
">
    <div class="spinner"></div>
    Loading ZIP data...
</div>
</main>
     
@endsection


@section('script')


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script src="https://unpkg.com/@mapbox/polyline@1.1.1/src/polyline.js"></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>

<script src="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.js"></script>

<!-- <script src="https://unpkg.com/maplibre-gl@3.6.2/dist/maplibre-gl.js"></script> -->
<script>
const PASSED_ZIPS = @json($passedZips);

let loadedZipIds = new Set();
let globalBounds = L.latLngBounds();

// --------------------
// 1. INIT LEAFLET MAP
// --------------------
const map = L.map('map').setView([39.8283, -98.5795], 6);

// OSM Tile Layer
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19
}).addTo(map);

// --------------------
// 2. GEOJSON LAYER HOLDER
// --------------------
let zipLayer = L.geoJSON(null, {
    style: function (feature) {
        const isPassed = PASSED_ZIPS.includes(feature.properties.postcode);

        return {
            fillColor: isPassed ? '#00b894' : '#dfe6e9',
            fillOpacity: isPassed ? 0.6 : 0.3,
            color: '#636e72',
            weight: 0.5
        };
    },

    onEachFeature: function (feature, layer) {
        layer.on('mousemove', function (e) {
            popup
                .setLatLng(e.latlng)
                .setContent(`<b>ZIP:</b> ${feature.properties.postcode}`)
                .openOn(map);
        });

        layer.on('mouseout', function () {
            map.closePopup(popup);
        });
    }
}).addTo(map);

// --------------------
// 3. POPUP
// --------------------
const popup = L.popup({
    closeButton: false,
    autoClose: false
});

// --------------------
// 4. LOAD ZIPS IN VIEW
// --------------------
function loadZipsInView() {
    const bounds = map.getBounds();

    $.ajax({
        url: "/zips-in-view",
        type: "GET",
        data: {
            minLng: bounds.getWest(),
            minLat: bounds.getSouth(),
            maxLng: bounds.getEast(),
            maxLat: bounds.getNorth()
        },
        success: function (data) {

            const newFeatures = data.features.filter(f => {
                const id = f.properties.postcode;

                if (loadedZipIds.has(id)) return false;

                loadedZipIds.add(id);
                return true;
            });

            // Add new features to map
            zipLayer.addData(newFeatures);

            // Extend bounds
            newFeatures.forEach(f => {
                const coords = L.geoJSON(f).getBounds();
                globalBounds.extend(coords);
            });

            // optional: fit bounds once meaningful data exists
            // if (globalBounds.isValid()) {
            //     map.fitBounds(globalBounds, {
            //         padding: [40, 40],
            //         maxZoom: 10
            //     });
            // }
        },
        error: function (err) {
            console.error("ZIP load error", err);
        }
    });
}

// --------------------
// 5. EVENTS
// --------------------
map.on('moveend', function () {
    loadZipsInView();
});

// initial load
loadZipsInView();

</script>



@endsection

