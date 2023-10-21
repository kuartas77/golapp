<section>
    <div class="col-md-12 p-2">
        <legend class="w-auto text-center">Información Básica</legend>
        <h6 class="row block-helper justify-content-center">Los Campos Con (<span class="text-danger">*</span>) Son Requeridos.</h6>

        <div class="row justify-content-center">
            <img src="{{$edit ? $player->photo_url : 'https://golapp.softdreamc.com/img/user.png' }}" class="rounded" alt="player" id="player-img" width="200" height="200">
        </div>

        <div class="row justify-content-center">
            <div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
                <div class="form-group">
                @if($edit)
                    <label for="unique_code" class="">Código Único</label>
                    {!! html()->text('unique_code', null)->attributes(['class' => 'form-control form-control-sm','disabled','id'=>'unique_code']) !!}
                @else
                    <label for="unique_code" class=""> Código Único (<span class="text-danger">*</span>)</label>
                    {!! html()->text('unique_code', null)->attributes(['class' => 'form-control form-control-sm','id'=>'unique_code']) !!}
                @endif
                </div>
            </div>

            <div class="col-sm-3 col-md-3 col-lg-3 col-xl-3">
                <div class="form-group">
                    <label>Foto</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file-upload" accept="image/png, image/jpeg" name="player">
                        <label class="custom-file-label" for="file-upload">Seleccionar...</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Nombres', 'names',) !!}(<span class="text-danger">*</span>)
                    {!! html()->text('names', null)->attributes(['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Apellidos', 'last_names') !!}(<span class="text-danger">*</span>)
                    {!! html()->text('last_names', null)->attributes(['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Doc de identidad', 'identification_document') !!}(<span class="text-danger">*</span>)
                    {!! html()->text('identification_document', null)->attributes(['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Genero', 'gender') !!}(<span class="text-danger">*</span>)
                    {{ html()->select('gender', $genders, null)->attributes(['class' => 'form-control form-control-sm select2'])->placeholder('Selecciona...') }}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('F. Nacimiento','date_birth') !!}(<span class="text-danger">*</span>)
                    {!! html()->text('date_birth', null)->attributes(['class' => 'form-control form-control-sm date']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('L. Nacimiento', 'place_birth') !!}(<span class="text-danger">*</span>)
                    {!! html()->text('place_birth', null)->attributes(['class' => 'form-control ']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('RH', 'rh') !!}(<span class="text-danger">*</span>)
                    {{ html()->select('rh', $blood_types, null)->attributes(['class' => 'form-control form-control-sm select2'])->placeholder('Selecciona...') }}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Eps', 'eps') !!}(<span class="text-danger">*</span>)
                    {!! html()->text('eps', null)->attributes(['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Direccion', 'address') !!}(<span class="text-danger">*</span>)
                    {!! html()->text('address', null)->attributes(['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Municipio', 'municipality') !!}(<span class="text-danger">*</span>)
                    {!! html()->text('municipality', null)->attributes(['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Barrio', 'neighborhood') !!}(<span class="text-danger">*</span>)
                    {!! html()->text('neighborhood', null)->attributes(['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>


            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Teléfonos/Celular', 'phones') !!}(<span class="text-danger">*</span>)
                    {!! html()->text('phones', null)->attributes(['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Correo Electrónico', 'email') !!}
                    {!! html()->text('email', null)->attributes(['class' => 'form-control form-control-sm','autocomplete'=>'off']) !!}
                    <small class="form-text text-muted">Correo Electrónico de notificación</small>
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Colegio/Escuela', 'school') !!}
                    {!! html()->text('school', null)->attributes(['class' => 'form-control form-control-sm', 'data-provide'=>'typeahead']) !!}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Grado', 'degree') !!}
                    {{ html()->select('degree', [1,2,3,4,5,6,7,8,9,10,11], null)->attributes(['class' => 'form-control form-control-sm select2'])->placeholder('Selecciona...') }}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Posición en el campo:', 'position_field') !!}
                    {{ html()->select('position_field', $positions, null)->attributes(['class' => 'form-control form-control-sm select2'])->placeholder('Selecciona...') }}
                </div>
            </div>

            <div class="col-sm-2 col-md-2 col-lg-2 col-xl-2">
                <div class="form-group">
                    {!! html()->label('Perfil dominante:', 'dominant_profile') !!}
                    {{ html()->select('dominant_profile', $dominant_profile, null)->attributes(['class' => 'form-control form-control-sm select2'])->placeholder('Selecciona...') }}
                </div>
            </div>

        </div>

    </div>
</section>
