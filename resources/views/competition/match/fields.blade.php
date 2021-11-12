<div class="col-sm-12">

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('name', 'Grupo Competencia:') !!}<span class="text-danger">*</span>
                {!! Form::text('name', $information->name, ['class' => 'form-control form-control-sm','readonly']) !!}
                {!! Form::hidden('competition_group_id', $information->id, ['id' => 'competition_group_id']) !!}

            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('tournament_name', 'Torneo:') !!}
                {!! Form::text('tournament_name', $information->tournament->name, ['class' => 'form-control form-control-sm', 'readonly']) !!}
                {!! Form::hidden('tournament_id', $information->tournament->id, ['id' => 'tournament_id']) !!}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('user_id', 'Director Tec:') !!}
                {!! Form::text('user_id', $information->professor->name, ['class' => 'form-control form-control-sm', 'readonly']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2">
            <div class="form-group">
                {!! Form::label('num_match', 'Partido #:') !!}
                {!! Form::text('num_match', null, ['class' => 'form-control form-control-sm','required', 'placeholder'=>'Partido #']) !!}
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                {!! Form::label('place', 'Lugar:') !!}
                {!! Form::text('place', null, ['class' => 'form-control form-control-sm','required']) !!}
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                {!! Form::label('date', 'Fecha:') !!}
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                    </div>
                    {!! Form::text('date', null, ['class' => 'form-control form-control-sm','required']) !!}
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                {!! Form::label('hour', 'Hora:') !!}
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon2">
                            <i class="fas fa-clock"></i>
                        </span>
                    </div>
                    {!! Form::text('hour', null, ['class' => 'form-control form-control-sm timepicker','required']) !!}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('rival_name', 'Nombre Rival:') !!}
                {!! Form::text('rival_name', null, ['class' => 'form-control form-control-sm','required']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h4 class="text-themecolor text-center">Resultado Final</h4>
        </div>

        <div class="col-md-4"></div>
        <div class="col-md-2">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="far fa-futbol"></i>
                        </span>
                    </div>
                    {!! Form::text('final_score[soccer]', optional($information->match->final_score_array)->soccer, ['class' => 'form-control form-control-sm','required', 'placeholder'=>'Escuela']) !!}
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="far fa-futbol"></i>
                        </span>
                    </div>
                    {!! Form::text('final_score[rival]', optional($information->match->final_score_array)->rival, ['class' => 'form-control form-control-sm','required', 'placeholder'=>'Rival']) !!}
                </div>
            </div>
        </div>
        <div class="col-md-4"></div>
    </div>

    <div class="row">
        <div class="col-md-12 text-center">
            <h6 class="help-block">Al Final De La Página Está El Botón Guardar.</h6>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {!! Form::label('general_concept', 'Concepto General:') !!}
                {!! Form::textarea('general_concept', null, ['class' => 'form-control form-control-sm','size'=>'3x5']) !!}
            </div>
        </div>

        @if(!stripos(Request::url(),'/edit'))

        <div class="col-md-12 text-center">
            <button type="button" class="btn waves-effect waves-light btn-rounded btn-primary m-b-10"
                    data-toggle="modal" data-target="#modal_search_member">Agregar Deportista
            </button>
            <h6 class="help-block">El Deportista Se Agregará Temporalmente, Cómo Refuerzo.</h6>
        </div>
        @endif
    </div>
</div>
