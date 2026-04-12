@extends('layouts.app') 
   
@section('content')
<main>
    
    <!-- search-popup -->
<div class="modal fade bs-example-modal-lg search-bg popup1" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content search-popup">
            <div class="text-center">
                <a href="#" class="close2" data-dismiss="modal" aria-label="Close">× close</a>
            </div>
            <div class="row search-outer">
                <div class="col-md-11"><input type="text" placeholder="Search for products..." /></div>
                <div class="col-md-1 text-right"><a href="#"><i class="fa fa-search" aria-hidden="true"></i></a></div>
            </div>
        </div>
    </div>
</div>
<!-- /search-popup -->
    <!-- breadcrumb-area -->
    <section class="breadcrumb-area d-flex align-items-center" style="background-image:url({{ url('front/img/bg/bdrc-bg.jpg')}});">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-12 col-lg-12">
                    <div class="breadcrumb-wrap text-left">
                        <div class="breadcrumb-title">
                            <h2>How it Works</h2>    
                            <div class="breadcrumb-wrap">
                        
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">How it Works</li>
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
        <!-- service-area -->
    <section style="background: #f7fafd;" class="service-details pt-90 pb-60 p-relative">
        <div class="container">
            <div class="row">
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
     <section class="team-area-content" >
        <div class="container">

            <!-- Lower Content -->
            <div class="lower-content">
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-sm-12">
                            <div class="team-img-box">
                                <img src="{{ url("front/img/how_it_works/connect.png")}}" alt="img">
                            </div>
                    </div>

                    <div class="text-column col-lg-8 col-md-12 col-sm-12">
                        <div class="s-about-content pl-30 wow fadeInRight" data-animation="fadeInRight" data-delay=".2s">  
                             <h3>Connect & Move Your Way </h3>
                                 <p>Connect with Strava. Then move your way, Walk. Run. Ride. Choose the activity that fits your life. Short or long sessions both count. Travel, events, or everyday routines all work. This is not about speed or distance. It's about showing up consistently.</p>
                                 
                            </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-sm-12">
                            <div class="team-img-box">
                                <img src="{{ url("front/img/how_it_works/explore.png")}}" alt="img">
                            </div>
                    </div>

                    <div class="text-column col-lg-8 col-md-12 col-sm-12">
                        <div class="s-about-content pl-30 wow fadeInRight" data-animation="fadeInRight" data-delay=".2s">  
                             <h3>Explore ZIP Codes</h3>
                            <p>Every activity tells a story.</p>
                            <p>Each unique ZIP code you move through is counted once—whether you're close to home or traveling across cities and states. Your movement naturally builds a map of the places you've explored. No routes to follow. No targets to chase. Just exploration.</p>
                                 
                            </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-sm-12">
                            <div class="team-img-box">
                                <img src="{{ url("front/img/how_it_works/belong.png")}}" alt="img">
                            </div>
                    </div>

                    <div class="text-column col-lg-8 col-md-12 col-sm-12">
                        <div class="s-about-content pl-30 wow fadeInRight" data-animation="fadeInRight" data-delay=".2s">  
                             <h3>Track Progress & Belong</h3>
                                <p>Watch your journey grow over time.</p>
                                <p>See how many ZIP codes you've unlocked. Celebrate milestones. Share the experience with a like-minded community. No competition. No pressure. Just mindset, commitment, and community—one ZIP code at a time.</p>  
                                <!-- Two Column -->       
                        </div>
                         
                           
                    </div>
                 <div class="col-lg-4"></div> 
                    <div class="text-column col-lg-8 col-md-12 col-sm-12">
                        <div class="service-detail">
                            <div class="content-box">
                                <h2>That's it. Three steps. One habit that lasts. </h2>
                            </div>
                        </div>
                    </div>
                    

                </div>
                 
            </div>

        </div>
    </section>
     
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