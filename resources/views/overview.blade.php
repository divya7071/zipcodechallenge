@extends('layouts.app') 
   
@section('content')
<main>
    

    <!-- breadcrumb-area -->
    <section class="breadcrumb-area d-flex align-items-center" style="background-image:url({{ url('front/img/bg/bdrc-bg.jpg')}});">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-12 col-lg-12">
                    <div class="breadcrumb-wrap text-left">
                        <div class="breadcrumb-title">
                            <h2>Overview</h2>    
                            <div class="breadcrumb-wrap">
                        
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Overview</li>
                            </ol>
                        </nav>
                    </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
    <!-- breadcrumb-area-end -->

        <!-- service-details2-area -->
    <section id="service-details2" class="pt-120 pb-105 p-relative" style="background: url({{ url('front/img/bg/services-bg.html')}}); background-size: contain; background-position: center center; background-repeat: no-repeat;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="section-title center-align mb-50 text-center">
                        <h5>Overview</h5>
                        <h2>
                            Whelcome to ZIP Code challenge
                        </h2>
                        
                    </div>
                    
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="about-content s-about-content">
                    
                        <ul class="sr-tw-ul ">
                        <li>
                            <div class="icon-right"><img src="{{ url('front/img/icon/fe-icon01.png')}}" alt="icon01"></div>
                            <div class="text">
                                <h4><a href="{{route('todo-zips')}}">Passed Zips </a></h4> 
                                <p>{{$totalZips}}</p>
                            </div>
                                
                        </li>
                        
                    </ul>
                        
                
                    </div>
                </div>
              
                
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="about-content s-about-content">
                    
                            <ul class="sr-tw-ul sr-thr-ul">
                        <li>
                            <div class="icon"><img src="{{ url('front/img/icon/fe-icon04.png')}}"alt="icon01"></div>
                                <div class="text pt-10">
                                <h4><a href="{{route('passed-zips')}}">Activities </a></h4> 
                                <p>{{auth('athlete')->user()->activities->count()}}</p>
                            </div>
                        </li>
                                                
                    </ul>
                        
                
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="about-content s-about-content">
                    
                            <ul class="sr-tw-ul sr-thr-ul">
                        <li>
                            <div class="icon"><img src="{{ url('front/img/icon/fe-icon04.png')}}"alt="icon01"></div>
                                <div class="text pt-10">
                                <h4><a href="{{route('todo-zips')}}">Passed Zips / Week </a></h4> 
                                <p>{{ $summary['totalZips'] }}</p>
                            </div>
                        </li>
                                           
                    </ul>
                        
                
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- service-details2-area-end -->
        <!-- services-five-area -->
    <section id="services-05" class="services-05 services-09 pt-100 pb-100 p-relative" style="background-image: url({{ url('front/img/bg/approch-bg.png')}}); background-repeat: no-repeat; background-color: #f7fafd; background-size: contain; border-bottom: 1px solid #efefef; ">
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
                                    <img src="{{ url('front/img/bg/services-01.png')}}" alt="icon01">
                                    
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
                                    <img src="{{ url('front/img/bg/services-02.png')}}" alt="icon01">
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
                                    <img src="{{ url('front/img/bg/services-03.png')}}" alt="icon01">
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
    <!-- testimonial-area -->
    <section class="testimonial-area pt-120 pb-115 p-relative fix">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-title text-center mb-50 wow fadeInDown animated" data-animation="fadeInDown" data-delay=".4s">
                        <h5>Testimonial</h5>
                        <h2>
                            What Our Clients Says
                        </h2>
                        
                    </div>
                    
                </div>
                
                <div class="col-lg-12">
                    <div class="testimonial-active">
                        <div class="single-testimonial">
                                <div class="testi-author">
                                <img src="img/testimonial/testi_avatar.png')}}" alt="img">
                            </div>
                            <p>“Morbi neque nisi, tincidunt nec erat vitae, viverra porttitor lorem. Fusce tempor nunc at luctus blandit. Donec eget fermentum magna.we dedicate financial on services the teams serve all Curabitur ac tortor ante. Sed quis dignissim”</p>
                            <div class="ta-info">
                                    <h6>Margie Dose</h6>
                                    <span>Web Developer</span>
                                </div>
                            <div class="qt-img">
                            <img src="{{ url('front/img/testimonial/qt-icon.png')}}" alt="img">
                            </div>
                        </div>
                        <div class="single-testimonial">
                            <div class="testi-author">
                                <img src="{{ url('front/img/testimonial/testi_avatar_02.png')}}" alt="img">
                            </div>
                            <p>“Morbi neque nisi, tincidunt nec erat vitae, viverra porttitor lorem. Fusce tempor nunc at luctus blandit. Donec eget fermentum magna.we dedicate financial on services the teams serve all Curabitur ac tortor ante. Sed quis dignissim”</p>
                            <div class="ta-info">
                                    <h6>Jone Walker</h6>
                                    <span>Web Designer</span>
                                </div>
                                <div class="qt-img">
                            <img src="{{ url('front/img/testimonial/qt-icon.png')}}" alt="img">
                            </div>
                        </div>
                        <div class="single-testimonial">
                            <div class="testi-author">
                                <img src="{{ url('front/img/testimonial/testi_avatar_02.png')}}" alt="img">
                            </div>
                            <p>“Morbi neque nisi, tincidunt nec erat vitae, viverra porttitor lorem. Fusce tempor nunc at luctus blandit. Donec eget fermentum magna.we dedicate financial on services the teams serve all Curabitur ac tortor ante. Sed quis dignissim”</p>
                            <div class="ta-info">
                                    <h6>Dose Robot</h6>
                                    <span>Web Developer</span>
                                </div>
                            <div class="qt-img">
                            <img src="{{ url('front/img/testimonial/qt-icon.png')}}" alt="img">
                            </div>
                        </div>
                        <div class="single-testimonial">
                                <div class="testi-author">
                                <img src="{{ url('front/img/testimonial/testi_avatar.png')}}" alt="img">
                            </div>
                            <p>“Morbi neque nisi, tincidunt nec erat vitae, viverra porttitor lorem. Fusce tempor nunc at luctus blandit. Donec eget fermentum magna.we dedicate financial on services the teams serve all Curabitur ac tortor ante. Sed quis dignissim”</p>
                            <div class="ta-info">
                                    <h6>Jone Walker</h6>
                                    <span>Web Designer</span>
                                </div>
                                <div class="qt-img">
                            <img src="{{ url('front/img/testimonial/qt-icon.png')}}" alt="img">
                            </div>
                        </div>
                        <div class="single-testimonial">
                                <div class="testi-author">
                                <img src="{{ url('front/img/testimonial/testi_avatar_02.png')}}" alt="img">
                            </div>
                            
                            <p>“Morbi neque nisi, tincidunt nec erat vitae, viverra porttitor lorem. Fusce tempor nunc at luctus blandit. Donec eget fermentum magna.we dedicate financial on services the teams serve all Curabitur ac tortor ante. Sed quis dignissim”</p>
                            <div class="ta-info">
                                    <h6>Margie Dose</h6>
                                    <span>Web Developer</span>
                                </div>
                            <div class="qt-img">
                            <img src="{{ url('front/img/testimonial/qt-icon.png')}}" alt="img">
                            </div>
                        </div>
                        <div class="single-testimonial">
                            <div class="testi-author">
                                <img src="{{ url('front/img/testimonial/testi_avatar.png')}}" alt="img">
                            </div>
                    
                            <p>“Morbi neque nisi, tincidunt nec erat vitae, viverra porttitor lorem. Fusce tempor nunc at luctus blandit. Donec eget fermentum magna.we dedicate financial on services the teams serve all Curabitur ac tortor ante. Sed quis dignissim”</p>
                            <div class="ta-info">
                                    <h6>Jone Walker</h6>
                                    <span>Web Designer</span>
                                </div>
                                <div class="qt-img">
                            <img src="{{ url('front/img/testimonial/qt-icon.png')}}" alt="img">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- testimonial-area-end -->
    <!-- brand-area -->
    <div class="brand-area pt-60 pb-60" style="background-color:#e81c2e">
        <div class="container">
            <div class="row brand-active">
                <div class="col-xl-2">
                    <div class="single-brand">
                        <img src="{{ url('front/img/brand/b-logo1.png')}}" alt="img">
                    </div>
                </div>
                <div class="col-xl-2">
                    <div class="single-brand">
                            <img src="{{ url('front/img/brand/b-logo2.png')}}" alt="img">
                    </div>
                </div>
                <div class="col-xl-2">
                    <div class="single-brand">
                            <img src="{{ url('front/img/brand/b-logo3.png')}}" alt="img">
                    </div>
                </div>
                <div class="col-xl-2">
                    <div class="single-brand">
                            <img src="{{ url('front/img/brand/b-logo4.png')}}" alt="img">
                    </div>
                </div>
                <div class="col-xl-2">
                    <div class="single-brand">
                            <img src="{{ url('front/img/brand/b-logo5.png')}}" alt="img">
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