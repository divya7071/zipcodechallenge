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
                            <h2>Passed Zips</h2>    
                            <div class="breadcrumb-wrap">
                        
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Passed Zips </li>
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
                        <div class="bakix-details-tab">
                        <ul class="nav text-center justify-content-center pb-30 mb-50">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('todo-zips') ? 'active' : '' }}"
                                    href="{{ route('todo-zips') }}">
                                        Todo ZIPs
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('passed-zips') ? 'active' : '' }}"
                                    href="{{ route('passed-zips') }}">
                                        Completed ZIPs
                                    </a>
                                </li>
                        </ul>
                        </div>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade" id="id-desc" role="tabpanel" aria-labelledby="desc-tab">
                                <div class="event-text mb-40">
                                     <div class="table-responsive">
                                        <div class="container">
                                            <div class="row g-2" id="todo-zip-container">
                                                @include('partials.zip-items')
                                            </div>

                                        <div id="loading" class="text-center my-3" style="display:none;">
                                            <div class="spinner-border text-primary"></div>
                                        </div>
                                        </div> 
                                     </div>
                                </div>
                            </div>
                            <div class="tab-pane fade active show" id="id-add" role="tabpanel" aria-labelledby="id-add-in">
                                <div class="additional-info">
                                     <div class="table-responsive">
                                        <div class="container">
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
            </div>
    </section>
    <!-- breadcrumb-area-end -->
        <!-- faq-area -->
<!-- <section id="faq" class="faq-area pt-120 pb-120">             
        <div class="container">   
            <div class="row">
                <div class="col-lg-12 col-md-6">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-body py-3">
                                 <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link " href="{{ route('todo-zips') }}">
                Todo ZIPs
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('passed-zips') }}">
                Completed ZIPs
            </a>
        </li>
    </ul>
     <div class="mb-4">
       
       <div class="table-responsive">
        <div class="container">
            <div class="row g-2" id="zip-container">
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
</section> -->
<div id="mapDrawer" class="map-drawer">
  
        <div class="row">
        <div class="col-lg-12 position-relative">

        <!-- <span id="closeMapDrawer"
            class="position-absolute top-0 end-0 m-2"
            style="cursor:pointer; font-size:20px; font-weight:bold; z-index:1000;">
            ✕
        </span> -->

        </div>
        <div class="col-lg-12" >
        <div id="map"></div>
        <span id="closeMapDrawer"
            style="
                position:absolute;
                top:60px;
                right:17px;
                z-index:1000;
                cursor:pointer;
                font-size:22px;
                font-weight:bold;
                background:#fff;
                padding:2px 8px;
                
            ">
            ✕
        </span>
        </div>
        </div>
</div>
</main>
     
@endsection



@section('script')
<!-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


<script src="https://unpkg.com/@mapbox/polyline@1.1.1/src/polyline.js"></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
<script src="https://unpkg.com/leaflet.fullscreen@2.4.0/Control.FullScreen.js"></script>

<script>
let page = 2;
let loading = false;

$(window).on('scroll', function () {

    if (loading) return;

    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 100) {

        loading = true;
        $('#loading').show();

        $.ajax({
            url: '?page=' + page,
            type: 'GET',
            success: function (data) {

                if ($.trim(data) === '') {
                    $(window).off('scroll'); 
                    return;
                }

                $('#passed-zip-container').append(data);
                page++;
                loading = false;
                $('#loading').hide();
            },
            error: function () {
                loading = false;
                $('#loading').hide();
            }
        });
    }
});

    const $drawer  = $('#mapDrawer');
    const $overlay = $('#drawerOverlay');
    var selectedZip='';
    $(document).on('click', '.open-map-drawer', function (e) {
        e.preventDefault();
        e.stopPropagation();   
        const zipcode = $(this).data('id');
      
        loadActivityMap(zipcode);
         $.ajax({
            url: "{{ route('getSingleZipview') }}",
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
