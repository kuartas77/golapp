<h6>Información Básica</h6>
<section>
    <h6 class="row block-helper justify-content-center">Los Campos Con (<span class="text-danger">*</span>) Son Requeridos.</h6>
    <fieldset class="col-md-12">
        <legend>Personal:</legend>
        <div class="row col-md-12">

            <div class="col-md-3">
                <img src="{{$edit ? $player->photo_url : asset('img/user.png') }}" class="rounded img-center mx-auto mb-1" alt="player" id="player-img" width="200" height="200">
                <div class="form-group">
                    <label>Foto</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-upload" accept="image/png, image/jpeg" name="player">
                        <label class="custom-file-label" for="file-upload">Seleccionar...</label>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    @if($edit)
                    <label for="unique_code" class="">Código Único</label>
                    {{ html()->text('unique_code', null)->attributes(['class' => 'form-control form-control-sm','disabled','id'=>'unique_code']) }}
                    @else
                    <label for="unique_code" class=""> Código Único</label>
                    {{ html()->text('unique_code', null)->attributes(['class' => 'form-control form-control-sm','id'=>'unique_code']) }}
                    <small class="form-text text-muted">Se puede generar automaticamente. ejemplo: 20240001</small>
                    @endif
                </div>
                <div class="form-group">
                    {{ html()->label('# Documento de identidad', 'identification_document') }}(<span class="text-danger">*</span>)
                    {{ html()->text('identification_document', null)->attributes(['class' => 'form-control form-control-sm']) }}
                </div>
                <div class="form-group">
                    {!! html()->label('Fecha de nacimiento','date_birth') !!}(<span class="text-danger">*</span>)
                    {!! html()->text('date_birth', null)->attributes(['class' => 'form-control form-control-sm date']) !!}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Nombres', 'names',) }}(<span class="text-danger">*</span>)
                    {{ html()->text('names', null)->attributes(['class' => 'form-control form-control-sm']) }}
                </div>
                <div class="form-group">
                    <label for="document_type" class="">Tipo Documento (<span class="text-danger">*</span>)</label>
                    {{ html()->select('document_type', $document_types, null)->attributes(['class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
                </div>
                <div class="form-group">
                    {{ html()->label('Lugar de nacimiento', 'place_birth') }}(<span class="text-danger">*</span>)
                    {{ html()->text('place_birth', null)->attributes(['class' => 'form-control form-control-sm']) }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Apellidos', 'last_names') }}(<span class="text-danger">*</span>)
                    {{ html()->text('last_names', null)->attributes(['class' => 'form-control form-control-sm']) }}
                </div>
                <div class="form-group">
                    {{ html()->label('Genero', 'gender') }}(<span class="text-danger">*</span>)
                    {{ html()->select('gender', $genders, null)->attributes(['class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
                </div>
                <div class="form-group">
                    {{ html()->label('Grupo sanguíneo', 'rh') }}(<span class="text-danger">*</span>)
                    {{ html()->select('rh', $blood_types, null)->attributes(['class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
                </div>
            </div>

        </div>
    </fieldset>
</section>

<h6>Información General</h6>
    <section>
        <h6 class="row block-helper justify-content-center">Los Campos Con (<span class="text-danger">*</span>) Son Requeridos.</h6>
    <fieldset class="col-md-12">
        <legend>General:</legend>
        <div class="row">

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('EPS', 'eps') }}(<span class="text-danger">*</span>)
                    {{ html()->text('eps', null)->attributes(['class' => 'form-control form-control-sm']) }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Correo Electrónico', 'email') }}
                    {{ html()->text('email', null)->attributes(['class' => 'form-control form-control-sm','autocomplete'=>'off']) }}
                    <small class="form-text text-muted">Correo Electrónico de notificación</small>
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Direccion de residencia', 'address') }}(<span class="text-danger">*</span>)
                    {{ html()->text('address', null)->attributes(['class' => 'form-control form-control-sm']) }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Municipio de residencia', 'municipality') }}(<span class="text-danger">*</span>)
                    {{ html()->text('municipality', null)->attributes(['class' => 'form-control form-control-sm']) }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Barrio de residencia', 'neighborhood') }}(<span class="text-danger">*</span>)
                    {{ html()->text('neighborhood', null)->attributes(['class' => 'form-control form-control-sm']) }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Números Teléfonicos / Celular', 'phones') }}(<span class="text-danger">*</span>)
                    {{ html()->text('phones', null)->attributes(['class' => 'form-control form-control-sm']) }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Institución educativa', 'school') }}
                    {{ html()->text('school', null)->attributes(['class' => 'form-control form-control-sm', 'data-provide'=>'typeahead']) }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Grado que cursa', 'degree') }}
                    {{ html()->select('degree', [0,1,2,3,4,5,6,7,8,9,10,11], null)->attributes(['class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Jornada de estudio', 'jornada') }}
                    {{ html()->select('jornada', $jornada, null)->attributes(['class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Nombre del Seguro Estudiantil', 'student_insurance') }}
                    {{ html()->text('student_insurance', null)->attributes(['class' => 'form-control form-control-sm', 'data-provide'=>'typeahead']) }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Posición en el campo:', 'position_field') }}
                    {{ html()->select('position_field', $positions, null)->attributes(['class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
                </div>
            </div>

            <div class="col-md-3">
                <div class="form-group">
                    {{ html()->label('Perfil dominante:', 'dominant_profile') }}
                    {{ html()->select('dominant_profile', $dominant_profile, null)->attributes(['class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
                </div>
            </div>

        </div>

        <div class="row">

            <div class="col-md-12 mb-2">
                <div class="form-group">
                    <label for="medical_history" class="">Antecedentes Médicos</label>
                    {!! html()->textarea('medical_history', null)->attributes(['class' => 'form-control','size'=>'5x8']) !!}
                </div>
            </div>

        </div>

    </fieldset>
</section>
