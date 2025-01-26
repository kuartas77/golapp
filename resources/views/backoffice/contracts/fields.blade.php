<div class="form-body row m-l-20 m-r-20">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Nombre</label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            {{ html()->text('name')->attributes(['class' => 'form-control', 'autocomplete' => 'off']) }}
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Escuela</label>(<span class="text-danger">*</span>)
            <span class="bar"></span>
            {{ html()->select('school_id', $admin_schools, null)->attributes(['id'=>'school_id','class' => 'form-control', 'required'])->placeholder('Selecciona...') }}
        </div>
    </div>
</div>

<div class="row">

    <div class="col-md-12 mb-2">
        <div class="form-group">
            <label for="parameters">Parametros</label>
            {{ html()->textarea('parameters', null)->attributes(['class' => 'form-control', 'readonly', 'rows'=>"4", ]) }}
            <small class="form-text text-muted">Despues de guardado el contrato se tomaran como parametros todos los valores que estén con este formato [NAME]</small>
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-12 mb-2">
        <div class="form-group">
            <label for="header">Encabezado</label>(<span class="text-danger">*</span>)
            {{ html()->textarea('header', null)->attributes(['class' => 'form-control']) }}
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-12 mb-2">
        <div class="form-group">
            <label for="body">Cuerpo</label>(<span class="text-danger">*</span>)
            {{ html()->textarea('body', null)->attributes(['class' => 'form-control']) }}
        </div>
    </div>

</div>

<div class="row">

    <div class="col-md-12 mb-2">
        <div class="form-group">
            <label for="footer">Pie de página</label>(<span class="text-danger">*</span>)
            {{ html()->textarea('footer', null)->attributes(['class' => 'form-control']) }}
        </div>
    </div>

</div>
