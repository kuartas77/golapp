<h6>Información Familiar</h6>
<section>
    <h6 class="row block-helper justify-content-center">Los campos con (<span class="text-danger">*</span>) son requeridos.</h6>
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
                        {{ html()->text('tutor_email', null)->attributes(['class' => 'form-control', "onblur" => "return forceLower(this);"]) }}
                        <small class="form-text text-muted">Correo Electrónico para enviar notificaciónes.</small>
                    </div>
                </div>


        </fieldset>
    </div>
</section>
