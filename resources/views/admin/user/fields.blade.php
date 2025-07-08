<div class="form-body row m-l-20 m-r-20">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Nombre</label>
            <span class="bar"></span>
            {{ html()->text('name')->attributes(['class' => 'form-control', 'autocomplete' => 'off']) }}
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Correo</label>
            <span class="bar"></span>
            {{ html()->email('email')->attributes(['class' => 'form-control', 'autocomplete' => 'off']) }}
        </div>
    </div>
</div>
@hasanyrole(['super-admin', 'school'])
<div class="form-body row m-l-20 m-r-20">
    <div class="col-md-6">
        <div class="form-group">
            <label for="rol_id">Rol</label>
            <span class="bar"></span>
            {{ html()->select('rol_id', $roles, ($default ?? null))->attributes(['class' => 'form-control select', 'id' => 'rol_id', 'required'])->placeholder('Selecciona...') }}
        </div>
    </div>
</div>
@endhasanyrole
