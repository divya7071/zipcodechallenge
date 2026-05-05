 <!-- header -->
<header class="header-area header-three">  
        <div id="header-sticky" class="menu-area">
        <div class="container">
            <div class="second-menu">
                <div class="row align-items-center">
                    <div class="col-xl-2 col-lg-2">
                        <div class="logo">
                            <a href="{{route('home')}}"><img src="{{ url("front/img/logo/logo.png")}}" alt="logo"></a>
                        </div>
                    </div>
                    <div class="col-xl-7 col-lg-7">
                        
                        <div class="main-menu text-center text-xl-right">
                            <nav id="mobile-menu">
                                    <ul>
                                    <li>
                                        <a href="{{route('home')}}">Home</a>
                                        
                                    </li>
                                    <li><a href="{{route('how-it-works')}}">How It Works</a></li>        
                                     <li><a href="{{route('contact')}}">Contact</a></li>        
                                    @if(auth('athlete')->check()) 
                                    <li><a href="{{route('account.dashboard')}}">Dashboard</a></li>    
                                    <li><a  href="{{route('account.explore-map')}}">Explore on Map</a></li>    
                                    <!-- <li><a href="{{route('account.leaderboard')}}">Leader Board</a></li>     -->
                                    <li><a  href="{{route('account.todo-zips')}}">Todo Zips</a></li>    
                                @endif   
                                    @if(auth('athlete')->check())
                                    <li class="has-sub nav-item d-block d-md-none"> 
                                        <a><img src="{{ !empty(auth('athlete')->user()->profile_medium)?auth('athlete')->user()->profile_medium : asset('front/img/general/account.jpg') }}" class="profile-avatar" alt="Profile"> {{auth('athlete')->user()->first_name.' '.auth('athlete')->user()->last_name}} </a>
                                        <ul>
                                        <li> <a href="javascript:;"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form"
                                            action="{{ route('account.athlete.logout') }}"
                                            method="POST"
                                            style="display: none;">
                                            @csrf
                                        </form>
                                    </li>
                                    @endif
                                        @if(!auth('athlete')->check()) 
                                    <li class="nav-item d-block d-md-none">
                                        <div class="logo text-center " >   
                                        <a  href="{{route('athlete.strava.login')}}" ><img src="{{ url("front/img/strava-connect/btn_strava_connect_with_orange.png")}}"
                                            alt=""></a>
                                        </div>
                                    </li>
                                @endif
                                    <!-- <li><a href="contact.html">Contact</a></li>   -->
                                                                            
                                </ul>
                            </nav>
                        </div>
                    </div>   
                    
                        <div class="col-xl-3 col-lg-3 d-none d-lg-block">
                            <div class="main-menu text-center text-xl-right">
                            
                                    <ul>
                                    @if(auth('athlete')->check()) 
                                    <li class="has-sub"> 
                                        <a><img src="{{ auth('athlete')->user()->profile_medium ?? asset('front/img/general/account.jpg') }}" class="profile-avatar" alt="Profile" onerror="this.onerror=null; this.src='{{asset('front/img/general/account.jpg')}}';"> {{auth('athlete')->user()->first_name.' '.auth('athlete')->user()->last_name}} </a>
                                        <ul>
                                        <li> <a href="javascript:;"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            Logout
                                        </a>

                                        <form id="logout-form"
                                            action="{{ route('account.athlete.logout') }}"
                                            method="POST"
                                            style="display: none;">
                                            @csrf
                                        </form>
                                    </li>
                                            
                                </ul>
                                </li>
                                @endif     
                                
                            </ul>
                            </div>
                            <div class="right-menu">
                            
                            <ul>
                                @if(!auth('athlete')->check()) 
                                <li>
                                    <div class="logo text-center " >   
                                    <a  href="{{route('athlete.strava.login')}}" ><img src="{{ url("front/img/strava-connect/btn_strava_connect_with_orange.png")}}"
                                        alt=""></a>
                                    </div>
                                </li>
                                @endif
                            </ul>
                            
                            </div>
                    </div>
                        
                        <div class="col-12">
                            <div class="mobile-menu">
                                
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- header-end -->
<!-- offcanvas-area -->
<div class="offcanvas-menu">
    <span class="menu-close"><i class="fas fa-times"></i></span>
    <form role="search" method="get" id="searchform"   class="searchform" action="http://wordpress.zcube.in/xconsulta/">
                    <input type="text" name="s" id="search" value="" placeholder="Search"  />
                    <button><i class="fa fa-search"></i></button>
                </form>

        
        <div id="cssmenu3" class="menu-one-page-menu-container">
            <ul  class="menu">
                <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="index-2.html">Home</a></li>
                <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="about.html">About Us</a></li>
                <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="services.html">Services</a></li>
                    <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="pricing.html">Pricing </a></li>
                    <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="team.html">Team </a></li>
                    
                    <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="projects.html">Cases Study</a></li>
                    <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="blog.html">Blog</a></li>
                <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="contact.html">Contact</a></li>
            </ul>
        </div>  
        
        <div id="cssmenu2" class="menu-one-page-menu-container">
            <ul id="menu-one-page-menu-12" class="menu">
                <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="#home"><span>+8 12 3456897</span></a></li>
                <li class="menu-item menu-item-type-custom menu-item-object-custom"><a href="#howitwork"><span>info@example.com</span></a></li>
            </ul>
        </div>                
</div>
<div class="offcanvas-overly"></div>
<!-- offcanvas-end -->
    