<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zipcode Challenge - Dashboard</title>
    <link rel="shortcut icon" type="image" href="{{ url("front/img/favicon.png")}}">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="{{ url("front/css/bootstrap.min.css")}}">
    <link rel="stylesheet" href="{{ url("front/css/animate.min.css")}}">
    <link rel="stylesheet" href="{{ url("front/fontawesome/css/all.min.css")}}">
    <link rel="stylesheet" href="{{ url("front/css/default.css")}}">
    <link rel="stylesheet" href="{{ url("front/css/style.css")}}">
    <link rel="stylesheet" href="{{ url("front/css/responsive.css")}}">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <style>
        body {
            background: #f5f6f8;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #0c2957;
            position: fixed;
            color: #fff;
            padding: 20px;
            transition: 0.3s;
            display: flex;
            justify-content: space-between;
            flex-direction: column;
            overflow-y: scroll;
        }

        .sidebar h5 {
            font-weight: 600;
        }

        .sidebar a {
            display: block;
            padding: 10px;
            color: #ccc;
            text-decoration: none;
            border-radius: 6px;
        }

        .sidebar a.active,
        .sidebar a:hover {
            background: #fc5200;
            color: #fff;
        }

        /* Mobile Sidebar */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1000;
            }

            .sidebar.show {
                transform: translateX(0);
            }
        }

        /* Main */
        .main {
            margin-left: 250px;
            padding: 20px;
            transition: 0.3s;
        }

        @media (max-width: 992px) {
            .main {
                margin-left: 0;
            }
        }

        /* Cards */
        .metric-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #fc5200;
        }

        .border-blue {
            border-color: #185FA5;
        }

        .border-green {
            border-color: #3B6D11;
        }

        .border-purple {
            border-color: #534AB7;
        }

        .border-orange {
            border-color: #E84B1A;
        }


        /* Table card */
        .card-box {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
        }

        /* Buttons */

        .dashboard .btn {
            padding: 10px 20px;
            font-weight: 600;
        }


        /* Small text */
        .small-text {
            font-size: 12px;
            color: #777;
        }


        /* Mobile adjustments */
        @media (max-width: 576px) {
            h5 {
                font-size: 16px;
            }

            .btn {
                font-size: 12px;
                padding: 6px 10px;
            }
        }

        .sidebar .navigation {
            flex: 1;
        }

        .dashboard table .btn {
            padding: 7px 15px;
            font-size: 14px;
            text-transform: capitalize;
            font-weight: 400;
        }

        .dashboard table th {
            font-weight: 700;
            font-family: 'Rajdhani', sans-serif;
        }

        .dashboard table td,
        .dashboard table th {
            vertical-align: middle !important;
        }


        /* Map Drawer Styles */
        /* ===== MAP ===== */
        #map {
            width: 100%;
            height: 100vh;
            /* responsive height */
        }
       #activityMap {
            width: 100%;
            height: 100vh;
            
        }
        /* ===== MAP INFO BAR ===== */
        /*#mapInfoBar {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 900;
}*/
        /* ===== ZIP LABEL ===== */
        .zip-text-label {
            font-size: 10px;
            font-weight: 600;
            color: #6b7280;
            background: transparent;
            border: none;
            box-shadow: none;
            pointer-events: none;
            text-shadow: 0 0 2px #fff;
        }


        /* ===== DRAWER ===== */
        #drawerOverlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1000;
            display: none;
        }

        .map-drawer {
            position: fixed;
            top: 0;
            right: -100%;
            width: 400px;
            max-width: 100%;
            height: 100%;
            background: #fff;
            z-index: 1001;
            transition: right 0.3s ease;
            box-shadow: -4px 0 10px rgba(0, 0, 0, 0.2);
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


        /* ===== MOBILE ===== */
        @media (max-width: 768px) {
            .map-drawer {
                width: 100%;
            }

            #map {
                height: 50vh;
            }
        }

        .dashboard table i {
            font-size: 14px;
            margin-left: 0;
        }

        #closeMapDrawer,
        .drawer-close {
            position: absolute;
            left: -30px;
            background: #fc5200;
            width: 30px;
            height: 30px;
            padding: 0;
            border-radius: 0;
            color: #FFFFFF;
            border: 0;
        }

        @media (max-width: 768px) {

            #closeMapDrawer,
            .drawer-close {
                left: unset;
                right: 0px;
            }
        }

        .drawer-close:hover {
            background: #f1f3f6;
        }

        /*//////loader/////*/

        /* FULL PAGE OVERLAY */
        .loader-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: #ffffff;
            z-index: 9999;

            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;

            font-family: system-ui, sans-serif;
        }

        /* FADE OUT */
        .loader-container.hide {
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.6s ease;
        }

        /* SVG PATH */
        .path-svg {
            width: 100%;
            max-width: 500px;
            height: 120px;
        }

        .loader-container svg path {
            fill: none;
            stroke: #e0e0e0;
            stroke-width: 3;
        }

        #journeyPath {
            stroke-dasharray: 500;
            stroke-dashoffset: 500;
            animation: drawPath 4s linear forwards;
        }

        @keyframes drawPath {
            to {
                stroke-dashoffset: 0;
            }
        }

        #progressDot {
            fill: #ff5a5f;
            transition: cx 0.4s ease, cy 0.4s ease;
        }

        /* TEXT */
        .status {
            margin-top: 20px;
            text-align: center;
        }

        #step-title {
            font-size: 18px;
            font-weight: 600;
        }

        #step-sub {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        /* PULSE ANIMATION */
        .pulse {
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 0.4;
            }

            50% {
                opacity: 1;
            }

            100% {
                opacity: 0.4;
            }
        }
        .start-label {
            background: #16a34a;
            color: #fff;
            border: none;
            padding: 2px 6px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .end-label {
            background: #dc2626;
            color: #fff;
            border: none;
            padding: 2px 6px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo mb-5">
            <img src="{{ url("front/img/logo/logo-white.png")}}" alt="logo">

        </div>
        <div class="navigation">
            <small class="text-muted">MAIN</small>
            <a href="{{ route('home') }}"
                class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
            <a href="{{ route('account.dashboard') }}"
                class="{{ request()->routeIs('account.dashboard') ? 'active' : '' }}" class="active">Dashboard</a>
            <a href="{{ route('account.activity.index') }}"
                class="{{ request()->routeIs('account.activity.*') ? 'active' : '' }}">Activities</a>
            <a href="{{ route('account.passed-zips') }}"
                class="{{ request()->routeIs('account.passed-zips') ? 'active' : '' }}">Passed Zipcodes</a>
            <a href="{{ route('account.todo-zips') }}"
                class="{{ request()->routeIs('account.todo-zips') ||request()->routeIs('account.completed-zips')? 'active' : '' }}">Todo Zipcodes</a>
            <!-- <a href="{{ route('account.leaderboard') }}" 
            class="{{ request()->routeIs('account.leaderboard') ? 'active' : '' }}">Leaderboard</a> -->
            <a href="{{ route('account.explore-map') }}"
                class="{{ request()->routeIs('account.explore-map') ? 'active' : '' }}">Your map</a>
            <br>
            <small class="text-muted">ACCOUNT</small>
        
            <a href="javascript:;" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a>
            <form id="logout-form"
                action="{{ route('account.athlete.logout') }}"
                method="POST"
                style="display: none;">
                @csrf
            </form>
            <!-- <a href="{{ route('how-it-works') }}" class="{{ request()->routeIs('how-it-works') ? 'active' : '' }}">How it works</a> -->
        </div>
        <div class="mt-5">
            <strong>{{auth('athlete')->user()->first_name.' '.auth('athlete')->user()->last_name}}</strong><br>
            <small class="text-muted">Strava connected</small>
        </div>
    </div>
    <div class="main dashboard">
        
        <!-- Header -->
   
        <div class="d-flex justify-content-between align-items-center mb-4">

            <div class="d-flex align-items-center gap-2">
                <!-- Mobile Toggle -->
                <i class="bi bi-list fs-4 d-lg-none" onclick="toggleSidebar()"></i>

                <div>
                    <h5 class="mb-0">Welcome back, {{auth('athlete')->user()->first_name.' '.auth('athlete')->user()->last_name}}</h5>
                    @if(auth('athlete')->user()->activities->last())
                    @php
                    $lastActivity = auth('athlete')->user()->activities()->orderByDesc('created_at')->first();
                    @endphp
                    <div class="small-text">Last synced {{$lastActivity->created_at->format('F, j Y h:i A')}}</div>
                    @else
                    <div class="small-text">Last synced </div>
                    @endif
                </div>
            </div>
            <div id="syncContainer" class="mb-3">

            <div id="syncLoading" class="alert alert-info d-none d-flex align-items-center">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                <span>Fetching activities...</span>
            </div>
            <div id="syncSuccess" class="alert alert-success d-none">
                Activities synced successfully 
            </div>
          </div>
        <div class="d-flex align-items-center gap-2">
                @if(auth('athlete')->user()->is_syncing==1)
                <span class="text-danger d-none d-md-inline">● Syncing</span>
                @else
                <span class="text-success d-none d-md-inline">● Synced</span>
                @endif
                <a class="btn btn-strava btn-sm" href="{{route('account.activity.index')}}">View all activity</a>
            </div>

        </div>
        @yield('content')
    </div>
    <script src="{{ url("front/js/vendor/jquery-3.6.0.min.js")}}"></script>
    <script src="{{ url("front/js/bootstrap.min.js")}}"></script>
    <script src="{{ url("front/js/main.js")}}"></script>

    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://unpkg.com/laravel-echo@2.3.4/dist/echo.iife.js"></script>

    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Turf.js -->
    <script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>

    @yield('script')
    @stack('scripts')
    <script>
        const sidebar = document.querySelector('.sidebar');

        // Toggle function (keep this)
        function toggleSidebar() {
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isToggleButton = event.target.closest('.bi-list');

            if (!isClickInsideSidebar && !isToggleButton && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
    </script>

</body>

</html>