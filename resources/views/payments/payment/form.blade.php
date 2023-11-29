<form action="#" class="form-horizontal form-material" id="form_payments">
    <div class="row form-body">
        <div class="col-md-4">
            <div class="form-group">
                <label for="training_group_id">Grupo De Entrenamiento</label>
                <span class="bar"></span>
                {{ html()->select('training_group_id', $training_groups, null)->attributes(['id'=>'training_group_id','class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label for="unique_code">Código Único</label>
                <span class="bar"></span>
                {!! html()->text('unique_code', null)->attributes(['class' => 'form-control form-control-sm','placeholder' => 'Ej: 20190000']) !!}
            </div>
        </div>

        <div class="col-md-2">
            <div class="form-group">
                <label for="unique_code">Categoría</label>
                <span class="bar"></span>
                {{ html()->select('category', $categories, null)->attributes(['id'=>'categories','class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
            </div>
        </div>

        <div class="col-md-4">
            <button class="btn waves-effect waves-light btn-rounded btn-info mt-4" id="busqueda">Buscar</button>
        </div>
    </div>
</form>
