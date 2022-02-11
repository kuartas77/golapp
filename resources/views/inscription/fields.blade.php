<h6 class="text-center w-auto">Información Del Deportista</h6>
<div class="row col-md-12 ">

    <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
        <div class="form-group">
            <label for="unique_code">Código Unico</label><span class="text-danger">*</span>
            <span class="bar"></span>
            <input id="unique_code" name="unique_code" type="text"
                   placeholder="Ingresa El Código Para Buscar"
                   class="form-control form-control-sm" autocomplete="off" required>
            <small class="form-text text-muted">Buscará Deportistas Sin Inscripción</small>
        </div>
    </div>

    <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
        <div class="form-group">
            <label for="member_name">Nombre</label><span class="text-danger">*</span>
            <input id="member_name" name="member_name" type="text"
                   class="form-control form-control-sm" autocomplete="off" disabled="true" readonly>
            <input type="hidden" name="player_id" id="player_id">
        </div>
    </div>

    <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
        <div class="form-group">
            <label for="start_date">Fecha De Inicio</label><span class="text-danger">*</span>
            <span class="bar"></span>
            <input id="start_date" name="start_date" type="text"
                   class="form-control form-control-sm date" autocomplete="off" required>
            <small class="form-text text-muted">Se Agregará Al Año Seleccionado</small>
        </div>
    </div>

    <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
        <div class="form-group">
            <label for="training_group_id">Grupo De Entrenamiento</label>
            {!! Form::select('training_group_id', $training_groups , null, ['class' => 'form-control form-control-sm select2','placeholder' =>
            'Seleccione uno...', 'id'=>'training_group_id']) !!}
            <small
                class="form-text text-muted">Si No se Selecciona, Se Agregara Al Grupo
                Provicional</small>
        </div>
    </div>

    <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
        <div class="form-group">
            <label for="competition_group_id">Grupo De Competencia</label>
            {!! Form::select('competition_group_id', $competition_groups, null, ['class' => 'form-control form-control-sm select2',
            'placeholder'=>'Seleccionar...','id'=>'competition_group_id']) !!}
        </div>
    </div>

    <div class="check col-md-4 col-sm-4 col-lg-4 col-xs-12">
        <small class="form-text text-muted">Al Ser Becado, Todos Los Pagos Del Año Se Estableceran Cómo: "<span class="text-danger">Becado</span>"</small>
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="scholarship" id="scholarship" value="1">
                <label for="scholarship" class="checkboxsizeletter">¿ Becado ?</label>
            </div>
        </div>
    </div>
</div>

<h6 class="text-center w-auto">Documentos</h6>
<div class="row col-md-12 col-sm-12 col-lg-12 col-xs-12">

    <div class="check col-md-3 col-sm-4 col-xs-6">
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="photos" id="photos" value="1">
                <label for="photos" class="checkboxsizeletter">Fotos</label>
            </div>
        </div>
    </div>

    <div class="check col-md-3 col-sm-4 col-xs-6">
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="copy_identification_document"
                       id="copy_identification_document" value="1">
                <label for="copy_identification_document" class="checkboxsizeletter">Fotocopia
                    Doc.
                    Identidad</label>
            </div>
        </div>
    </div>

    <div class="check col-md-3 col-sm-4 col-xs-6">
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="eps_certificate" id="eps_certificate"
                       value="1">
                <label for="eps_certificate" class="checkboxsizeletter">Certificado
                    EPS,SISBEN</label>
            </div>
        </div>
    </div>

    <div class="check col-md-3 col-sm-4 col-xs-6">
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="medic_certificate" id="medic_certificate"
                       value="1">
                <label for="medic_certificate" class="checkboxsizeletter">Certificado
                    Médico</label>
            </div>
        </div>
    </div>

    <div class="check col-md-3 col-sm-4 col-xs-6">
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="study_certificate" id="study_certificate"
                       value="1">
                <label for="study_certificate" class="checkboxsizeletter">Fotocopia Doc.
                    Acudiente</label>
            </div>
        </div>
    </div>

</div>

<h6 class="text-center w-auto">Productos</h6>
<div class="row col-md-12 col-sm-12 col-lg-12 col-xs-12">

    <div class="check col-md-3 col-sm-4 col-xs-6">
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="overalls" id="overalls" value="1">
                <label for="overalls" class="checkboxsizeletter">Peto</label>
            </div>
        </div>
    </div>

    <div class="check col-md-3 col-sm-4 col-xs-6">
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="ball" id="ball" value="1">
                <label for="ball" class="checkboxsizeletter">Balón</label>
            </div>
        </div>
    </div>

    <div class="check col-md-3 col-sm-4 col-xs-6">
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="bag" id="bag" value="1">
                <label for="bag" class="checkboxsizeletter">Morral</label>
            </div>
        </div>
    </div>

    <div class="check col-md-3 col-sm-4 col-xs-6">
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="presentation_uniform" id="presentation_uniform"
                       value="1">
                <label for="presentation_uniform" class="checkboxsizeletter">Uniforme De
                    Presentación</label>
            </div>
        </div>
    </div>

    <div class="check col-md-3 col-sm-4 col-xs-6">
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="competition_uniform" id="competition_uniform"
                       value="1">
                <label for="competition_uniform" class="checkboxsizeletter">Uniforme De
                    Competencia</label>
            </div>
        </div>
    </div>

    <div class="check col-md-3 col-sm-4 col-xs-6">
        <div class="form-group">
            <div class="checkbox">
                <input type="checkbox" name="tournament_pay" id="tournament_pay" value="1">
                <label for="tournament_pay" class="checkboxsizeletter">Pagó Inscripción A
                    Torneo</label>
            </div>
        </div>
    </div>

</div>
