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
            <input id="date_birth" name="date_birth" type="text" placeholder="Fecha De Nacimiento"
                   class="form-control form-control-sm date" autocomplete="off">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="identification_document">Cédula</label>
            <input id="identification_document" name="identification_document" type="text" placeholder="Cédula"
                   class="form-control form-control-sm" autocomplete="off">
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
            <input id="address" name="address" type="text" placeholder="Dirección"
                   class="form-control form-control-sm" autocomplete="off">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="phone">Teléfono</label>
            <input id="phone" name="phone" type="text" placeholder="Teléfono"
                   class="form-control form-control-sm" autocomplete="off">
        </div>
    </div>

    <div class="col-md-3">
        <div class="form-group">
            <label for="mobile">Celular</label>
            <input id="mobile" name="mobile" type="text" placeholder="Celular"
                   class="form-control form-control-sm" autocomplete="off">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="studies">Estudios</label>
            <textarea name="studies" id="studies" cols="30" rows="6" class="form-control form-control-sm"></textarea>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="references">Referencias</label>
            <textarea name="references" id="references" cols="30" rows="6" class="form-control form-control-sm"></textarea>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="contacts">Contactos</label>
            <textarea name="contacts" id="contacts" cols="30" rows="6" class="form-control form-control-sm"></textarea>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="experience">Experiencias</label>
            <textarea name="experience" id="experience" cols="30" rows="6" class="form-control form-control-sm"></textarea>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="aptitude">Aptitudes</label>
            <textarea name="aptitude" id="aptitude" cols="30" rows="6" class="form-control form-control-sm"></textarea>
        </div>
    </div>
</div>
