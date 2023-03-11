<div class="col-sm-12">

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label for="competition_group_id">Grupo Competencia:</label>(<span class="text-danger">*</span>)
                {!! Form::text('name', $information->name, ['class' => 'form-control form-control-sm','readonly']) !!}
                {!! Form::hidden('competition_group_id', $information->id, ['id' => 'competition_group_id']) !!}

            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="tournament_id">Torneo:</label>(<span class="text-danger">*</span>)
                {!! Form::select('tournament_id', $tournaments , $information->tournament->id, ['id'=>'tournament_id','class' => 'form-control form-control-sm','placeholder' =>'Seleccione uno...','required']) !!}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="user_id">Director Tec:</label>(<span class="text-danger">*</span>)
                {!! Form::text('user_id', $information->professor->name, ['id'=>'user_id', 'class' => 'form-control form-control-sm', 'readonly']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2">
            <div class="form-group">
                <label for="num_match">Partido #:</label>(<span class="text-danger">*</span>)
                {!! Form::text('num_match', null, ['class' => 'form-control form-control-sm','required', 'placeholder'=>'Partido #']) !!}
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label for="place">Lugar:</label>(<span class="text-danger">*</span>)
                {!! Form::text('place', null, ['class' => 'form-control form-control-sm','required']) !!}
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label for="date">Fecha:</label>(<span class="text-danger">*</span>)
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                    </div>
                    {!! Form::text('date', null, ['class' => 'form-control form-control-sm with-icon','required']) !!}
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label for="hour">Hora:</label>(<span class="text-danger">*</span>)
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="basic-addon2">
                            <i class="fas fa-clock"></i>
                        </span>
                    </div>
                    {!! Form::text('hour', null, ['class' => 'form-control form-control-sm timepicker with-icon','required']) !!}
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="rival_name">Nombre Rival:</label>(<span class="text-danger">*</span>)
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
                    {!! Form::text('final_score[soccer]', optional($information->match->final_score_array)->soccer, ['class' => 'form-control form-control-sm with-icon','required', 'placeholder'=>'Escuela']) !!}
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
                    {!! Form::text('final_score[rival]', optional($information->match->final_score_array)->rival, ['class' => 'form-control form-control-sm with-icon','required', 'placeholder'=>'Rival']) !!}
                </div>
            </div>
        </div>
        <div class="col-md-4"></div>
    </div>

    <div class="row">
        <div class="col-md-12 text-center">
            <h6 class="help-block">Al final de la página se encuentra el botón de guardar <a href="#button_save" class="badge badge-info">Click para ir</a>.</h6>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                <label for="general_concept">Concepto General:</label>
                {!! Form::textarea('general_concept', null, ['class' => 'form-control form-control-sm','size'=>'3x5']) !!}
            </div>
        </div>
    </div>

    <div class="row col-12 text-center">
        @if(!stripos(Request::url(),'/edit'))
            <div class="col-4">
                <div class="form-group">
                    <button type="button" class="btn waves-effect waves-light btn-rounded btn-primary m-b-10"
                            data-toggle="modal" data-target="#modal_search_member">Agregar Deportista
                    </button>
                    <h6 class="help-block">El deportista se agregará temporalmente, cómo refuerzo.</h6>
                </div>
            </div>

            <div class="col-4">
                <div class="form-group">
                    <label>Formato</label> 
                    <a href="{{route('export.match_detail', [$information->id])}}" class="btn waves-effect waves-light btn-rounded btn-primary m-b-10">Descargar</a>
                    <h6 class="help-block">Descargar el formato en excel.</h6>
                    <small class="help-block">Es el mismo formato que se debe subir</small>
                </div>
            </div>

            <div class="col-4">
                <div class="form-group">
                    <label>Archivo</label> 
                    <input type="file" id="file-upload" name="details" class="btn waves-effect waves-light btn-rounded btn-primary m-b-10">
                    <h6 class="help-block">Se cargará la información que contenga el archivo.</h6>
                </div>
            </div>
        @endif
    </div>
</div>
