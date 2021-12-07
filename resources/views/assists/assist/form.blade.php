<form action="#" class="form-horizontal form-material" id="form_assist">

    <div class="row form-body">

        <div class="col-md-1"></div>

        <div class="col-md-5">
            <div class="form-group">
                <label for="training_group_id">Grupo De Entrenamiento</label>
                <span class="bar"></span>
                {!! Form::select('training_group_id', $training_groups, null,['id'=>'training_group_id','class' => 'form-control form-control-sm','placeholder' => 'Seleccionar...', 'required']) !!}
            </div>
        </div>

        <div class="col-md-5">
            <div class="form-group">
                <label for="month">Mes</label>
                <span class="bar"></span>
                {!! Form::select('month', $months, null,['id'=>'month','class' => 'form-control form-control-sm', 'id'=>'month']) !!}
            </div>
        </div>

        <div class="col-md-1"></div>

    </div>

    <div class="form-actions text-center">
        <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info">Buscar</button>
        <button type="button" class="btn waves-effect waves-light btn-rounded btn-success" id="createAssist">Crear
            Asistencias
        </button>
    </div>
</form>
