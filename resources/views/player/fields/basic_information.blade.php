<h6>Información Básica</h6>
<section>
    <fieldset class="col-md-12 p-2">

        <div class="row justify-content-center">
            <div class="col-md-3 col-sm-12 col-xs-12 form-group">
                @if($edit)
                    <label for="unique_code" class="">Código Único</label>
                    {!! Form::text('unique_code', null, ['class' => 'form-control form-control-sm','disabled']) !!}
                @else
                    <label for="unique_code" class=""> Código Único <span class="text-danger">*</span></label>
                    {!! Form::text('unique_code', null, ['class' => 'form-control form-control-sm']) !!}
                @endif
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-4">
                <div class="form-group custom-file">
                    @if($edit)
                        <input
                            id="file-upload"
                            class="custom-file-input"
                            type="file"
                            name="image"
                            accept="image/jpeg , image/jpg, image/png"/>
                        <label class="custom-file-label" for="file-upload">Seleccionar Archivo</label>
                    @else
                        <input
                            id="file-upload"
                            class="custom-file-input"
                            type="file"
                            name="image"
                            accept="image/jpeg , image/jpg, image/png"
                            placeholder="Seleccionar Archivo"/>
                        <label class="custom-file-label" for="file-upload">Seleccionar Archivo</label>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('names', 'Nombres') !!}<span class="text-danger">*</span>
                    {!! Form::text('names', null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('last_names', 'Apellidos') !!}<span class="text-danger">*</span>
                    {!! Form::text('last_names', null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('identification_document', 'Doc de identidad') !!}<span class="text-danger">*</span>
                    {!! Form::text('identification_document', null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('gender', 'Genero') !!}<span class="text-danger">*</span>
                    {!! Form::select('gender', $genders , null, ['class' => 'form-control form-control-sm select2','placeholder' =>
                    'Seleccione uno...']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('date_birth', 'Fecha De Nacimiento') !!}<span class="text-danger">*</span>
                    {!! Form::text('date_birth', null, ['class' => 'form-control form-control-sm date']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('place_birth', 'Lugar De Nacimiento') !!}<span class="text-danger">*</span>
                    {!! Form::text('place_birth', null, ['class' => 'form-control ']) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('rh', 'RH') !!}<span class="text-danger">*</span>
                    {!! Form::select('rh', $blood_types, null, ['class' => 'form-control form-control-sm select2',
                    'placeholder'=>'Selecciona...']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('eps', 'Eps') !!}<span class="text-danger">*</span>
                    {!! Form::text('eps', null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('email', 'Correo Electrónico') !!}<span class="text-danger">*</span>
                    {!! Form::email('email', null, ['class' => 'form-control form-control-sm',
                    'autocomplete'=>'off']) !!}
                    <small class="form-text text-muted">Correo Electrónico de notificación</small>
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('address', 'Direccion') !!}<span class="text-danger">*</span>
                    {!! Form::text('address', null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('municipality', 'Municipio') !!}<span class="text-danger">*</span>
                    {!! Form::text('municipality', null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('neighborhood', 'Barrio') !!}<span class="text-danger">*</span>
                    {!! Form::text('neighborhood', null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('zone', 'Zona') !!}
                    {!! Form::text('zone', null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('commune', 'Comuna') !!}
                    {!! Form::text('commune', null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('phones', 'Teléfonos') !!}<span class="text-danger">*</span>
                    {!! Form::text('phones', null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('mobile', 'Movil') !!}<span class="text-danger">*</span>
                    {!! Form::text('mobile', null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('school', 'Instituto/Colegio/Escuela') !!}<span class="text-danger">*</span>
                    {!! Form::text('school', null, ['class' => 'form-control form-control-sm']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('degree', 'Grado') !!}<span class="text-danger">*</span>
                    {!! Form::text('degree', null, ['class' => 'form-control form-control-sm']) !!}

                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('position_field', 'Posición en el campo:') !!}<span class="text-danger">*</span>
                    {!! Form::select('position_field', $positions , null, ['class' => 'form-control form-control-sm select2','placeholder' =>
                    'Seleccione uno...']) !!}
                </div>
            </div>

            <div class="col-md-4 col-sm-12 col-xs-12">
                <div class="form-group">
                    {!! Form::label('dominant_profile', 'Perfil dominante:') !!}<span class="text-danger">*</span>
                    {!! Form::select('dominant_profile', $dominant_profile , null, ['class' => 'form-control form-control-sm select2','placeholder' =>
                    'Seleccione uno...']) !!}
                </div>
            </div>

        </div>

    </fieldset>
</section>
