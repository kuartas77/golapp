<h6>T y C</h6>
<section>
    <h6 class="row block-helper justify-content-center">Los campos con (<span class="text-danger">*</span>) son requeridos.</h6>
    @if($school->create_contract)
    <h6 class="row block-helper justify-content-center "><strong>Desliza con el mouse de su ordenador o si esta en dispositivo movil con su tactil firme en el area indicada.</strong></h6>
    @endif
    <div class="row">
        <fieldset class="col-md-6 {{ !$school->create_contract ? 'hidden' : ''}} p-2" >
            <legend>Firma Del Acudiente (<span class="text-danger">*</span>):</legend>
            <h6 class="row block-helper justify-content-center ">Firma de la persona que va a figurar en el <strong>&nbsp;CONTRATO&nbsp;</strong></h6>
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-4">
                    <div class="form-group">
                        <canvas id="firma_tutor" width=160 height=150></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <button type="button" class="btn btn-danger waves-effect text-left" onclick="signaturePadTutor.clear()">Limpiar</button>
                    </div>
                </div>
                <div class="col-md-2"></div>
            </div>

        </fieldset>

        <fieldset class="col-md-6 {{ !$school->sign_player ? 'hidden' : ''}} p-2 ">
            <legend>Firma del Deportista (<span class="text-danger">*</span>):</legend>
            <h6 class="row block-helper justify-content-center ">Firma del <strong>&nbsp;Deportista&nbsp;</strong> que hará parte de {{$school->name}}</h6>
            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-4">
                    <div class="form-group">
                        <canvas id="firma_alumno" width=160 height=150></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <button type="button" class="btn btn-danger waves-effect text-left" onclick="signaturePadPlayer.clear()">Limpiar</button>
                    </div>
                </div>
                <div class="col-md-2"></div>
            </div>
        </fieldset>

        <fieldset class="col-md-12 p-2 {{ (!$school->create_contract && !$school->sign_player) ? 'hidden' : ''}}">
            <div class="row {{ !$school->sign_player ? 'hidden' : ''}}">
                <div class="check col">
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" name="contrato_aff" id="contrato_aff" value="1">
                            <label for="contrato_aff" class="checkboxsizeletter">(<span class="text-danger">*</span>) Acepta los terminos y condiciones del
                                <a target="_blank" href="{{asset('contracts/'.$school->slug.'/CAFICODEPOR.pdf')}}">CONTRATO DE AFILIACIÓN Y CORRESPONSABILIDAD DEPORTIVA</a>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row {{ !$school->create_contract ? 'hidden' : ''}}">
                <div class="check col">
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" name="contrato_insc" id="contrato_insc" value="1">
                            <label for="contrato_insc" class="checkboxsizeletter">
                                (<span class="text-danger">*</span>) Acepta los terminos y condiciones del
                                <a target="_blank" href="{{asset('contracts/'.$school->slug.'/COINSCRIP.pdf')}}">CONTRATO DE INSCRIPCIÓN</a>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</section>