<h6>Firmas</h6>
<section>
    <h6 class="row block-helper justify-content-center">Los campos con (<span class="text-danger">*</span>) son requeridos.</h6>
    <h6 class="row block-helper justify-content-center "><strong>Desliza con el mouse de su ordenador o si esta en dispositivo movil con su tactil firme en el area indicada.</strong></h6>
    <div class="row">
        <fieldset class="col-md-6 p-2">
            <legend>Firma Del Acudiente (<span class="text-danger">*</span>):</legend>
            <h6 class="row block-helper justify-content-center ">Firma de la persona que va a figurar en el <strong>&nbsp;CONTRATO&nbsp;</strong></h6>
            <div class="row">
                <div class="col-md-8 mb-3">
                    <div class="form-group">
                        <canvas id="firma_tutor"></canvas>
                        <input name="sign_tutor" id="sign_tutor" type="hidden">
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-group">
                        <button type="button" class="btn btn-danger waves-effect text-left" onclick="signaturePadTutor.clear()">Limpiar</button>
                    </div>
                </div>
            </div>

        </fieldset>

        <fieldset class="col-md-6 p-2">
            <legend>Firma del Deportista (<span class="text-danger">*</span>):</legend>
            <h6 class="row block-helper justify-content-center ">Firma del <strong>&nbsp;Deportista&nbsp;</strong> que hará parte de {{$school->name}}</h6>
            <div class="row">
                <div class="col-md-8 mb-3">
                    <div class="form-group">
                        <canvas id="firma_alumno"></canvas>
                        <input name="sign_alumno" id="sign_alumno" type="hidden">
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="form-group">
                        <button type="button" class="btn btn-danger waves-effect text-left" onclick="signaturePadAlumno.clear()">Limpiar</button>
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset class="col-md-12">
            <div class="row">
                <div class="check col">
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" name="contrato_aff" id="contrato_aff" value="1">
                            <label for="contrato_aff" class="checkboxsizeletter">(<span class="text-danger">*</span>) Acepta los terminos y condiciones del
                                <a target="_blank" href="{{asset('img/CONTRATO DE AFILIACIÓN Y CORRESPONSABILIDAD DEPORTIVA.pdf')}}">CONTRATO DE AFILIACIÓN Y CORRESPONSABILIDAD DEPORTIVA</a>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="check col">
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" name="contrato_insc" id="contrato_insc" value="1">
                            <label for="contrato_insc" class="checkboxsizeletter">
                                (<span class="text-danger">*</span>) Acepta los terminos y condiciones del
                                <a target="_blank" href="{{asset('img/CONTRATO DE INSCRIPCIÓN.pdf')}}">CONTRATO DE INSCRIPCIÓN</a>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</section>