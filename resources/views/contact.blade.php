@extends('layouts.app') 
   
@section('content')
<style>
.form-validation{
   color:red;
}
</style>
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
    <section class="breadcrumb-area d-flex align-items-center" style="background-image:url({{ url('front/img/bg/inner-page.png')}});">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-12 col-lg-12">
                    <div class="breadcrumb-wrap text-left">
                        <div class="breadcrumb-title">
                            <h3 class="text-white">Contact Us</h3>    
                            <div class="breadcrumb-wrap">
                        
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
                            </ol>
                        </nav>
                    </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>
<section id="contact" class="contact-area after-none contact-bg pt-120 pb-120 p-relative fix">
        <div class="container">
        
            <div class="row justify-content-center align-items-center">
                
                    <div class="col-lg-4 order-1">
                        
                    <div class="contact-info">
                            <div class="single-cta pb-30 mb-30 wow fadeInUp animated" data-animation="fadeInDown animated" data-delay=".2s">
                                <div class="f-cta-icon">
                                    <i class="far fa-map"></i>
                                </div>
                                <h5>Office Address</h5>
                                <p>380 St Kilda Road, Melbourne <br>
                                    VIC 3004, Australia</p>
                            </div>
                                <div class="single-cta pb-30 mb-30 wow fadeInUp animated" data-animation="fadeInDown animated" data-delay=".2s">
                                <div class="f-cta-icon">
                                    <i class="far fa-clock"></i>
                                </div>
                                <h5>Working Hours</h5>
                                <p>Monday to Friday 09:00 to 18:30 <br> 
                                    Saturday 15:30</p>
                            </div>
                                <div class="single-cta wow fadeInUp animated" data-animation="fadeInDown animated" data-delay=".2s">
                                <div class="f-cta-icon">
                                    <i class="far fa-envelope-open"></i>
                                </div>
                                <h5>Message Us</h5>
                                <p> <a href="#">support@example.com</a><br><a href="#">info@example.com</a></p>
                            </div>
                        </div>							
                </div>
                <div class="col-lg-8 order-2">
                    <div class="contact-bg02">
                        <div class="section-title center-align">
                            <h2>
                                Contact us
                            </h2>
                        </div>       
                        <form class="contact-form mt-30"  method="post" action="{{route('contact-submit')}}" id="contact-from">                         
                            <div class="row">
                            <div class="col-lg-6">
                                <div class="contact-field p-relative c-name mb-20">                                    
                                    <input type="text" id="name" name="name" placeholder="First Name" required>
                                       <span class="form-validation " id="name1"></span>
                                </div>                               
                            </div>

                            <div class="col-lg-6">                               
                                <div class="contact-field p-relative c-subject mb-20">                                   
                                    <input  class="contact-form-control" type="text" id="email" name="email" placeholder="Email" required>
                                    <span class="form-validation " id="email1"></span>
                                </div>
                                
                            </div>		
                            <div class="col-lg-6">                               
                                <div class="contact-field p-relative c-subject mb-20">                                   
                                    <input  class="contact-form-control" type="number" id="phone" name="phone" placeholder="Phone No." required>
                                     <span class="form-validation " id="phone1"></span>
                                </div>
                            </div>	
                            <div class="col-lg-6">                               
                                <div class="contact-field p-relative c-subject mb-20">                                   
                                    <input  class="contact-form-control" type="text" id="subject" name="subject" placeholder="Subject">
                                      <span class="form-validation " id="subject1"></span>
                                </div>
                            </div>	
                            <div class="col-lg-12">
                                <div class="contact-field p-relative c-message mb-30">                                  
                                    <textarea  class="contact-form-control" name="message" id="message" cols="30" rows="10" placeholder="Write comments"></textarea>
                                     <span class="form-validation " id="message1"></span>
                                </div>
                                <div class="slider-btn">                                          
                                        <button type="submit" class="btn ss-btn" data-animation="fadeInRight" data-delay=".8s"><span>Submit Now</span></button>				
                                </div>
                                <span id="successContact" style="display: none;color: #fc5200;">Submitted Successfully</span>                             
                            </div>
                            </div>
                    </form>                            
                    </div>    
                
                </div>
            </div>
            
        </div>
        
    </section>
    <div class="map fix" style="background: #f5f5f5;">
                <div class="container-flud">
                    
                    <div class="row">
                        <div class="col-lg-12">
                       <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d212867.83634504632!2d-112.24455686962897!3d33.52582710700138!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x872b743829374b03%3A0xabaac255b1e43fbe!2sPlexus%20Worldwide!5e0!3m2!1sen!2sin!4v1618567685329!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>
     
   
    <!-- brand-area-end -->
</main>
     
@endsection

@section('script')
<script src="{{ url("front/js/sweet-alert.js")}}"></script>
<script>
    $(document).on('input', '.contact-form-control', function (obj) {
   
      name = $(this).attr('name') + 1;
      $('#' + name).text('');

   });
    $('#contact-from').submit(function (event) {
      event.preventDefault();
      var valid = 1
      $('.contact-form-control').each(function (i, obj) {
         console.log($(this).val());
        if ($(this).val() == '') {
            name = obj.name + 1;
            $('#' + name).text('field is required');
            valid = 0
         }
   
      });
      var email = $('#email').val();
      console.log(email);
      if (validateEmail(email)) {
   
      } else {
         valid = 0;
         $('#email1').text('email not valid');
      }
      if (valid == 1) {
         var url = $(this).attr('action');
         $.ajax({
            headers: {
               'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            type: 'POST',
            url: url,
            data: new FormData(this),
            dataType: 'json',
            contentType: false,
            cache: false,
            processData: false,
            success: function (response) {
   
               if (response.error == 0) {
                  $('.form-validation').text('');
                  $('#contact-from')[0].reset();
                  $('#successContact').show();
                 swal("Success", response.message, "success");
               } else {
   
                  swal("Error", "Something went wrong", "error");
               }
            }
   
         });
      }
   });
   function validateEmail(email) {
   var pattern = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
   
   return $.trim(email).match(pattern) ? true : false;
   }  
</script>
@endsection