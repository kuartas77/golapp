<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} | @yield('title')</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta name="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="/img/ballon.png" />
    <link href="{{asset('css/all.css')}}" rel="stylesheet">
    @yield('css')
    <link href="{{asset('css/default.css')}}" rel="stylesheet">
    @stack('css')
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js" defer></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js" defer></script>
    <![endif]-->

</head>
<body class="card-no-border">
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10" />
        </svg>
    </div>
    @include('layouts.portal.topbar')
    <section id="wrapper" class="p-2">
        @yield('content')
    </section>

    <script>
        window.token = @json(['csrfToken' => csrf_token()]);
        window.app_name = "{{config('app.name', 'Laravel') }}";
        window.img_login = "{{asset('img/login.png')}}";
        window.img_logout = "{{asset('img/logout.png')}}";
    </script>
    @include('sweetalert::alert')
    <script src="{{mix('js/all.js')}}" ></script>
    <script src="{{asset('js/config.js')}}" ></script>
    @yield('scripts')
</body>
</html>
