@extends('layouts.app')
@section('title', 'Conformar Grupos Entrenamiento')
@section('content')
    <x-bread-crumb title="Conformar Grupos Entrenamiento" :option="0"/>
    <div class="row">

        <div class="col-xl-1 col-lg-1 col-md-1"></div>
        <div class="col-xl-5 col-lg-5 col-md-5 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        {!! Form::label('training_group_origin', 'Grupo De Origen:') !!}
                        {!! Form::select('training_group_origin', $groups, null, ['class' => 'form-control form-control-sm', 'placeholder'=>'seleccionar...']) !!}
                    </div>
                    <div class="row row-cols-3 space col-sm-12" id="origin"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-5 col-lg-5 col-md-5 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        {!! Form::label('training_group_destiny', 'Grupo De Destino:') !!}
                        {!! Form::select('training_group_destiny', $groups, null, ['class' => 'form-control form-control-sm', 'placeholder'=>'seleccionar...']) !!}
                    </div>
                    <div class="row row-cols-3 space col-sm-12" id="destiny"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-1 col-lg-1 col-md-1"></div>
    </div>
@endsection
@section('scripts')
    <script>
        const urlCurrent = "{{URL::current()}}";
        $(function () {
            $(".preloader").fadeOut()
        })
    </script>
    <script src="{{mix('js/adminInscriptionGTraining.js')}}" defer></script>
@endsection
