@extends('layouts.app')
@section('head')

@endsection
@section('content')
<style>
    ul.list-disc li {
        display: list-item !important;
        list-style-type: disc !important;
    }
</style>

<main id="content" class="site-main">
    <section class="breadcrumb-area d-flex align-items-center" style="background-image:url({{ url('front/img/bg/inner-page.png')}});">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-12 col-lg-12">
                    <div class="breadcrumb-wrap text-left">
                        <div class="breadcrumb-title">
                            <h3 class="text-white">Privacy Policy</h3>
                            <div class="breadcrumb-wrap">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Privacy Policy</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="privacy-policy" class="pt-70 pb-70">
        <div class="container">
            <h2 class="fw-bold mb-3"> Privacy Policy</h2>
            <p>We respect your privacy. Zipcode Challenge is built to help you explore your movement—not to exploit your data.</p>
            <p>This Privacy Policy explains what we collect, how we use it, and how we protect it.</p>
            <div class="">
                <h4 class="mb-2">1. Information We Collect</h4>
                <h5 class="ms-3">a. Information you provide</h5>
                <ul class="ms-5 list-disc">
                    <li>Name</li>
                    <li>Email address</li>
                    <li>Profile details</li>
                </ul>

                <h5 class="ms-3 mt-4">b. Strava Data (with your permission)</h5>
                <p class="ms-3 mb-2">When you connect your Strava account, we may access:</p>
                <ul class="ms-5 mb-2 list-disc">
                    <li>Activities (ride, run, walk)</li>
                    <li>GPS/route data</li>
                    <li>Distance, time, elevation</li>
                    <li>Activity metadata</li>
                </ul>
                <p class="ms-3 mb-2 text-dark"><strong>We only access data you explicitly authorize.</strong></p>
            </div>

            <div class="mt-4">
                <h4 class="mb-2">2. How We Use Your Data</h4>
                <p class="mb-2">We use your data to:</p>
                <ul class="ms-5 mb-2 list-disc">
                    <li>Calculate ZIP codes you’ve traveled through</li>
                    <li>Display your dashboard</li>
                    <li>Provide insights</li>
                    <li>Improve experience</li>
                </ul>
                <p class="mb-2 text-dark"><strong>We do not sell your data.</strong></p>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">3. Data Sharing</h4>
                <ul class="ms-5 mb-2 list-disc">
                    <li>Data is private unless you share it</li>
                    <li>No sharing of other users’ data</li>
                    <li>No third-party marketing sharing</li>
                </ul>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">4. Data Storage & Security</h4>
                <p class="mb-2">We take reasonable measures to:</p>
                <ul class="ms-5 mb-2 list-disc">
                    <li>Protect your data</li>
                    <li>Prevent unauthorized access</li>
                    <li>Secure integrations</li>
                </ul>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">5. Your Control</h4>
                <p class="mb-2">You can:</p>
                <ul class="ms-5 mb-2 list-disc">
                    <li>Disconnect Strava anytime</li>
                    <li>Request data deletion</li>
                    <li>Stop using anytime</li>
                </ul>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">6. Third-Party Services</h4>
                <p>We rely on Strava API. Your use is also subject to Strava’s Privacy Policy.</p>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">7. Updates</h4>
                <p>We may update this policy as the product evolves.</p>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">8. Contact</h4>
                <a href="mail-to:info@zipcodechallenge.com">info@zipcodechallenge.com</a>
            </div>
        </div>

    </section>
</main>
@endsection
@section('script')

@endsection