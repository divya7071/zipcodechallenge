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
                            <h3 class="text-white">Terms & Conditions</h3>
                            <div class="breadcrumb-wrap">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Terms & Conditions</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="terms-and-conditions" class="pt-70 pb-70">
        <div class="container">
            <h2 class="fw-bold mb-3">Terms of Use</h2>
            <div class="mt-4">
                <h4 class="mb-2">1. Acceptance</h4>
                <p>By using Zipcode Challenge, you agree to these terms.</p>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">2. What This Platform Does</h4>
                <ul class="ms-5 mb-2 list-disc">
                    <li>Connect your Strava account</li>
                    <li>Analyze activities</li>
                    <li>Visualize ZIP codes</li>
                </ul>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">3. User Responsibility</h4>
                <ul class="ms-5 mb-2 list-disc">
                    <li>Use responsibly</li>
                    <li>No hacking or misuse</li>
                    <li>Only connect accounts you own</li>
                </ul>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">4. Data Usage</h4>
                <ul class="ms-5 mb-2 list-disc">
                    <li>Your data belongs to you</li>
                    <li>Used only to provide service</li>
                    <li>Complies with Strava API</li>
                </ul>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">5. No Guarantees</h4>
                <p>Provided “as is” without guarantees.</p>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">6. Limitation of Liability</h4>
                <ul class="ms-5 mb-2 list-disc">
                    <li>No liability for inaccuracies</li>
                    <li>No liability for downtime</li>
                    <li>No liability for third-party issues</li>
                </ul>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">7. Termination</h4>
                <p>Access may be suspended if terms are violated.</p>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">8. Changes</h4>
                <p>Terms may evolve over time.</p>
            </div>
        </div>
    </section>

</main>
@endsection
@section('script')

@endsection