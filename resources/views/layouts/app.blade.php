<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta name="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
          content="Ayuda a tu escuela de futbol a mejorar deportivamente con nuestra herramienta, la cual te facilitará muchos procesos, asistencias, entrenamientos, pagos entre otros">
    <link rel="icon" type="image/png" href="/img/ballon.png"/>
    <link href="{{asset('css/all.css')}}" rel="stylesheet">
    @yield('css')
    <link href="{{asset('css/default.css')}}" rel="stylesheet">
 </head>

<body class="fix-header fix-sidebar card-no-border">

<div class="preloader">
    <svg class="circular" viewBox="25 25 50 50">
        <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
    </svg>
</div>

@if(stripos(Request::url(),'/login') || stripos(Request::url(),'/contrasenia/vencida')
|| stripos(Request::url(),'password/reset') || stripos(Request::url(),'/register') )
    <section id="wrapper">
        @yield('content')
    </section>
@else

    <div id="app">

        @include('layouts.topbar')
        @include('layouts.sidebar')
        <div class="page-wrapper" style="min-height: 878px;">
            {{-- Container fluid  --}}
            <div class="container-fluid">
                {{-- Start Page Content --}}
                {{-- @include('errors.error-alert') --}}
                @yield('content')
                {{-- End PAge Content --}}
            </div>
            {{-- End Container fluid  --}}
        </div>
        @yield('modals')
    </div>
@endif
<script>
    window.token = @json(['csrfToken' => csrf_token()]);
    window.app_name = "{{config('app.name', 'Laravel') }}";
    window.img_login = "{{asset('img/login.png')}}";
    window.img_logout = "{{asset('img/logout.png')}}";
</script>
@include('sweetalert::alert')
<script src="{{asset('js/all.js')}}"></script>
<script src="{{asset('js/config.js')}}"></script>
@yield('scripts')
@stack('scripts')
<script>
    $(document).ready(function () {
        $(".scroll-sidebar").closest('div.slimScrollDiv').css('position', 'fixed');
    });
</script>
</body>
</html>
