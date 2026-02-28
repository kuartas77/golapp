@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Asistencias" :option="0"/>
    <x-row-card col-inside="12" >
        @include('assists.assist.form')
        <hr>
        <h6 class="card-subtitle text-themecolor m-b-0 m-t-0" id="group_name"></h6>
        @include('assists.assist.table')
    </x-row-card >
@endsection
@section('modals')
    @include('modals.assist_observation')
    @include('modals.modal_attendance')
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}';
        const options = @json($optionAssist);
        $(function () {
            $(".preloader").fadeOut()
        })
    </script>
    <script type="text/javascript" src="{{mix('js/assist.js')}}"></script>
@endsection
