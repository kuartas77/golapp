<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="sidebar-noneoverflow">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="X-UA-Compatible" content="IE=edge, chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="description" content="Ayuda a tu escuela de futbol a mejorar deportivamente con nuestra herramienta, la cual te facilitará muchos procesos, asistencias, entrenamientos, pagos entre otros">
    <link rel="icon" type="image/png" href="/img/ballon.png" />

    <!-- ENABLE LOADERS -->
    <link href="{{asset('layouts/css/light/loader.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('layouts/css/dark/loader.css')}}" rel="stylesheet" type="text/css" />
    <script src="{{asset('layouts/js/loader.js')}}"></script>
    <!-- /ENABLE LOADERS -->
    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
    <link href="{{asset('layouts/css/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('layouts/css/light/plugins.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('layouts/css/dark/plugins.css')}}" rel="stylesheet" type="text/css" />
    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BEGIN PAGE LEVEL PLUGINS/CUSTOM STYLES -->
    <link rel="stylesheet" type="text/css" href="{{asset('layouts/css/light/elements/alert.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('layouts/css/dark/elements/alert.css')}}">
    <!-- END PAGE LEVEL PLUGINS/CUSTOM STYLES -->

    <link href="https://cdn.datatables.net/v/dt/moment-2.29.4/dt-2.2.1/fc-5.0.4/fh-4.0.1/datatables.min.css" rel="stylesheet">
    <!-- <link href="{{asset('layouts/plugins/src/table/datatable/datatables.css')}}" rel="stylesheet"> -->
    <!-- <link href="{{asset('layouts/plugins/css/dark/table/datatable/dt-global_style.css')}}" rel="stylesheet"> -->
    @yield('css')
    <!-- <link href="{{asset('css/all.css')}}" rel="stylesheet"> -->
    <!-- <link href="{{asset('css/default.css')}}" rel="stylesheet"> -->

</head>

<body class="layout-boxed">

    <!-- BEGIN LOADER -->
    <div id="load_screen">
        <div class="loader">
            <div class="loader-content">
                <div class="spinner-grow align-self-center"></div>
            </div>
        </div>
    </div>
    <!--  END LOADER -->

    @guest
    <section id="wrapper">
        @yield('content')
    </section>
    @endguest

    @auth

    <!--  BEGIN NAVBAR  -->
    @include('layouts.topbar')
    <!--  END NAVBAR  -->

    <!--  BEGIN MAIN CONTAINER  -->
    <div class="main-container " id="container">

        <div class="overlay"></div>
        <div class="search-overlay"></div>

        <!--  BEGIN SIDEBAR  -->
        @include('layouts.sidebar')
        <!--  END SIDEBAR  -->

        <div id="content" class="main-content">
            <div class="layout-px-spacing">
                @yield('content')
            </div>
        </div>
        <!--  END CONTENT AREA  -->
    </div>
    <!-- END MAIN CONTAINER -->
    @endauth

    @yield('modals')
    <script>
        window.token = @json(['csrfToken' => csrf_token()]);
        window.app_name = "{{config('app.name', 'Laravel') }}";
        window.img_login = "{{asset('img/login.png')}}";
        window.img_logout = "{{asset('img/logout.png')}}";
    </script>
    <script src="{{asset('layouts/plugins/src/global/vendors.min.js')}}"></script>
    <script src="{{asset('layouts/css/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('layouts/plugins/src/perfect-scrollbar/perfect-scrollbar.min.js')}}"></script>
    <script src="{{asset('layouts/plugins/src/waves/waves.min.js')}}"></script>
    <script src="{{asset('layouts/js/app.js')}}"></script>


    <!-- <script src="{{asset('layouts/js/custom.js')}}"></script> -->
    @include('sweetalert::alert')
    <!-- <script src="{{asset('js/all.js')}}"></script> -->
    <script src="https://cdn.datatables.net/v/dt/moment-2.29.4/dt-2.2.1/fc-5.0.4/fh-4.0.1/datatables.min.js"></script>
    <!-- <script src="{{asset('layouts/plugins/src/table/datatable/datatables.js')}}"></script> -->

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script> -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-daterangepicker/3.0.5/daterangepicker.min.js" integrity="sha512-mh+AjlD3nxImTUGisMpHXW03gE6F4WdQyvuFRkjecwuWLwD2yCijw4tKA3NsEFpA1C3neiKhGXPSIGSfCYPMlQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.9/jquery.inputmask.min.js" integrity="sha512-F5Ul1uuyFlGnIT1dk2c4kB4DBdi5wnBJjVhL7gQlGh46Xn0VhvD8kgxLtjdZ5YN83gybk/aASUAlpdoWUjRR3g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.10.3/typeahead.bundle.min.js" integrity="sha512-E4rXB8fOORHVM/jZYNCX2rIY+FOvmTsWJ7OKZOG9x/0RmMAGyyzBqZG0OGKMpTyyuXVVoJsKKWYwbm7OU2klxA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.21.0/jquery.validate.min.js" integrity="sha512-KFHXdr2oObHKI9w4Hv1XPKc898mE4kgYx58oqsc/JqqdLMDI4YjOLzom+EMlW8HFUd0QfjfAvxSL6sEq/a42fQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{asset('js/config.js')}}"></script>

    @yield('scripts')
    @stack('scripts')
    <!-- <script>
        $(document).ready(function() {
            $(".scroll-sidebar").closest('div.slimScrollDiv').css('position', 'fixed');
        });
    </script> -->
</body>

</html>