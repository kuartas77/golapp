@extends('layouts.app')
@section('title', 'Conformar Grupos De Competencia')
@section('content')
    <x-bread-crumb title="Conformar Grupos De Competencia" :option="0"/>
    <div class="row no-gutters">

        <div class="col-lg-2 col-md-2"></div>
        <div class="col-lg-8 col-md-8 col-sm-12">
            <div class="card m-b-0">
                <div class="card-body m-b-0">

                    <h6 class="card-subtitle">Selecciona El Grupo Para Mostrar Sus Integrantes Al Lado Derecho</h6>
                    <form action="{{route('ins_competition.index')}}" class="form-horizontal form-material"
                          id="form_admin" method="GET">
                        <div class="row form-body">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="training_group_destination">Grupo De Competencia</label>
                                    <span class="bar"></span>
                                    {!! Form::select('training_group_destination', $groupsCompetition, null, ['class' => 'form-control form-control-sm', 'placeholder'=>'seleccionar...', 'id' =>'training_group_destination']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="form-actions text-center">
                            <button class="btn waves-effect waves-light btn-rounded btn-info" id="search">Buscar
                            </button>
                            <a href="{{route('ins_competition.index')}}"
                               class="btn waves-effect waves-light btn-rounded btn-outline-warning"
                               id="clear">Limpiar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-md-2"></div>

        <div class="col-lg-6 col-md-6 col-sm-6">
            <div class="card m-b-0">
                <div class="card-body m-b-0">
                    <h6 class="card-subtitle text-themecolor m-t-5">Deportistas Sin Grupo De Competencia 
                        <strong id="inscriptions_count"></strong>
                    </h6>
                    <hr>
                    <div class="row row-cols-3 space col-lg-12 col-md-12 col-sm-12" id="inscriptions">
                        @each('templates.groups.div_row', $insWithOutGroup, 'inscription')
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6 ">
            <div class="card m-b-0">
                <div class="card-body m-b-0">
                    <h6 class="card-subtitle text-themecolor m-t-5">Grupo Seleccionado: 
                        <strong id="group_selected">Selecciona...</strong> 
                        <strong id="destination_count"></strong>
                    </h6>
                    <hr>
                    <div class="row row-cols-3 space col-lg-12 col-md-12 col-sm-12" id="destination">
                        @each('templates.groups.div_row', $insWithGroup, 'inscription')
                    </div>
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
    <script src="{{mix('js/adminInscriptionGCompetition.js')}}" defer></script>
@endsection
