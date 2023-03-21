<div class="row justify-content-center">

    <img src="{{ $profile->url_photo ?? asset('img/user.png') }}" class="rounded" alt="player" id="player-img" width="200" height="200">

    <div class="col-md-3 col-sm-12 col-xs-12">
        <div class="form-group">
            <label>Foto</label>
            <div class="custom-file">
                <input type="file" class="custom-file-input" id="file-upload" accept="image/png, image/jpeg" name="profile">
                <label class="custom-file-label" for="file-upload">Seleccionar...</label>
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-md-3">
        <div class="form-group">
            <label for="position">Cargo</label>
            {!! Form::select('position', $positions , null, ['class' => 'form-control form-control-sm select2','placeholder' => 'Seleccione uno...']) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="date_birth">Fecha De Nacimiento</label>
            {!! Form::text('date_birth', null, ['class' => 'form-control form-control-sm date', 'autocomplete'=>'off', 'id'=>'date_birth']) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="identification_document">Cédula</label>
            {!! Form::text('identification_document', null, ['class' => 'form-control form-control-sm', 'autocomplete'=>'off','id'=>'identification_document']) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="gender">Genero</label>
            {!! Form::select('gender', $genders , null, ['class' => 'form-control form-control-sm select2','placeholder' => 'Seleccione uno...']) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label for="address">Dirección</label>
            {!! Form::text('address', null, ['class' => 'form-control form-control-sm', 'autocomplete'=>'off','id'=>'address']) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="phone">Teléfono</label>
            {!! Form::text('phone', null, ['class' => 'form-control form-control-sm', 'autocomplete'=>'off','id'=>'phone']) !!}
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="mobile">Celular</label>
            {!! Form::text('mobile', null, ['class' => 'form-control form-control-sm', 'autocomplete'=>'off','id'=>'mobile']) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="studies">Estudios</label>
{{--            <textarea name="studies" id="studies" cols="30" rows="6" class="form-control form-control-sm"></textarea>--}}
            {!! Form::textarea('studies', null, ['class' => 'form-control form-control-sm','size'=>'3x5','id' => 'studies']) !!}
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="references">Referencias</label>
{{--            <textarea name="references" id="references" cols="30" rows="6" class="form-control form-control-sm"></textarea>--}}
            {!! Form::textarea('references', null, ['class' => 'form-control form-control-sm','size'=>'3x5', 'id' => 'references']) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="contacts">Contactos</label>
            {!! Form::textarea('contacts', null, ['class' => 'form-control form-control-sm','size'=>'3x5', 'id' => 'contacts']) !!}
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="experience">Experiencias</label>
            {!! Form::textarea('experience', null, ['class' => 'form-control form-control-sm','size'=>'3x5', 'id' => 'experience']) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="aptitude">Aptitudes</label>
            {!! Form::textarea('aptitude', null, ['class' => 'form-control form-control-sm','size'=>'3x5', 'id' => 'aptitude']) !!}
        </div>
    </div>



</div>
