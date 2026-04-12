<!doctype html>
<html class="no-js" lang="zxx">
    
<!-- Mirrored from htmldemo.zcubethemes.com/autozox/index-3.html by HTTrack Website Copier/3.x [XR&CO'2014], Sun, 12 Apr 2026 08:58:49 GMT -->
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
    @include('components.header')
    @yield('content')
    @include('components.footer')
    @yield('script')
    @stack('scripts')
</body>
</html>