<h6>Información Del Deportista</h6>
<section>
    <h6 class="row block-helper justify-content-center">Los Campos Con (<span class="text-danger">*</span>) Son Requeridos.</h6>
    <h6 class="row block-helper justify-content-center"><span class="text-warning">La Foto debe ser tipo documento de lo contrario abtenerse de agregar la</span>, <strong>{{$school->name}}</strong> .</h6>
    <fieldset class="col-md-12 p-2">
        <div class="row col-md-12">

            <input type="hidden" name="year" value="{{$year}}" />
            <div class="col-md-3">
                <img src="{{ isset($player) ? $player->photo_url : asset('img/user.png') }}" class="rounded img-center mx-auto mb-1" alt="player" id="player-img" width="200" height="200">
                <div class="form-group">
                    <label>Foto JPG, JPEG, PNG</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-upload" accept="image/png, image/jpeg" name="photo">
                        <label class="custom-file-label" for="file-upload">Seleccionar...</label>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2">
                <div class="form-group">
                    <label for="identification_document" class=""># Documento de identidad (<span class="text-danger">*</span>)</label>
                    {{ html()->text('identification_document', null)->attributes(['class' => 'form-control']) }}
                </div>
                <div class="form-group">
                    <label for="document_type" class="">Tipo Documento (<span class="text-danger">*</span>)</label>
                    {{ html()->select('document_type', $document_types, null)->attributes(['class' => 'form-control select2'])->placeholder('Selecciona...') }}
                </div>
                <div class="form-group">
                    <label for="date_birth" class="">Fecha de nacimiento (<span class="text-danger">*</span>)</label>
                    {!! html()->text('date_birth', null)->attributes(['class' => 'form-control date']) !!}
                </div>
            </div>

            <div class="col-md-3 mb-2">
                <div class="form-group">
                    <label for="names" class="">Nombres (<span class="text-danger">*</span>)</label>
                    {{ html()->text('names', null)->attributes(['class' => 'form-control']) }}
                </div>
                <div class="form-group">
                    <label for="last_names" class="">Apellidos (<span class="text-danger">*</span>)</label>
                    {{ html()->text('last_names', null)->attributes(['class' => 'form-control']) }}
                </div>
                <div class="form-group">
                    <label for="place_birth" class="">Lugar de nacimiento (<span class="text-danger">*</span>)</label>
                    {{ html()->text('place_birth', null)->attributes(['class' => 'form-control ']) }}
                </div>
            </div>

            <div class="col-md-3 mb-2">
                <div class="form-group">
                    <label for="gender" class="">Genero (<span class="text-danger">*</span>)</label>
                    {{ html()->select('gender', $genders, null)->attributes(['class' => 'form-control'])->placeholder('Selecciona...') }}
                </div>
                <div class="form-group">
                    <label for="email" class="">Correo Electrónico (<span class="text-danger">*</span>)</label>
                    {{ html()->text('email', null)->attributes(['class' => 'form-control','autocomplete'=>'off']) }}
                </div>
                <div class="form-group">
                    <label for="mobile" class="">Números Teléfonicos / Celular</label>
                    {{ html()->text('mobile', null)->attributes(['class' => 'form-control']) }}
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-12">
                <div class="form-group">
                    <label for="medical_history" class="">Antecedentes Médicos</label>
                    {!! html()->textarea('medical_history', null)->attributes(['class' => 'form-control','size'=>'5x8']) !!}
                </div>
            </div>

        </div>

    </fieldset>
</section>