@extends('layouts.app')
@section('title', 'Conformar Grupos Entrenamiento')
@section('content')
    <x-bread-crumb title="Conformar Grupos Entrenamiento" :option="0"/>
    <div class="row">

        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        {{ html()->label('Grupo De Origen:','training_group_origin') }}
                        {{ html()->select('training_group_origin', $groups, null)->attributes(['class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
                    </div>
                    <div class="row row-cols-3 space col-sm-12" id="origin"></div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        {{ html()->label('Grupo De Destino:', 'training_group_destiny') }}
                        {{ html()->select('training_group_destiny', $groups, null)->attributes(['class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
                    </div>
                    <div class="row row-cols-3 space col-sm-12" id="destiny"></div>
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
