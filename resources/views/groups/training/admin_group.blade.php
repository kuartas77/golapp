@extends('layouts.app')
@section('title', 'Conformar Grupos Entrenamiento')
@section('content')
    @include('templates.bread_crumb', ['title' => 'Conformar Grupos Entrenamiento', 'option' => 0])
    <div class="row no-gutters">

        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        {!! Form::label('training_group_origin', 'Grupo De Origen:') !!}
                        {!! Form::select('training_group_origin', $groups, null, ['class' => 'form-control form-control-sm', 'placeholder'=>'seleccionar...']) !!}
                    </div>
                    <div class="space col-sm-12" id="origin"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        {!! Form::label('training_group_destiny', 'Grupo De Destino:') !!}
                        {!! Form::select('training_group_destiny', $groups, null, ['class' => 'form-control form-control-sm', 'placeholder'=>'seleccionar...']) !!}
                    </div>
                    <div class="space col-sm-12" id="destiny"></div>
                </div>
            </div>
        </div>

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
