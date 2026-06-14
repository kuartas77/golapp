<h6>T y C</h6>
<section>

    @if($requiresTutorSignature || $requiresPlayerSignature)
    <h6 class="row block-helper justify-content-center "><strong>Desliza con el mouse de su ordenador o si esta en dispositivo movil con su tactil firme en el area indicada.</strong></h6>
    @elseif(count($availablePortalContracts) === 0)
    <h6 class="row block-helper justify-content-center ">Por el momento no hay terminos y condiciones que aceptar.</h6>
    @else
    <h6 class="row block-helper justify-content-center">Los campos con (<span class="text-danger">*</span>) son requeridos.</h6>
    @endif
    <div class="row">

        <fieldset class="col-md-6 {{ !$requiresTutorSignature ? 'hidden' : ''}} p-2" >
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

        <fieldset class="col-md-6 {{ !$requiresPlayerSignature ? 'hidden' : ''}} p-2 ">
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

        <fieldset class="col-md-12 p-2 {{ count($availablePortalContracts) === 0 ? 'hidden' : ''}}">
            @foreach($availablePortalContracts as $contract)
            @continue(empty($contract['acceptance_field']) || !($contract['requires_acceptance'] ?? false))
            <div class="row">
                <div class="check col">
                    <div class="form-group">
                        <div class="checkbox">
                            <input type="checkbox" name="{{ $contract['acceptance_field'] }}" id="{{ $contract['acceptance_field'] }}" value="1">
                            <label for="{{ $contract['acceptance_field'] }}" class="checkboxsizeletter">
                                (<span class="text-danger">*</span>) Acepta los terminos y condiciones del
                                <a target="_blank" href="{{ $contract['url'] }}">{{ $contract['label'] }}</a>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </fieldset>
    </div>
</section>
