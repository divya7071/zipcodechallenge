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
                            <h3 class="text-white">Legal</h3>
                            <div class="breadcrumb-wrap">
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Legal</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="legal" class="pt-70 pb-70">
        <div class="container">
            <h2 class="fw-bold mb-3">Legal & Compliance</h2>
            <div class="mt-4">
                <h4 class="mb-2">1. Independent Platform</h4>
                <p>Zipcode Challenge is not affiliated with or endorsed by Strava.</p>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">2. Data Compliance</h4>
                <p>We follow Strava API Agreement and applicable data standards.</p>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">3. Intellectual Property</h4>
                <ul class="ms-5 mb-2 list-disc">
                    <li>Platform content belongs to Zipcode Challenge</li>
                    <li>Users own their activity data</li>
                </ul>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">4. Disclaimer</h4>
                <p>Data insights are informational only and not professional advice.</p>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">5. Community Nature</h4>
                <p>Focused on fitness, exploration, and positive engagement.</p>
            </div>
            <div class="mt-4">
                <h4 class="mb-2">6. Contact</h4>
                <p>info@zipcodechallenge.com</p>
            </div>
        </div>
    </section>
</main>
@endsection
@section('script')

@endsection