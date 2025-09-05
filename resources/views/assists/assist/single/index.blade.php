@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Asistencias" :option="0"/>
    <x-row-card col-inside="12">
        @include('assists.assist.form')
        <hr>
        <div class="col row">
            <h6 class="card-subtitle text-themecolor m-b-0 m-t-0">Entrenamientos <span id="ClassCount"></span></h6>
            <div class="col-md-12" id="classdays"><small class="form-text text-muted">Selecciona un grupo y mes.</small></div>
        </div>
        <hr>
        <h6 class="card-subtitle text-themecolor m-b-0 m-t-0" id="group_name"></h6>
        <h6 class="card-subtitle text-themecolor m-b-0 m-t-0" id="class_name"></h6>
        @include('assists.assist.single.table')
    </x-row-card >
@endsection
@section('modals')
    @include('modals.assist_observation')
    @include('modals.modal_attendance')
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}';
        let url_classDays = "{{route('group_classdays')}}";
        const options = @json($optionAssist);
        $(function () {
            $(".preloader").fadeOut()
        })
    </script>
    <script type="text/javascript" src="{{asset('js/single_assist.js')}}"></script>
@endsection
