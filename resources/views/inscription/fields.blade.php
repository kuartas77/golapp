<h4 class="text-center w-auto text-uppercase"><strong>Información del deportista</strong></h4>
<div class="row col-12 ">

    <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
        <div class="form-group">
            <label for="unique_code">Código unico:</label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input id="unique_code" name="unique_code" type="text"
                   placeholder="Ingresa El Código Para Buscar"
                   class="form-control form-control-sm" autocomplete="off" required>
            <small class="form-text text-muted"> {{__('messages.search_player_by_code_info')}}</small>
        </div>
    </div>

    <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
        <div class="form-group">
            <label for="member_name">Nombre:</label>(<span class="text-danger">*</span>)
            <input id="member_name" name="member_name" type="text"
                   class="form-control form-control-sm" autocomplete="off" disabled="true" readonly>
            <input type="hidden" name="player_id" id="player_id">
        </div>
    </div>

    <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
        <div class="form-group">
            <label for="start_date">Fecha de inicio:</label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            <input id="start_date" name="start_date" type="text"
                   class="form-control form-control-sm date" autocomplete="off" required>
            <small class="form-text text-muted">{{__('messages.inscription_date_info')}}</small>
        </div>
    </div>

    <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
        <div class="form-group">
            <label for="training_group_id">Grupo de entrenamiento:</label>
            {{ html()->select('training_group_id', $training_groups, null)->attributes(['class' => 'form-control form-control-sm select2', 'id'=>'training_group_id'])->placeholder('Selecciona...') }}
            <small
                class="form-text text-muted">{{__('messages.provicional_group_info')}}</small>
        </div>
    </div>

    <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
        <div class="form-group">
            <label for="competition_groups">Grupo de competencia:</label>
            {{ html()->multiselect('competition_groups[]', $competition_groups, null)->attributes(['class' => 'form-control form-control-sm select2', 'id'=>'competition_groups'])->placeholder('Selecciona...') }}
        </div>
    </div>

    <div class="check col-md-4 col-sm-4 col-lg-4 col-xs-12">
        <small class="form-text text-muted mt-4">{!! __('messages.becado_text') !!}</small>
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="scholarship" id="scholarship" value="1">
                <label for="scholarship" class="checkboxsizeletter">¿ Becado ?</label>
            </div>
        </div>
    </div>
</div>

<div class="row col-12">
    <div class="col-6">
        <h4 class="text-center text-uppercase"><strong>Documentos</strong></h4>

        <div class="check col">
            <div class="form-group">
                <div class="checkbox">
                    <input type="checkbox" name="photos" id="photos" value="1">
                    <label for="photos" class="checkboxsizeletter">Fotos</label>
                </div>
                <div class="checkbox">
                    <input type="checkbox" name="copy_identification_document"
                        id="copy_identification_document" value="1">
                    <label for="copy_identification_document" class="checkboxsizeletter">Fotocopia Doc. Identidad</label>
                </div>
                <div class="checkbox">
                    <input type="checkbox" name="eps_certificate" id="eps_certificate"
                        value="1">
                    <label for="eps_certificate" class="checkboxsizeletter">Certificado
                        EPS,SISBEN</label>
                </div>
                <div class="checkbox">
                    <input type="checkbox" name="medic_certificate" id="medic_certificate"
                        value="1">
                    <label for="medic_certificate" class="checkboxsizeletter">Certificado
                        Médico</label>
                </div>
                <div class="checkbox">
                    <input type="checkbox" name="study_certificate" id="study_certificate"
                        value="1">
                    <label for="study_certificate" class="checkboxsizeletter">Fotocopia Doc.
                        Acudiente</label>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6">
        <h4 class="text-center text-uppercase">
            <strong>
                Grupo provisional (principal), <span class="text-warning">ESTE SÓLO SE UTILIZARÁ PARA LAS INSCRIPCIONES NUEVAS Y/O PREINSCRIPCIONES, no tendrá asistencias ni pagos.</span>
            </strong>
        </h4>

        <br>
        <h4 class="text-center text-uppercase"><strong>Pre-Inscripción</strong></h4>
        <small class="form-text text-muted">Al ser marcado cómo <span class="text-warning">"PREINSCRIPCIÓN"</span>, este <span class="text-danger">Deportista no aparecerá en el listado de asistencias ni pagos</span>, verifica la documentación y desmarcalo.</small>
        <div class="check col">
            <div class="form-group">
                <div class="checkbox">
                    <input type="checkbox" name="pre_inscription" id="pre_inscription" value="1">
                    <label for="pre_inscription" class="checkboxsizeletter">Preinscrito?</label>
                </div>
            </div>
        </div>
    </div>

    <!-- <div class="col-6">
        <h4 class="text-center text-uppercase"><strong>Productos</strong></h4>

        <div class="check col">
            <div class="form-group">
                <div class="checkbox">
                    <input type="checkbox" name="overalls" id="overalls" value="1">
                    <label for="overalls" class="checkboxsizeletter">Peto</label>
                </div>
                <div class="checkbox">
                    <input type="checkbox" name="presentation_uniform" id="presentation_uniform"
                        value="1">
                    <label for="presentation_uniform" class="checkboxsizeletter">Uniforme De Presentación</label>
                </div>
                <div class="checkbox">
                    <input type="checkbox" name="competition_uniform" id="competition_uniform"
                        value="1">
                    <label for="competition_uniform" class="checkboxsizeletter">Uniforme De Competencia</label>
                </div>
                <div class="checkbox">
                    <input type="checkbox" name="tournament_pay" id="tournament_pay" value="1">
                    <label for="tournament_pay" class="checkboxsizeletter">Pagó Inscripción Torneo 1</label>
                </div>
                <div class="checkbox">
                    <input type="checkbox" name="ball" id="ball" value="1">
                    <label for="ball" class="checkboxsizeletter">Pagó Inscripción Torneo 2</label>
                </div>
                <div class="checkbox">
                    <input type="checkbox" name="bag" id="bag" value="1">
                    <label for="bag" class="checkboxsizeletter">Pagó Inscripción Torneo 3</label>
                </div>
            </div>
        </div>
    </div> -->
</div>
