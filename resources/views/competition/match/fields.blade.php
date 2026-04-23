<div>
    <div class="match-form-block">
        <div class="match-form-heading">
            <h5 class="match-form-title">Datos del grupo</h5>
            <p class="match-form-subtitle">Información base del control de competencia.</p>
        </div>

        <div class="form-group">
            <label for="competition_group_id">Grupo Competencia (<span class="text-danger">*</span>)</label>
            {!! html()->text('name', $information->name)->attributes(['class' => 'form-control form-control-sm','readonly']) !!}
            {!! html()->hidden('competition_group_id', $information->id)->attributes(['id' => 'competition_group_id']) !!}
        </div>

        <div class="form-group">
            <label for="tournament_id">Torneo (<span class="text-danger">*</span>)</label>
            {{ html()->select('tournament_id', $tournaments, $information->tournament->id)->attributes(['id'=>'tournament_id','class' => 'form-control form-control-sm','required'])->placeholder('Selecciona...') }}
        </div>

        <div class="form-group">
            <label for="user_id">Director técnico (<span class="text-danger">*</span>)</label>
            {!! html()->text('user_id', $information->professor->name)->attributes(['id'=>'user_id', 'class' => 'form-control form-control-sm', 'readonly']) !!}
        </div>
    </div>

    <div class="match-form-block">
        <div class="match-form-heading">
            <h5 class="match-form-title">Programación</h5>
            <p class="match-form-subtitle">Define cuándo, dónde y contra quién se juega.</p>
        </div>

        <div class="row">
            <div class="col-sm-6">
                <div class="form-group">
                    <label for="num_match">Partido # (<span class="text-danger">*</span>)</label>
                    {!! html()->text('num_match', null)->attributes(['class' => 'form-control form-control-sm','required', 'placeholder'=>'Partido #']) !!}
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="hour">Hora (<span class="text-danger">*</span>)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon2">
                                <i class="fas fa-clock"></i>
                            </span>
                        </div>
                        {!! html()->text('hour', null)->attributes(['class' => 'form-control form-control-sm timepicker with-icon','required']) !!}
                    </div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="date">Fecha (<span class="text-danger">*</span>)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="far fa-calendar-alt"></i>
                            </span>
                        </div>
                        {!! html()->text('date', null)->attributes(['class' => 'form-control form-control-sm with-icon','required', 'id' => 'date']) !!}
                    </div>
                </div>
            </div>

            <div class="col-sm-6">
                <div class="form-group">
                    <label for="place">Lugar (<span class="text-danger">*</span>)</label>
                    {!! html()->text('place', null)->attributes(['class' => 'form-control form-control-sm','required']) !!}
                </div>
            </div>

            <div class="col-12">
                <div class="form-group">
                    <label for="rival_name">Nombre rival (<span class="text-danger">*</span>)</label>
                    {!! html()->text('rival_name', null)->attributes(['class' => 'form-control form-control-sm','required']) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="match-form-block">
        <div class="match-form-heading">
            <h5 class="match-form-title">Resultado y concepto</h5>
            <p class="match-form-subtitle">Resume el marcador y el balance general del partido.</p>
        </div>

        <div class="match-score-strip">
            <div class="match-score-side">
                <div class="form-group">
                    <label for="final_score_soccer">Escuela (<span class="text-danger">*</span>)</label>
                    {!! html()->text('final_score[soccer]', optional($information->match->final_score_array)->soccer)->attributes(['class' => 'form-control form-control-sm','required', 'placeholder'=>'0', 'id' => 'final_score_soccer']) !!}
                </div>
            </div>

            <div class="match-score-divider">vs</div>

            <div class="match-score-side">
                <div class="form-group">
                    <label for="final_score_rival">Rival (<span class="text-danger">*</span>)</label>
                    {!! html()->text('final_score[rival]', optional($information->match->final_score_array)->rival)->attributes(['class' => 'form-control form-control-sm','required', 'placeholder'=>'0', 'id' => 'final_score_rival']) !!}
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="general_concept">Concepto general</label>
            {!! html()->textarea('general_concept', null)->attributes(['class' => 'form-control form-control-sm','rows' => 4]) !!}
        </div>
    </div>

    @if(!stripos(Request::url(),'/edit'))
        <div class="match-form-block">
            <div class="match-form-heading">
                <h5 class="match-form-title">Acciones rápidas</h5>
                <p class="match-form-subtitle">Agrega jugadores manualmente o carga el formato del partido.</p>
            </div>

            <div class="match-toolbar">
                <div class="match-toolbar-item">
                    <button type="button" class="btn waves-effect waves-light btn-rounded btn-info"
                            data-toggle="modal" data-target="#modal_search_member">
                        Agregar Deportista
                    </button>
                </div>

                <div class="match-toolbar-item">
                    <a href="{{route('export.match_detail', [$information->id])}}"
                       class="btn waves-effect waves-light btn-rounded btn-outline-info">
                        Descargar Formato
                    </a>
                </div>

                <div class="match-toolbar-item match-toolbar-item--file">
                    <label for="file-upload">Cargar archivo</label>
                    <input type="file" id="file-upload" name="details" class="form-control form-control-sm">
                </div>
            </div>

            <p class="match-toolbar-note">
                Los jugadores agregados manualmente quedarán al inicio del listado y el archivo debe usar el formato descargado.
            </p>
        </div>
    @endif
</div>
