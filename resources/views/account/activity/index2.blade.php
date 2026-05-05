@extends('layouts.account-app') 
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
<style>
    
 #map {
    height: calc(100vh - 110px);
    top: 70px; 
}
   #mapInfoBar {
    position: absolute;
    /* top: 110px; */
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
.activity-table-wrapper {
  overflow-x: auto;
}

.activity-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0 12px;
}

.activity-table thead th {
  font-size: 14px;
  text-transform: uppercase;
  color: #777;
  border: none;
}

.activity-row {
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.activity-row td {
  padding: 16px;
  vertical-align: middle;
  border: none;
}

.activity-main h6 {
  margin: 6px 0 2px;
  font-weight: 600;
}

.badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.badge-ride {
  background: #FC4C02;
  color: #fff;
}

.zip-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.zip-pill {
  background: #f1f3f5;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 12px;
}

.stats-list {
  list-style: none;
  padding: 0;
  margin: 0;
  font-size: 14px;
}

.stats-list li {
  margin-bottom: 4px;
}

.activity-row:hover {
  transform: translateY(-2px);
  transition: 0.2s ease;
}
#custom-loader {
    display: none; /* Hidden by default */
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(255, 255, 255, 0.7);
    z-index: 10;
    text-align: center;
    padding-top: 20%;
}
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


    
    /* Mobile */
    @media (max-width: 600px) {
        .map-drawer {
            width: 100%;
        }
    }

  #map {
    height: calc(100vh - 110px);
    top: 100px; 
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
/* Card styling */
.activity-card {
    border: none;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    background: #fff;
}

/* Header */
.activity-header {
    background: #f8f9fa;
    font-weight: 600;
    font-size: 15px;
    padding: 12px 16px;
    border-bottom: 1px solid #eee;
    position: sticky;
    top: 0;
    z-index: 2;
}

/* List container */
.activity-list {
    max-height: 460px;
    overflow-y: auto;
}

/* List items */
.activity-list .list-group-item {
    border: none;
    padding: 12px 16px;
    font-size: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: background 0.2s ease, transform 0.15s ease;
}

.activity-list .list-group-item:hover {
    background: #f5f7fa;
    transform: translateX(3px);
}

/* Optional active item */
.activity-list .list-group-item.active {
    background: #e9f2ff;
    color: #0d6efd;
    font-weight: 500;
}

/* Scrollbar (Chrome / Edge) */
.activity-list::-webkit-scrollbar {
    width: 6px;
}

.activity-list::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 6px;
}

.activity-list::-webkit-scrollbar-track {
    background: transparent;
}

.highlightRoute {
    background: #ffffff;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 10px;
    border-left: 4px solid transparent;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Hover */
.highlightRoute:hover {
    background: #f1f5ff;
    border-left-color: #0d6efd;
    transform: translateX(4px);
}

/* Selected */
.highlightRoute.active {
    background: #e9f2ff;
    border-left-color: #0d6efd;
    color: #0d6efd;
}
#activityList {
    padding: 8px;
    background: #f8f9fa;
}


 </style> 
 <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css"> 
<section id="hero" class="bg_gray py-6">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 d-flex flex-column">
                    <p class="tc_primary fs-4">Activities</p>
                    <!-- <h1 class="mb-4">Activities</h1> -->
                </div>
            </div>
        </div>
</section>

<section id="activities" class="py-5 bg-white">
  <div class="container">
     <div class="mb-4">
        
        <div class="row" id="activityDiv">
        <div class="col-lg-8">
              <div id="activityMap" style="height: 500px; width: 100%;"></div>
        </div>
        <div class="col-lg-4 mt-2 mb-2 text-end">
          <div class="card h-100">
            <div class="card-header fw-semibold">
                Activities
            </div>
          <ul id="activityList" class="list-group list-group-flush overflow-auto"
                style="max-height: 460px;">
           
          </ul>
        </div>
        </div>
        </div>
        <div class="row">
        <div class="col-lg-12 mt-2 mb-2 text-end">
           <a id="openactivityMap" class="btn btn-sm btn-outline-dark">View All Activity <i class="fa-regular fa-map" style="color:#c84f5c;"></i></a> 
        </div>
      
       </div>
         
       <div class="table-responsive">
          
        <div id="first-sync-message" class="alert alert-info" style="display: none;">
            We’re importing your Strava activities. This may take a few minutes.
        </div>
      
          <div id="custom-loader">
              <div class="spinner-border text-primary"></div> <!-- Bootstrap Spinner -->
              <p>Loading the activities...</p>
          </div>
          <div class="row mb-3">

                <div class="col-md-3">
                    <select id="sportFilter" class="form-select">
                        <option value="">All Sports</option>
                        @foreach($sportTypes as $type)
                        <option value="{{$type}}">{{$type}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                <input type="text" id="dateRange" class="form-control" placeholder="Select date range">
                </div>
                
                <div class="col-md-3">
                    <input type="text" id="nameSearch" class="form-control" placeholder="Search activity name">
                </div>
                <div class="col-md-3">
                    <button id="filterBtn" class="btn btn-primary">Filter</button>
                    <button id="resetBtn" class="btn btn-secondary">Reset</button>
                </div>

            </div>
          <table id="activitiesTable" class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Sport</th>
              <th>Date</th>
              <th>Title</th>
              <th width="10%">Passed ZIPs</th>
              <th class="text-end">Moving Time</th>
              <th class="text-end">Distance</th>
              <th class="text-end">Elevation</th>
            <th width="10%">Action</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
       
    </div>
      
  </div>

  <div id="mapDrawer" class="map-drawer">
  
    <div class="row">
        <div class="col-lg-12 position-relative">
              <div id="mapInfoBar" class="position-absolute top-0 start-0 w-100 z-3 drawer-header">
               
            </div>
        </div>
   
     <div class="col-lg-12 position-relative">
    <div id="map"></div>
     </div>
    </div>
    
</div>
    
</section>
<!-- Modal -->


@endsection

@section('script')
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(document).ready(function () { 
 /* const isSyncing = @json($isSyncing);
 const hasActivities = @json($hasActivities); */

 
    let startDate = '';
    let endDate = '';
    let name = '';

    $('#dateRange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            cancelLabel: 'Clear'
        }
    });
      
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
            url: '{{ route('activity.index') }}',
            data: function(d) {
                d.sport_type = $('#sportFilter').val();
                d.start_date = startDate;
                d.end_date = endDate;
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
    $('#sportFilter').on('change', function () {
        table.draw();
    });

    $('#resetBtn').click(function(){

        $('#sportFilter').val('');
        $('#fromDate').val('');
        $('#toDate').val('');

        table.draw();
    });
    $('#activitiesTable_filter').hide();
       $('#first-sync-message').show();
       $.ajax({
        url: "{{ route('activity.sync') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}"
        },
        success: function () {
          //  $('#first-sync-message').hide();
            $('#activitiesTable').DataTable().ajax.reload();
        },
         error: function () {
         // $('#first-sync-message').hide();
            $('#activitiesTable').DataTable().ajax.reload();
          }

    });
         
    const intervalId = setInterval(function () {

        table.ajax.reload(null, false);

        $.get("{{ route('activity.sync-status') }}", function (res) {

            if (res.is_syncing === false) {
                 $('#first-sync-message').hide();
                clearInterval(intervalId); 
            }
        });

    }, 4000);

  
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
            url: "{{ route('activity.map') }}",
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

    function loadActivityMap(activityResponse) {
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

        map = L.map('map').setView([39.8, -98.6], 5);

        L.tileLayer(
          'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
          {
            attribution: '© OpenStreetMap © CARTO',
            subdomains: 'abcd',
            maxZoom: 20
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

        // White outline
        L.polyline(leafletCoords, {
            color: '#ffffff',
            weight: 6
        }).addTo(map);

        // Orange route (Strava style)
        const routeLine = L.polyline(leafletCoords, {
            color: '#fc4c02',
            weight: 2,
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
    $('#zipList').html(zipArray.join(', '));
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
    if (zipSaved || HAS_PASSED_ZIPS) return;

    zipSaved = true;
    const SAVE_ZIPS_URL ="{{ route('activity.savePassedZips', ['activity' => '__ID__']) }}";
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
            url: "{{ route('activity.polylines') }}",
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
