@extends('layouts.account-app') 
   
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">

    <!-- Activities -->
<div class="card-box mb-4">
    <h5 class="mb-1">To Do Zips</h5>
    <small class="text-muted">Showing all To Do Zips</small>

    <div class="row">
        <div class="col-12">
            <div class="bakix-details-tab">
            <ul class="nav text-center justify-content-center pb-30 mb-30">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('account.todo-zips') ? 'active' : '' }}"
                    href="{{ route('account.todo-zips') }}">
                        Todo Zips
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('account.passed-zips') ? 'active' : '' }}"
                    href="{{ route('account.completed-zips') }}">
                        Completed Zips
                    </a>
                </li>
            </ul>
            </div>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade active show" id="id-desc" role="tabpanel" aria-labelledby="desc-tab">
                    <div class="mb-4">
                        <div class="row g-2" id="todo-zip-container">
                            @include('partials.zip-items')
                        </div>

                        <div id="loading" class="text-center my-3" style="display:none;">
                            <div class="spinner-border text-primary"></div>
                        </div>
                             
                        <div class="row g-2 pt-4">
                            <div class="col-lg-12">
                                <div class="pricing-btn">
                                        
                                    <a id="load-more-btn"  class="btn ss-btn"> Load More ({{ $remaining }} left)</a>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
                        

                <div class="tab-pane fade " id="id-add" role="tabpanel" aria-labelledby="id-add-in">
                    <div class="additional-info">
                        <div class="row g-2" id="passed-zip-container">
                            @include('partials.zip-items')
                        </div>

                        <div id="loading" class="text-center my-3" style="display:none;">
                            <div class="spinner-border text-primary"></div>
                        </div>
                    </div>
                      
                </div>
                
            </div>
        </div>
    </div>
</div>
<div id="mapDrawer" class="map-drawer">
    <button id="closeMapDrawer" class="drawer-close">
        <i class="bi bi-x-lg"></i>
    </button>
    <div class="row">
        <div class="col-lg-12" >
            <div id="map"></div>
        </div>
    </div>
</div>
@endsection


@section('script')

<script src="https://unpkg.com/@mapbox/polyline@1.1.1/src/polyline.js"></script>
<script src="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.js"></script>

<script>
let page = 2;
let loading = false;
$('#load-more-btn').on('click', function () {

    if (loading) return;

    loading = true;
    $('#loading').show();
    $('#load-more-btn').text('Loading...');

    $.ajax({
        url: '?page=' + page,
        type: 'GET',
        success: function (response) {

            if ($.trim(response.html) === '') {
                $('#load-more-btn').text('No more data').prop('disabled', true);
                $('#loading').hide();
                return;
            }

            $('#todo-zip-container').append(response.html);
            page++;

            let remaining = response.remaining;

            if (remaining > 0) {
                $('#load-more-btn').text('Load More (' + remaining + ' left)');
            } else {
                $('#load-more-btn').text('No more data').prop('disabled', true);
            }

            loading = false;
            $('#loading').hide();
        },
        error: function () {
            loading = false;
            $('#loading').hide();
            $('#load-more-btn').text('Load More');
        }
    });
});

// $(window).on('scroll', function () {

//     if (loading) return;

//     if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {

//         loading = true;
//         $('#loading').show();

//         $.ajax({
//             url: '?page=' + page,
//             type: 'GET',
//             success: function (data) {

//                 if ($.trim(data) === '') {
//                     $(window).off('scroll'); 
//                     return;
//                 }

//                 $('#passed-zip-container').append(data);
//                 page++;
//                 loading = false;
//                 $('#loading').hide();
//             },
//             error: function () {
//                 loading = false;
//                 $('#loading').hide();
//             }
//         });
//     }
// });

    const $drawer  = $('#mapDrawer');
    const $overlay = $('#drawerOverlay');
    var selectedZip='';
    $(document).on('click', '.open-map-drawer', function (e) {
        e.preventDefault();
        e.stopPropagation();   
        const zipcode = $(this).data('id');
      
        loadActivityMap(zipcode);
         $.ajax({
            url: "{{ route('account.getSingleZipview') }}",
            type: "POST",
            data: {
                zipcode: zipcode,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
            let selectedZip = response.selectedZip;

            // Only parse if string
            if (typeof selectedZip === 'string') {
                selectedZip = JSON.parse(selectedZip);
            }
          
              loadActivityMap(selectedZip,zipcode);
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

     let map = null;
    let routeLine = null;
    let zipLayer = null;
   
    function loadActivityMap(selectedZip,zipcode) {
                
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

         try {
           
                if (!selectedZip) {
                    console.warn("ZIP not found:", zipcode);
                } else {

                    requestAnimationFrame(() => {
                       console.log(selectedZip);
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
                                html: zipcode,
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

       
    }

</script>
@endsection
