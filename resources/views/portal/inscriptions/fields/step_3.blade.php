<h6>Información Familiar</h6>
<section>
    <h6 class="row block-helper justify-content-center">Los campos con (<span class="text-danger">*</span>) son requeridos.</h6>
    <h6 class="row block-helper justify-content-center "><strong>Sí el acudiente es el padre o la madre no es necesario ingresar la información en los otros campos respectivamente.</strong></h6>
    <div class="row">
        <fieldset class="col-md-12 p-2">
            <legend>Acudiente:</legend>
            <h6 class="row block-helper justify-content-center ">Esta persona es la que va a figurar en el<strong>&nbsp;CONTRATO&nbsp;</strong>con<strong>&nbsp;{{ $school->name }}&nbsp;</strong></h6>
            <div class="row col-md-12">

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tutor_name">Nombres completos (<span class="text-danger">*</span>)</label>
                        {{ html()->text('tutor_name', null)->attributes(['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tutor_doc"># Doc de identidad (<span class="text-danger">*</span>)</label>
                        {{ html()->text('tutor_doc', null)->attributes(['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tutor_relationship">Parentesco (<span class="text-danger">*</span>)</label>
                        {{ html()->select('tutor_relationship', $relationships, null)->attributes(['class' => 'form-control'])->placeholder('Selecciona...') }}
                    </div>

                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tutor_phone">Whatsapp (<span class="text-danger">*</span>)</label>
                        {{ html()->text('tutor_phone', null)->attributes(['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tutor_work">Empresa donde labora (<span class="text-danger">*</span>)</label>
                        {{ html()->text('tutor_work', null)->attributes(['class' => 'form-control']) }}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tutor_position_held">Cargo que desempeña (<span class="text-danger">*</span>)</label>
                        {{ html()->text('tutor_position_held', null)->attributes(['class' => 'form-control']) }}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tutor_email">Correo electrónico (<span class="text-danger">*</span>)</label>
                        {{ html()->text('tutor_email', null)->attributes(['class' => 'form-control']) }}
                        <small class="form-text text-muted">Correo Electrónico para enviar notificaciónes.</small>
                    </div>
                </div>


        </fieldset>
    </div>

    <fieldset class="col-md-12 p-2">
        <legend>Madre:</legend>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="mom_name">Nombres completos </label>
                    {{ html()->text('mom_name', null)->attributes(['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="mom_doc"># Doc de identidad </label>
                    {{ html()->text('mom_doc', null)->attributes(['class' => 'form-control']) }}
                    <input name="relationship_mom" value="15" type="hidden" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="mom_phone">WhatsApp </label>
                    {{ html()->text('mom_phone', null)->attributes(['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="mom_work">Ocupación </label>
                    {{ html()->text('mom_work', null)->attributes(['class' => 'form-control']) }}
                </div>
            </div>
        </div>
    </fieldset>

    <fieldset class="col-md-12 p-2">
        <legend>Padre:</legend>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="dad_name">Nombres completos </label>
                    {{ html()->text('dad_name', null)->attributes(['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="dad_doc"># Doc de identidad </label>
                    {{ html()->text('dad_doc', null)->attributes(['class' => 'form-control']) }}
                    <input name="relationship_dad" value="20" type="hidden" />
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="dad_phone">WhatsApp </label>
                    {{ html()->text('dad_phone', null)->attributes(['class' => 'form-control']) }}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="dad_work">Ocupación </label>
                    {{ html()->text('dad_work', null)->attributes(['class' => 'form-control']) }}
                </div>
            </div>
        </div>
    </fieldset>

</section>