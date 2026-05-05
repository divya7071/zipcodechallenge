@extends('layouts.app')

@section('content')
<style>
    .form-validation {
        color: red;
    }

    .contact-bg02 {
        padding: 30px;
    }

    .contact-left-panel {
        position: relative;
        height: 100%;
        min-height: 500px;
        background: url('/images/fitness-map.jpg') center/cover no-repeat;
        border-radius: 12px;
        overflow: hidden;
    }

    /* Dark overlay */
    .contact-left-panel .overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(12, 41, 87, 0.9), rgba(8, 28, 58, 0.9));
    }

    /* Content */
    .contact-left-panel .content {
        position: relative;
        color: #fff;
        padding: 40px;
    }

    .contact-left-panel h3 {
        font-weight: 600;
        margin-bottom: 10px;
    }

    .contact-left-panel p {
        font-size: 14px;
        opacity: 0.8;
    }

    /* Highlights */
    .contact-highlights {
        margin-top: 30px;
    }

    .highlight {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
    }

    .highlight span {
        font-size: 20px;
    }

    .highlight strong {
        display: block;
    }
</style>
<main>
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
    <section id="contact" class="contact-area after-none contact-bg pt-70 pb-70 p-relative fix">
        <div class="container">

            <div class="row justify-content-center align-items-center">

                <div class="col-lg-5 col-md-6 col-sm-12 order-1">

                    <h1>Let’s keep you moving 🚴</h1>

                    <p>
                        Whether you have a question, found a bug, or need help understanding your progress,
                        we’re here to support your journey.
                    </p>

                    <div class="contact-highlights mt-4">

                        <div class="highlight">
                            <span>📍</span>
                            <div>
                                <h5 class="mb-1">Explore ZIP codes</h5>
                                <small>Track your movement across locations</small>
                            </div>
                        </div>

                        <div class="highlight">
                            <span>📊</span>
                            <div>
                                <h5 class="mb-1">Analyze your activities</h5>
                                <small>Distance, speed, elevation & insights</small>
                            </div>
                        </div>

                        <div class="highlight">
                            <span>⚡</span>
                            <div>
                                <h5 class="mb-1">Automatic sync</h5>
                                <small>Seamlessly connected with Strava</small>
                            </div>
                        </div>

                    </div>

                    <div class="mt-4">
                        <h6>Need help with:</h6>
                        <ul class="support-list">
                            <li>🔧 Sync issues</li>
                            <li>📍 Missing ZIP codes</li>
                            <li>📊 Activity insights</li>
                        </ul>
                    </div>

                    <div class="trust-box mt-4">
                        <p>💬 We usually respond within 24 hours.</p>
                    </div>
                </div>
                <div class="col-lg-7 col-md-6 col-sm-12 order-2">
                    <div class="contact-bg02">
                        <div class="section-title center-align">
                            <h1 class="text-start">Contact Us</h1>
                        </div>
                        <form class="contact-form mt-30" method="post" action="{{route('contact-submit')}}" id="contact-from">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="contact-field p-relative c-name mb-20">
                                        <input type="text" id="name" name="name" placeholder="First Name" required>
                                        <span class="form-validation " id="name1"></span>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="contact-field p-relative c-subject mb-20">
                                        <input class="contact-form-control" type="text" id="email" name="email" placeholder="Email" required>
                                        <span class="form-validation " id="email1"></span>
                                    </div>

                                </div>
                                <div class="col-lg-6">
                                    <div class="contact-field p-relative c-subject mb-20">
                                        <input class="contact-form-control" type="number" id="phone" name="phone" placeholder="Phone No." required>
                                        <span class="form-validation " id="phone1"></span>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="contact-field p-relative c-subject mb-20">
                                        <input class="contact-form-control" type="text" id="subject" name="subject" placeholder="Subject">
                                        <span class="form-validation " id="subject1"></span>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="contact-field p-relative c-message mb-30">
                                        <textarea class="contact-form-control" name="message" id="message" cols="30" rows="10" placeholder="Write comments"></textarea>
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
    <!-- brand-area-end -->
</main>

@endsection

@section('script')
<script src="{{ url("front/js/sweet-alert.js")}}"></script>
<script>
    $(document).on('input', '.contact-form-control', function(obj) {

        name = $(this).attr('name') + 1;
        $('#' + name).text('');

    });
    $('#contact-from').submit(function(event) {
        event.preventDefault();
        var valid = 1
        $('.contact-form-control').each(function(i, obj) {
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
                success: function(response) {

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