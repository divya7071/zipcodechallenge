@extends('layouts.app') 
   
@section('content')

<main>
    <!-- slider-area -->
    <section id="home" class="slider-area fix p-relative">
        
        <div class="slider-active" style="background: #00173c;">
        <div class="single-slider slider-bg d-flex slider-bg-three align-items-center" style="background-image: url('{{ url('front/img/slider/slider_bg_01.png') }}');" background-size: cover;">
                <div class="container">
                    <div class="row justify-content-center align-items-center">
                        
                        <div class="col-lg-7 col-md-7">
                            <div class="slider-content s-slider-content mt-20">
                                    <h5 data-animation="fadeInUp" data-delay=".4s">welcome To Zipcode Challenge</h5>
                                    <h2 data-animation="fadeInUp" data-delay=".4s">Join the movement - moving with purpose, one ZIP code at a time</h2>
                                <p data-animation="fadeInUp" data-delay=".6s">DA simple movement challenge that turns everyday activity into exploration—helping you get outdoors, discover new ZIP codes, and reconnect with the places you travel through.</p>
                                
                                    <div class="slider-btn mt-30 mb-105">     
                                    <a href="{{route('contact')}}" class="btn ss-btn mr-15" data-animation="fadeInLeft" data-delay=".4s">Discover More <i class="fal fa-angle-right"></i></a>
                                    
                                </div>        
                                                                        
                            </div>
                        </div>
                        <div class="col-lg-5 col-md-5 p-relative">
                        </div>
                        
                    </div>
                </div>
                            <!-- video -->
                <!-- <video id="my-video" class="video2" muted loop autoplay>
                <source src="{{ url("front/img/slider/slider-vedio.mp4")}}" type="video/mp4">
                <source src="{{ url("front/img/slider/slider-vedio.html")}}" type="video/ogg">
                <source src="{{ url("front/img/slider/slider-vedio-2.html")}}" type="video/webm">
            </video> -->
            </div>

            
            </div>
            
        
    </section>
    <!-- slider-area-end -->
                <!-- about-area -->
        <!-- <section class="about-area about-p pt-120 pb-120 p-relative fix">
                <div class="container">
                <div class="row justify-content-center align-items-center">
                        <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="s-about-img p-relative  wow fadeInLeft animated" data-animation="fadeInLeft" data-delay=".4s">
                            <img src="{{ url("front/img/bg/about.png")}}" alt="img">   
                            
                        </div>
                        
                    </div>
                                        
                </div>
            </div>
        </section> -->
        <!-- about-area-end -->
            <!-- service-area -->
        <section style="background: #f7fafd;" class="service-details pt-90 pb-60 p-relative">
            <div class="container">
                <div class="row">
                    
                <div class="about-title second-title pb-25">  
                    <h5>How it Works</h5>
                    <!-- <h2>Make your car feel like a brand new one</h2>                                    -->
                </div>
                    <div class="col-lg-4 col-md-12 col-sm-12">
                        <div class="services-box07 mb-30">
                            
                            <div class="sr-contner">
                            <div class="icon">
                            <img src="{{ url("front/img/icon/sve-icon4.png")}}" alt="icon01">
                            </div>
                            <div class="text">
                                <h5>Connect with Strava</h5>
                                <p>Link your Strava account to get started.</p>
                            </div>
                            </div>
                            
                            
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 col-sm-12">
                        <div class="services-box07 mb-30">
                            <div class="sr-contner">
                            <div class="icon">
                            <img src="{{ url("front/img/icon/sve-icon5.png")}}" alt="icon01">
                            </div>
                            <div class="text">
                                <h5>Unlock ZIP Codes</h5>
                                <p>Explore Unlock ZIP Codes Walks, run, ride anywhere.</p>
                            </div>
                            </div>
                            
                        </div>
                    </div>
                    
                    <div class="col-lg-4 col-md-12 col-sm-12">
                            <div class="services-box07 mb-30">
                                <div class="sr-contner">
                                <div class="icon">
                                <img src="{{ url("front/img/icon/sve-icon6.png")}}" alt="icon01">
                                </div>
                                <div class="text">
                                    <h5>Track Progress </h5>
                                    <p>Track progress & Join the Community See your ZIP code journey grow.</p>
                                </div>
                                </div>
                                
                            </div>
                    </div>
                
                    
                </div>
            </div>
        </section>
        <!-- service-details2-area-end -->
        
            <!-- services-five-area -->
        <section id="services-05" class="services-05 services-09 pt-100 pb-100 p-relative" style="background-image: url({{ url('front/img/bg/approch-bg.png') }}); background-repeat: no-repeat; background-color: #f7fafd; background-size: contain; border-bottom: 1px solid #efefef; ">
            <div class="container">
                <div class="row align-items-center">
    <div class="col-lg-6 col-md-6">
                        <div class="section-title center-align mb-50 text-left">
                                <h5>Our Approch</h5>  
                            <h2>
                            AUTO SERVICING
                            </h2>
                            
                        </div>
                        
                    </div>
                    <div class="col-lg-6 col-md-6">
                        <div class="section-title center-align mb-50 text-left">
                            <p>Improve efficiency, leverage tech, and provide better customer experiences with the modern technology services available allover the world.  </p>
                            
                        </div>
                        
                    </div>
                    
                                <div class="col-lg-4 col-md-4">
                                <div class="services-box-05">
                                    
                                    <div class="services-icon-05">
                                        <img src="{{ url("front/img/bg/services-01.png")}}" alt="icon01">
                                        
                                    </div>
                                    <div class="services-content-05">
                                        <span>Services</span>
                                        <h4><a href="single-service.html">Performance Upgrades</a></h4> 
                        
                                        </div>
                                    </div> 
                            </div>
                            <div class="col-lg-4 col-md-4">
                                <div class="services-box-05">
                                    
                                    <div class="services-icon-05">
                                        <img src="{{ url("front/img/bg/services-02.png")}}" alt="icon01">
                                    </div>
                                    <div class="services-content-05">
                                        <span>Services </span>
                                        <h4><a href="single-service.html">Auto Car Repair</a></h4> 
                        
                                        </div>
                                    </div> 
                            </div>
                    <div class="col-lg-4 col-md-4">
                                <div class="services-box-05">
                                    
                                    <div class="services-icon-05">
                                        <img src="{{ url("front/img/bg/services-03.png")}}" alt="icon01">
                                    </div>
                                    <div class="services-content-05">
                                        <span>Services  </span>
                                        <h4><a href="single-service.html">Crash Car Repair</a></h4> 
                        
                                        </div>
                                    </div> 
                            </div>
                    
                        
                        
                        
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                        <div class="services-text05">
                            <p>Stop wasting time and money on technology.</p> 
                            <a href="#">Explore our company</a>
                            </div>
                            
                        </div>
                        <div class="col-md-6 text-right"> <a href="#" class="btn ss-btn" data-animation="fadeInRight" data-delay=".8s">Request Demo</a>		</div>
                    </div>
                
                    
                </div>
        </section>
        <!-- services-three-area -->
            <!-- service-details2-area -->
        <section id="service-details2" class="pt-120 pb-105 p-relative" style="background: url({{ url('front/img/bg/services-bg.html') }}); background-size: contain; background-position: center center; background-repeat: no-repeat;">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-12">
                        <div class="section-title center-align mb-50 text-center">
                            <h5>Our Services</h5>
                            <h2>
                                What We Provide
                            </h2>
                            
                        </div>
                        
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="about-content s-about-content">
                        
                            <ul class="sr-tw-ul ">
                            <li>
                                <div class="icon-right"><img src="{{ url("front/img/icon/fe-icon01.png")}}" alt="icon01"></div>
                                <div class="text">
                                    <h4><a href="single-service.html">Accident Insurance </a></h4> 
                                    <p>Aenean eleifend turpis tellus, nec laoreet metus elementum ac.</p>
                                </div>
                                    
                            </li>
                            <li>
                                    <div class="icon-right"><img src="{{ url("front/img/icon/fe-icon05.png")}}" alt="icon01"></div>
                                <div class="text">
                                    <h4><a href="single-service.html">Fire Insurance </a></h4> 
                                    <p>Aenean eleifend turpis tellus, nec laoreet metus elementum ac.</p>
                                </div>
                                
                            </li>
                                <li>
                                <div class="icon-right"><img src="{{ url("front/img/icon/fe-icon07.png")}}" alt="icon01"></div>
                                <div class="text">
                                    <h4><a href="single-service.html">Hail Damage </a></h4> 
                                    <p>Aenean eleifend turpis tellus, nec laoreet metus elementum ac.</p>
                                </div>
                                    
                            </li>
                            
                            
                        </ul>
                            
                    
                        </div>
                    </div>
                        <div class="col-lg-4 col-md-12 col-sm-12 text-center d-none d-lg-block">
                        <div class="sd-img">
                            <img src="{{ url("front/img/features/services-img-details2.png")}}" alt="img">    
                        </div>
                        
                    </div>
                    
                <div class="col-lg-4 col-md-6 col-sm-12">
                        <div class="about-content s-about-content">
                        
                                <ul class="sr-tw-ul sr-thr-ul">
                            <li>
                                <div class="icon"><img src="{{ url("front/img/icon/fe-icon04.png")}}" alt="icon01"></div>
                                    <div class="text pt-10">
                                    <h4><a href="single-service.html">Flood Insurance </a></h4> 
                                    <p>Aenean eleifend turpis tellus, nec laoreet metus elementum ac.</p>
                                </div>
                            </li>
                            <li>
                                    <div class="icon"><img src="{{ url("front/img/icon/fe-icon06.png")}}" alt="icon01"></div>
                                    <div class="text pt-10">
                                    <h4><a href="single-service.html">Car Towing </a></h4> 
                                    <p>Aenean eleifend turpis tellus, nec laoreet metus elementum ac.</p>
                                </div>
                            </li>
                                <li>
                                    <div class="icon"><img src="{{ url("front/img/icon/fe-icon08.png")}}" alt="icon01"></div>
                                    <div class="text pt-10">
                                    <h4><a href="single-service.html">Motorcycle Towing </a></h4> 
                                    <p>Aenean eleifend turpis tellus, nec laoreet metus elementum ac.</p>
                                </div>
                            </li>
                            
                        </ul>
                            
                    
                        </div>
                    </div>
                    
                </div>
            </div>
        </section>
        <!-- service-details2-area-end -->
        
        <!-- cta-area -->
        <section class="cta-area cta-bg pt-120 pb-120" style="background-image:url({{ url('front/img/bg/cta_bg02.png') }})">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="section-title cta-title wow fadeInLeft animated" data-animation="fadeInDown animated" data-delay=".2s">
                            <h3>Get Our Service</h3>
                            <h2>Get Premium Auto Car Service Feel Free To Contact Us.</h2>
                            
                        </div>
                                            
                    </div>
                        <div class="col-lg-4">
                            <div class="cta-btn s-cta-btn wow fadeInRight animated mt-30" data-animation="fadeInDown animated" data-delay=".2s">
                                    <a href="about.html" class="btn ss-btn smoth-scroll">Get Started <i class="fal fa-angle-right"></i></a>			
                            </div>
                    </div>
                
                </div>
            </div>
        </section>
        <!-- cta-area-end -->	
            <!-- team-area -->
        <section class="team-area2 fix p-relative pt-105 pb-80">  
            <div class="container">  
                <div class="row">   
                    <div class="col-lg-12 p-relative">
                        <div class="section-title center-align mb-50 text-center wow fadeInDown animated" data-animation="fadeInDown" data-delay=".4s">
                            <h5>Our Team</h5>
                            <h2>
                                Best Expert Designer
                            </h2>
                            
                        </div>
                    </div>                        
                        
                </div>
                <div class="row team-active">                   
                    <div class="col-xl-4">
                        <div class="single-team mb-40" >
                            <div class="team-thumb">
                                <div class="brd">
                                        <img src="{{ url("front/img/team/team01.jpg")}}" alt="img">
                                    
                                </div>
                            </div>
                            <div class="team-info">
                                <h4><a href="team-single.html">Howard Holmes</a></h4>
                                <p>Designer</p>
                                <div class="team-social">
                                    <ul>
                                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li> 
                                        <li> <a href="#"><i class="fab fa-twitter"></i></a></li>   
                                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>   
                                    </ul>       
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="single-team mb-40" >
                            <div class="team-thumb">
                                <div class="brd">
                                    <img src="{{ url("front/img/team/team02.jpg")}}" alt="img">
                                </div>                                     
                            </div>
                            <div class="team-info">
                                <h4><a href="team-single.html">Ella Thompson</a></h4>
                                <p>Designer</p>
                                <div class="team-social">
                                    <ul>
                                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li> 
                                        <li> <a href="#"><i class="fab fa-twitter"></i></a></li>   
                                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>   
                                    </ul>       
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="single-team mb-40" >
                            <div class="team-thumb">
                                <div class="brd">
                                    <img src="{{ url("front/img/team/team03.jpg")}}" alt="img">
                                </div>
                                
                            </div>
                            <div class="team-info">
                                <h4><a href="team-single.html">Vincent Cooper</a></h4>
                                <p>Designer</p>
                                <div class="team-social">
                                    <ul>
                                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li> 
                                        <li> <a href="#"><i class="fab fa-twitter"></i></a></li>   
                                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>   
                                    </ul>       
                                </div>
                            </div>
                        </div>
                    </div>
                        <div class="col-xl-4">
                        <div class="single-team mb-40" >
                            <div class="team-thumb">
                                <div class="brd">
                                        <img src="{{ url("front/img/team/team04.jpg")}}" alt="img">
                                </div>
                            
                            </div>
                            <div class="team-info">
                                <h4><a href="team-single.html">Danielle Bryant</a></h4>
                                <p>Designer</p>
                                <div class="team-social">
                                    <ul>
                                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li> 
                                        <li> <a href="#"><i class="fab fa-twitter"></i></a></li>   
                                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>   
                                    </ul>       
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="single-team mb-40" >
                            <div class="team-thumb">
                                <div class="brd">
                                    <img src="{{ url("front/img/team/team05.jpg")}}" alt="img">
                                </div>
                                
                            </div>
                            <div class="team-info">
                                <h4><a href="team-single.html">Vincent Cooper</a></h4>
                                <p>Designer</p>
                                <div class="team-social">
                                    <ul>
                                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li> 
                                        <li> <a href="#"><i class="fab fa-twitter"></i></a></li>   
                                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>   
                                    </ul>       
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </section>
        <!-- team-area-end --> 
        <!-- faq-area -->
        <section class="faq-area fix" style="background-color: #0c2957;">
            <div class="container">

                <div class="row align-items-center">                        
                    
                    <div class="col-lg-6">
                        <div class="section-title mb-50">
                            <h5>FAQ</h5>
                            <h2>Frequently Asked Question</h2>
                        </div>
                        <div class="faq-wrap">
                            <div class="accordion" id="accordionExample">
                                <div class="card">
                                    <div class="card-header" id="headingThree">
                                        <h2 class="mb-0">
                                            <button class="faq-btn" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapseThree" aria-bs-expanded="true" aria-bs-controls="collapseThree">
                                                Vivamus rhoncus ante a ipsum imperdiet ?
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapseThree" class="collapse show" aria-bs-labelledby="headingThree"
                                        data-bs-parent="#accordionExample">
                                        <div class="card-body">
                                            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna
                                            aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header" id="headingOne">
                                        <h2 class="mb-0">
                                            <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapseOne" aria-bs-expanded="false" aria-bs-controls="collapseOne">
                                                Integer id dolor at nisi laoreet iaculis vitae ?
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                        <div class="card-body">
                                            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna
                                            aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header" id="headingTwo">
                                        <h2 class="mb-0">
                                            <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapseTwo" aria-bs-expanded="false" aria-bs-controls="collapseTwo">
                                                Donec venenatis elit dignissim, posuere ?
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample">
                                        <div class="card-body">
                                            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna
                                            aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                                        </div>
                                    </div>
                                </div>
                                    <div class="card">
                                    <div class="card-header" id="headingOne">
                                        <h2 class="mb-0">
                                            <button class="faq-btn collapsed" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#headingFour" aria-bs-expanded="false" aria-bs-controls="headingFour">
                                                Curabitur varius, massa sit amet egestas ?
                                            </button>
                                        </h2>
                                    </div>
                                    <div id="headingFour" class="collapse" aria-labelledby="headingFour" data-bs-parent="#accordionExample">
                                        <div class="card-body">
                                            Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna
                                            aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip
                                        </div>
                                    </div>
                                </div>
                                                                    
                            </div>
                        </div>
                    </div>
                        <div class="col-lg-6">
                        <div class="faq-img text-right">
                            <img src="{{ url("front/img/bg/faq-img.jpg")}}" alt="img" class="img">
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- faq-aread-end -->
        
            <!-- gallery-area -->
        <section id="work" class="pt-120 pb-105">
                <div class="container mb-50">
                <div class="row align-items-end">
                    <div class="col-xl-5 col-lg-5">
                        <div class="section-title center-align ">
                                <h5>Our Work</h5>
                            <h2>
                                Latest Portfolio
                            </h2>
                            
                        </div>
                    </div>
                    <div class="col-xl-7 col-lg-7">
                            <div class="my-masonry text-right">
                            <div class="button-group filter-button-group ">
                                <button class="active" data-filter="*">All</button>
                                    <button data-filter=".financial">Car Towing </button>
                                <button data-filter=".banking">Motorcycle Towing </button>	
                                <button data-filter=".insurance">Hail Damage </button>
                                <button data-filter=".family">Fire Insurance </button>
                                <button data-filter=".business">Flood Insurance </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container-fluid"> 
                <div class="row">
                    <div class="col-lg-12">
                        <div class="masonry-gallery-huge">
                    <div class="grid col2">

                        <div class="grid-item financial">   
                            <a class="popup-image" href="{{ url("front/img/gallery/protfolio-img01.png")}}">
                                <figure class="gallery-image">
                                    <img src="{{ url("front/img/gallery/protfolio-img01.png")}}" alt="img" class="img">   
                                </figure>
                            </a>

                        </div>
                        
                            <div class="grid-item insurance">
                            <a class="popup-image" href="{{ url("front/img/gallery/protfolio-img03.png")}}">
                                    <figure class="gallery-image">
                                        <img src="{{ url("front/img/gallery/protfolio-img03.png")}}" alt="img" class="img">     
                                    </figure>
                                </a>

                        </div>
                            <div class="grid-item family">    
                                <a class="popup-image" href="{{ url("front/img/gallery/protfolio-img04.png")}}">
                                    <figure class="gallery-image">
                                        <img src="{{ url("front/img/gallery/protfolio-img04.png")}}" alt="img" class="img">    
                                    </figure>
                                </a>
                        </div>
                        <div class="grid-item business">
                            <a class="popup-image" href="{{ url("front/img/gallery/protfolio-img05.png")}}">
                                    <figure class="gallery-image">
                                        <img src="{{ url("front/img/gallery/protfolio-img05.png")}}" alt="img" class="img">
                                    </figure>
                                </a>

                        </div>
                            <div class="grid-item financial">
                                <a class="popup-image" href="{{ url("front/img/gallery/protfolio-img06.png")}}">
                                    <figure class="gallery-image">
                                        <img src="{{ url("front/img/gallery/protfolio-img06.png")}}" alt="img" class="img">    
                                    </figure>
                                </a>
                        </div>           
                        <div class="grid-item banking">
                                <a class="popup-image" href="{{ url("front/img/gallery/protfolio-img02.png")}}">
                                    <figure class="gallery-image">
                                        <img src="{{ url("front/img/gallery/protfolio-img02.png")}}" alt="img" class="img"> 
                                    </figure>
                                </a>


                        </div>
                        </div>
                </div>
                    
                    </div>
                
                </div>
                
            </div>
        </section>
            <!-- gallery-area-end -->
        
        
        
            <!-- blog-area -->
        <section id="blog" class="blog-area p-relative fix pt-120 pb-90" style="background: #f7fafd;">
            <div class="container">
                <div class="row align-items-center"> 
                    <div class="col-lg-12">
                        <div class="section-title center-align mb-50 text-center wow fadeInDown animated" data-animation="fadeInDown" data-delay=".4s">
                            <h5>Our Blog</h5>
                            <h2>
                                Latest Blog & News
                            </h2>
                            
                        </div>
                        
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="single-post2 hover-zoomin mb-30 wow fadeInUp animated" data-animation="fadeInUp" data-delay=".4s">
                            <div class="blog-thumb2">
                                <a href="blog-details.html"><img src="{{ url("front/img/blog/inner_b1.jpg")}}" alt="img"></a>
                            </div>                    
                            <div class="blog-content2">    
                                <div class="b-meta">
                                    <div class="meta-info">
                                        <ul>
                                            <li><i class="fal fa-user"></i> Admin</li>
                                            <li><i class="fal fa-calendar-alt"></i> 24th March 2021</li>

                                        </ul>
                                    </div>
                                </div>
                                <h4><a href="blog-details.html">Cras accumsan nulla nec lacus ultricies placerat.</a></h4> 
                                <p>Curabitur sagittis libero tincidunt tempor finibus. Mauris at dignissim ligula, nec tristique orci.</p>
                            </div>
                        </div>
                    </div>
                        <div class="col-lg-4 col-md-6">
                        <div class="single-post2 mb-30 hover-zoomin wow fadeInUp animated" data-animation="fadeInUp" data-delay=".4s">
                            <div class="blog-thumb2">
                                <a href="blog-details.html"><img src="{{ url("front/img/blog/inner_b2.jpg")}}" alt="img"></a>
                            </div>
                            <div class="blog-content2">                                    
                                <div class="b-meta">
                                    <div class="meta-info">
                                        <ul>
                                            <li><i class="fal fa-user"></i> Admin</li>
                                            <li><i class="fal fa-calendar-alt"></i> 24th March 2021</li>

                                        </ul>
                                    </div>
                                </div>
                                <h4><a href="blog-details.html">Dras accumsan nulla nec lacus ultricies placerat.</a></h4> 
                                <p>Curabitur sagittis libero tincidunt tempor finibus. Mauris at dignissim ligula, nec tristique orci.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="single-post2 mb-30 hover-zoomin wow fadeInUp animated" data-animation="fadeInUp" data-delay=".4s">
                            <div class="blog-thumb2">
                                <a href="blog-details.html"><img src="{{ url("front/img/blog/inner_b3.jpg")}}" alt="img"></a>
                            </div>
                            <div class="blog-content2">                                    
                                <div class="b-meta">
                                    <div class="meta-info">
                                        <ul>
                                            <li><i class="fal fa-user"></i> Admin</li>
                                            <li><i class="fal fa-calendar-alt"></i> 24th March 2021</li>

                                        </ul>
                                    </div>
                                </div>
                                <h4><a href="blog-details.html">Seas accumsan nulla nec lacus ultricies placerat.</a></h4> 
                                <p>Curabitur sagittis libero tincidunt tempor finibus. Mauris at dignissim ligula, nec tristique orci.</p>
                            </div>
                        </div>
                    </div>
            
                    
                </div>
            </div>
        </section>
        <!-- blog-area-end -->
        <!-- brand-area -->
        <div class="brand-area pt-60 pb-60" style="background-color:#e81c2e">
            <div class="container">
                <div class="row brand-active">
                    <div class="col-xl-2">
                        <div class="single-brand">
                            <img src="{{ url("front/img/brand/b-logo1.png")}}" alt="img">
                        </div>
                    </div>
                    <div class="col-xl-2">
                        <div class="single-brand">
                                <img src="{{ url("front/img/brand/b-logo2.png")}}" alt="img">
                        </div>
                    </div>
                    <div class="col-xl-2">
                        <div class="single-brand">
                                <img src="{{ url("front/img/brand/b-logo3.png")}}" alt="img">
                        </div>
                    </div>
                    <div class="col-xl-2">
                        <div class="single-brand">
                                <img src="{{ url("front/img/brand/b-logo4.png")}}" alt="img">
                        </div>
                    </div>
                    <div class="col-xl-2">
                        <div class="single-brand">
                                <img src="{{ url("front/img/brand/b-logo5.png")}}" alt="img">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- brand-area-end -->
</main>
    
@endsection

@section('script')
@endsection