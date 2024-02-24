@extends('layouts.public.public')
@section('content')
    <x-row-card col-inside="12" margin="my-0" >
        <div class="fluid_container">
            <div class="camera_wrap">
                <div data-src="{{asset('img/log3.svg')}}"></div>
                <div data-src="{{asset('img/user_login.png')}}"></div>
                <div data-src="{{asset('img/ballon.png')}}"></div>
                <div data-src="{{asset('img/1970.jpg')}}"></div>
            </div>
        </div>
    </x-row-card >
    
    <!-- <x-row-card col-inside="12" >

    </x-row-card > -->

@endsection
@section('modals')
@endsection
@section('scripts')
    <script>
        jQuery('.camera_wrap').camera({
            height: '25%',
            minHeight: '200px',
            loader: 'bar',
            pagination: false,
            navigation: false,
            time: 3000,
            portrait: true,

            // thumbnails: false,
            hover: false,
            playPause: false,
            pauseOnClick: false,
            opacityOnGrid: true,
            // mobileNavHover: true
            rows:12,
        });
    </script>
@endsection
