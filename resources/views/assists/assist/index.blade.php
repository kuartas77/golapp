@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Asistencias" :option="0"/>
    <x-row-card col-outside="3" col-inside="6" >
        <h6 class="card-subtitle">El año de busqueda será el actual.</h6>
        @include('assists.assist.form')
    </x-row-card >
    <x-row-card col-inside="12" >
        <h6 class="card-subtitle text-themecolor m-b-0 m-t-0" id="group_name"></h6>
        @include('assists.assist.table')
    </x-row-card >
@endsection
@section('modals')
    @include('modals.assist_observation')
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}';
        $(function () {
            $(".preloader").fadeOut()
        })
    </script>
    <script type="text/javascript" src="{{mix('js/assist.js')}}"></script>
@endsection
