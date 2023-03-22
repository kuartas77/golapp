<form action="#" class="form-horizontal form-material" id="form_assist">
    <div class="row form-body">

        <div class="col-md-4">
            <div class="form-group">
                <label for="training_group_id">Grupo De Entrenamiento</label>
                <span class="bar"></span>
                {!! Form::select('training_group_id', $training_groups, null,['id'=>'training_group_id','class' => 'form-control form-control-sm','placeholder' => 'Seleccionar...', 'required']) !!}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="month">Mes</label>
                <span class="bar"></span>
                {!! Form::select('month', $months, $actual_month,['id'=>'month','class' => 'form-control form-control-sm', 'id'=>'month']) !!}
            </div>
        </div>

        <div class="col-md-4">
            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info mt-4">Buscar</button>
            <button type="button" class="btn waves-effect waves-light btn-rounded btn-success mt-4" id="createAssist">Crear Asistencias</button>
        </div>

    </div>

</form>
