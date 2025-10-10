<form action="#" class="form-horizontal form-material" id="form_assist">
    <div class="row form-body">

        <div class="col-md-6">
            <div class="form-group">
                <label for="training_group_id">Grupo De Entrenamiento</label>
                <span class="bar"></span>
                {{ html()->select('training_group_id', $training_groups, null)->attributes(['id'=>'training_group_id','class' => 'form-control form-control-sm', 'required'])->placeholder('Selecciona...') }}
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="month">Mes</label>
                <span class="bar"></span>
                {{ html()->select('month', $months, $actual_month)->attributes(['id'=>'month','class' => 'form-control form-control-sm', 'id'=>'month'])->placeholder('Selecciona...') }}
            </div>
        </div>

        <div class="col-md-12">
            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info mt-4">Buscar</button>
            <button type="button" class="btn waves-effect waves-light btn-rounded btn-success mt-4" id="createAssist">Crear Asistencias</button>
        </div>

    </div>

</form>