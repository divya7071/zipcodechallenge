
<!doctype html>
<html class="no-js" lang="zxx">
 <head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Zipcode Challenge</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image" href="{{ url("front/img/favicon.png")}}">
    <!-- Place favicon.ico in the root directory -->

    <!-- CSS here -->
    <link rel="stylesheet" href="{{ url("front/css/bootstrap.min.css")}}">
    <link rel="stylesheet" href="{{ url("front/css/animate.min.css")}}">
    <link rel="stylesheet" href="{{ url("front/css/magnific-popup.css")}}">
    <link rel="stylesheet" href="{{ url("front/fontawesome/css/all.min.css")}}">
    
    <link rel="stylesheet" href="{{ url("front/css/dripicons.css")}}">
    <link rel="stylesheet" href="{{ url("front/css/slick.css")}}">
    <link rel="stylesheet" href="{{ url("front/css/meanmenu.css")}}">
    <link rel="stylesheet" href="{{ url("front/css/default.css")}}">
    <link rel="stylesheet" href="{{ url("front/css/style.css")}}">
    <link rel="stylesheet" href="{{ url("front/css/responsive.css")}}">
    <!-- <link rel="stylesheet" href="{{ url("front/css/dashboard.css")}}"> -->
     <link rel="stylesheet" href="{{ url("dist/css/app.min.css") }}" type="text/css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .profile-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            overflow: hidden;
            display: inline-flex;
            cursor: pointer;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

    </style>
    </head>
    <body>
  
   <main class="main">
    <div class="dashboard-layout">
    <!-- Topbar -->
    <div class="dashboard-sidebar">
    <div class="sidebar">
        <div class="sb-logo">
            <!-- <div class="sb-logo-icon">
                <a href="{{route('home')}}"><img src="{{ url("front/img/logo/logo.png")}}" alt="logo"></a>
                 
            </div> -->
           
            <div class="sb-logo-icon"></div>
            <div class="sb-logo-text">Zipcode<br>Challenge</div>
        </div>
       
        <nav class="sb-nav">
            <div class="sb-section">Main</div>
             <a href="{{ route('home') }}" 
            class="sb-item {{ request()->routeIs('home') ? 'active' : '' }}">
                Home
            </a>
            <a href="{{ route('dashboard') }}" 
            class="sb-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="{{ route('activity.index') }}" 
            class="sb-item {{ request()->routeIs('activity.*') ? 'active' : '' }}">
                Activities
            </a>

            <a href="{{ route('passed-zips') }}" 
            class="sb-item {{ request()->routeIs('passed-zips') ? 'active' : '' }}">
                Passed ZIP codes
            </a>
              <a href="{{ route('todo-zips') }}" 
            class="sb-item {{ request()->routeIs('todo-zips') ? 'active' : '' }}">
                To Do ZIP codes
            </a>
            <a href="{{ route('leaderboard') }}" 
            class="sb-item {{ request()->routeIs('leaderboard') ? 'active' : '' }}">
               Leaderboard
            </a>
            <a href="{{ route('explore-map') }}" 
            class="sb-item {{ request()->routeIs('explore-map') ? 'active' : '' }}">
                Your map
            </a>

            <div class="sb-section">Account</div>
            <a href="javascript:;"   class="sb-item"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form"
                                            action="{{ route('athlete.logout') }}"
                                            method="POST"
                                            style="display: none;">
                                            @csrf
                                        </form>
           
            <a href="{{ route('how-it-works') }}" 
            class="sb-item {{ request()->routeIs('how-it-works') ? 'active' : '' }}">
                How it works
            </a>
        </nav>
         <div class="sb-user">
            <div class="sb-avatar"><img src="{{ !empty(auth('athlete')->user()->profile_medium)?auth('athlete')->user()->profile_medium : asset('front/img/general/account.jpg') }}" class="profile-avatar" alt="Profile"> </div>
            <div>
                <div class="sb-user-name">{{auth('athlete')->user()->first_name.' '.auth('athlete')->user()->last_name}} </div>
                <div class="sb-user-sub">Strava connected</div>
            </div>
        </div>
    </div>
    <div class="sidebar-overlay"></div>
    </div>
    <div class="dashboard-content">
        <div class="topbar">

            <div class="topbar-left">
                <div class="topbar-title">Welcome back, {{auth('athlete')->user()->first_name.' '.auth('athlete')->user()->last_name}}</div>
                        @if(auth('athlete')->user()->activities->last())
                        @php
                        $lastActivity = auth('athlete')->user()->activities()->orderByDesc('created_at')->first();
                        @endphp
                        <div class="topbar-sub">Your last activity synced {{$lastActivity->created_at->format('F, j Y h:i A')}}</div>
                        @endif
            </div>

            <div class="topbar-right">
                
                <div class="sync-btn">
                    @if(auth('athlete')->user()->is_syncing)
                    <span class="sync-dot" style="background-color:red;"></span> Syncing
                    @else
                     <span class="sync-dot" style="background-color:green;"></span> Synced
                    @endif
                </div>

                <a class="view-all-btn" href="{{route('activity.index')}}">
                    View all activity
                </a>

                <button class="menu-btn toggle-btn d-md-none">
                    ☰
                </button>

            </div>

        </div>
    
        @yield('content')
    </div>
    </div>
    </main>
   
    <script src="{{ url("front/js/vendor/modernizr-3.5.0.min.js")}}"></script>
    <script src="{{ url("front/js/vendor/jquery-3.6.0.min.js")}}"></script>
    <script src="{{ url("front/js/popper.min.js")}}"></script>
    <script src="{{ url("front/js/bootstrap.min.js")}}"></script>
    <script src="{{ url("front/js/slick.min.js")}}"></script>
    <script src="{{ url("front/js/ajax-form.js")}}"></script>
    <script src="{{ url("front/js/paroller.js")}}"></script>
    <script src="{{ url("front/js/wow.min.js")}}"></script>
    <script src="{{ url("front/js/js_isotope.pkgd.min.js")}}"></script>
    <script src="{{ url("front/js/imagesloaded.min.js")}}"></script>
    <script src="{{ url("front/js/parallax.min.js")}}"></script>
    <script src="{{ url("front/js/jquery.waypoints.min.js")}}"></script>
    <script src="{{ url("front/js/jquery.counterup.min.js")}}"></script>
    <script src="{{ url("front/js/jquery.scrollUp.min.js")}}"></script>
    <script src="{{ url("front/js/jquery.meanmenu.min.js")}}"></script>
    <script src="{{ url("front/js/parallax-scroll.js")}}"></script>
    <script src="{{ url("front/js/jquery.magnific-popup.min.js")}}"></script>
    <script src="{{ url("front/js/element-in-view.js")}}"></script>
    <script src="{{ url("front/js/main.js")}}"></script>
    @yield('script')
    @stack('scripts')
    <script>
$(document).ready(function () {
    $('.toggle-btn').on('click', function () {
        $('.sidebar').toggleClass('active');
        $('.sidebar-overlay').toggleClass('active');
    });

    $('.sidebar-overlay').on('click', function () {
        $('.sidebar').removeClass('active');
        $(this).removeClass('active');
    });
});
</script>
</body>
</html>