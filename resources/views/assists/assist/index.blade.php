@extends('layouts.app')
@section('title', 'Asistencias')
@section('content')
    @include('templates.bread_crumb', ['title' => 'Asistencias', 'option' => 0])
    <div class="row">
        <div class="col-2"></div>
        <div class="col-8">
            <div class="card">
                <div class="card-body">

                    <h6 class="card-subtitle">El año de busqueda será el actual.</h6>

                    @include('assists.assist.form')
                </div>
            </div>
        </div>
        <div class="col-2"></div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle text-themecolor m-b-0 m-t-0" id="group_name"></h6>
                    @include('assists.assist.table')
                </div>
            </div>
        </div>
    </div>
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
