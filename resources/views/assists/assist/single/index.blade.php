@extends('layouts.app')
@section('content')
<x-bread-crumb title="Asistencias" :option="0" />

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                @include('assists.assist.single.form')
                <hr>
                <div class="col row">
                    <h6 class="card-subtitle text-themecolor m-b-0 m-t-0">Entrenamientos <span id="ClassCount"></span></h6>
                    <div class="col-md-12" id="classdays">
                        <small class="form-text text-muted">Selecciona un grupo y mes.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h4 class="card-subtitle text-themecolor m-b-0 m-t-0" id="group_name">Selecciona Grupo De Entrenamiento</h4>
                <h4 class="card-subtitle text-themecolor m-b-0 m-t-0" id="class_name"></h4>
                @include('assists.assist.single.table')
            </div>
        </div>
    </div>
</div>

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
    $(function() {
        $(".preloader").fadeOut()
    })
</script>
<script type="text/javascript" src="{{asset('js/single_assist.js')}}"></script>
@endsection