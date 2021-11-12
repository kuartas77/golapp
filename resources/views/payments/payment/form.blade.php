<form action="#" class="form-horizontal form-material" id="form_payments">

    <div class="row form-body">

        <div class="col-md-2"></div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="training_group_id">Grupo De Entrenamiento</label>
                <span class="bar"></span>
                {!! Form::select('training_group_id', $training_groups, null,['id'=>'training_group_id','class' => 'form-control form-control-sm','placeholder' => 'Seleccionar...']) !!}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="unique_code">Código Único</label>
                <span class="bar"></span>
                {!! Form::text('unique_code', null,['class' => 'form-control form-control-sm','placeholder' => 'Ej: 20190000']) !!}
            </div>
        </div>

        <div class="col-md-2"></div>

    </div>

    <div class="form-actions text-center">
        <button class="btn waves-effect waves-light btn-rounded btn-info" id="busqueda">Buscar
        </button>
    </div>
</form>
