@extends('layouts.account-app') 
@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <!-- Activities -->
<div class="card-box mb-4">
    <h5 class="mb-1">Activities</h5>
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
            <input type="text" id="datepicker"  class="form-control filter" placeholder="Date range">
        </div>

        <div class="col-12 col-md-4">
            <input type="text" id="nameSearch" class="form-control" placeholder="Activity name">
        </div>

        <div class="col-12 col-md-2 d-flex gap-2">
            <button class="btn btn-strava w-100 filter">Filter</button>
            <button class="btn btn-outline-secondary w-100" id="resetBtn">Reset</button>
        </div>

    </div>

    <!-- Table -->
    <div class="row">
    <div class="table table-responsive mt-3">
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
                    <th width="5%">Action</th>
                </tr>
            </thead>
            <tbody>
                
            </tbody>
        </table>
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
            <div id="map"></div>
        </div>
    </div>
    
</div>
@endsection

@section('script')

<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js"></script>
<script src="https://unpkg.com/@mapbox/polyline@1.1.1/src/polyline.js"></script>
<script src="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.js"></script>
<script>
$(document).ready(function () { 
 /* const isSyncing = @json($isSyncing);
 const hasActivities = @json($hasActivities); */

 
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
       
    };
  $('#activityDiv').hide();
   var table =$('#activitiesTable').DataTable({
        processing: true,
        serverSide: true,
        language: {
          processing: 
          '<div class="dt-loading"><span class="spinner"></span><span class="ms-2">Loading the activities...</span></div>',
          emptyTable: "No data available"
        },
        ajax: {
            url: '{{ route('account.activity.index') }}',
            data: function(d) {
                d.sport_type = $('#sportFilter').val();
                d.start_date = $('#datepicker').data('daterangepicker').startDate.format('YYYY-MM-DD');
                d.end_date = $('#datepicker').data('daterangepicker').endDate.format('YYYY-MM-DD');
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

    $('#nameSearch').on('keyup', function () {
        name=$(this).val();
        table.draw();
    });
    $('#filterBtn').click(function(){
        table.draw();
    });
    $('.filter').on('change', function () {
        table.draw();
    });

    $('#resetBtn').click(function(){
        $('#nameSearch').val('');
        $('#sportFilter').val('');
        $('#fromDate').val('');
        $('#toDate').val('');
        table.draw();
    });
    $('#activitiesTable_filter').hide();
     
   });

   $(document).ready(function () {

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


    let map = null;
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

function loadActivityMap(activityResponse) 
{
        var mapData = JSON.parse(activityResponse.activity_map.map); 
        var summaryPolyline = mapData.summary_polyline;
      //  console.log("Your Polyline:", summaryPolyline);
        let ACTIVITY_ID = activityResponse.id;
        HAS_PASSED_ZIPS =activityResponse.passed_zips ;
        const ACTIVITY_POLYLINE =summaryPolyline;
   
        if (map) {
              map.remove();
              map = null;
          }

        // map = L.map('map').setView([39.8, -98.6], 5);

        map = L.map('map', {
            zoomControl: true,
            preferCanvas: true   
        }).setView([39.8, -98.6], 5);

        map.addControl(L.control.fullscreen({
            position: 'topleft'
        }));


    /* ===============================
       BASE LAYERS
    ================================ */
    const cycleLayer =L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
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

    zipLayer = L.geoJSON(null, {
        style: {
            color: '#9aa3ad',      
            weight: 1,
            fillOpacity: 0        
        }
    }).addTo(map);


        const routeCoords = decodePolyline(ACTIVITY_POLYLINE);
        routeGeoJSON = turf.lineString(routeCoords);
        const leafletCoords = routeCoords.map(([lng, lat]) => [lat, lng]);
        const startPoint = leafletCoords[0];
        const endPoint   = leafletCoords[leafletCoords.length - 1];

        L.polyline(leafletCoords, {
            color: '#ffffff',
            weight: 6
        }).addTo(map);


        const routeLine = L.polyline(leafletCoords, {
            color: '#fc4c02',
            weight: 3,
            opacity: 0.95
        }).addTo(map);
       const startIcon = L.icon({
            iconUrl: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png',
            iconSize: [32,32],
            iconAnchor: [16, 32]
        });
 
        const endIcon = L.icon({
            iconUrl: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
            iconSize: [32, 32],
            iconAnchor: [16, 32]
        });
    
        L.marker(startPoint, { icon: startIcon })
            .addTo(map)
            .bindTooltip("START", {
                permanent: true,     
                direction: "top",  
                offset: [0, -40],   
                className: "start-label"
            });


        L.marker(endPoint, { icon: endIcon })
            .addTo(map)
             .bindTooltip("END", {
                permanent: true,     
                direction: "top",  
                offset: [0, -40],       
                className: "end-label"
            });
 
        // if (startPoint[0] === endPoint[0] && startPoint[1] === endPoint[1]) {
        //     L.circleMarker(startPoint, {
        //         radius: 7,
        //         color: '#000',
        //         fillColor: '#fc4c02',
        //         fillOpacity: 1
        //     }).addTo(map).bindPopup("Start / End");
        // }
        map.fitBounds(routeLine.getBounds(), { padding: [20, 20] });
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

    // 👉 create bounds object
    const bounds = L.latLngBounds();

    zipLayer.eachLayer(layer => {
        if(!layer.feature?.geometry) return;

        const zip = layer.feature.properties.postcode;
        zipPolygons.set(zip, layer);

        const isIntersected = turf.booleanIntersects(layer.feature, routeGeoJSON);

        if (isIntersected) {
            passedZips.add(zip);

            bounds.extend(layer.getBounds());

            layer.setStyle({
                color: '#2563eb',
                weight: 3,
                fillOpacity: 0
            });
            layer.bringToFront();
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
            layer.setStyle({
                color: '#9ca3af',
                weight: 1,
                fillOpacity: 0
            });

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

    if (bounds.isValid()) {
        map.fitBounds(bounds, {
            padding: [20, 20],
            maxZoom: 12 // optional limit
        });
    }

    $('#zipList').html(zipArray.join(', '));
    zipListDiv.innerHTML = zipArray.join(', ');
    zipCounttDiv.innerHTML = "ZIPs Passed: " + zipArray.length;
    document.getElementById('zipCountDiv').style.display = 'block';

    savePassedZips(zipArray);
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
//                 }).addTo(map);

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
//                 .addTo(map);
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



/* =======================
   SAVE TO LARAVEL
======================= */
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
/* =======================
   POLYLINE DECODER
======================= */



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

/* =======================
   INIT MAP (FREE STRAVA STYLE)
======================= */
const activitiesMap = L.map('activityMap').setView([39.8, -98.6], 5);

L.tileLayer(
  'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
  {
    attribution: '© OpenStreetMap © CARTO',
    subdomains: 'abcd',
    maxZoom: 20
  }
).addTo(activitiesMap);

/* =======================
   ZIP LAYER
======================= */
const zipLayer1 = L.geoJSON(null, {
    style: {
        color: '#9aa3ad',      
        weight: 1,
        fillOpacity: 0        
    }
}).addTo(activitiesMap);

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
        
    });
//////////////////////////////

</script>
@endsection
