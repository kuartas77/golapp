<form action="{{route('export.debtors.pdf')}}" id="form_report_debtors" class="form-horizontal form-material" method="GET" target="_blank">
    <div class="row form-body">
        <div class="col-md-2"></div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="year">Año</label>
                <span class="bar"></span>
                {{ html()->select('year', $years, null)->attributes(['id'=>'year','class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="training_group_id">Grupo De Entrenamiento</label>
                <span class="bar"></span>
                {{ html()->select('training_group_id', [], null)->attributes(['id'=>'training_group_id','class' => 'form-control form-control-sm'])->placeholder('Todos los grupos') }}
            </div>
        </div>

        <div class="col-md-4">
            <button class="btn waves-effect waves-light btn-rounded btn-info mt-4" id="busqueda">Exportar PDF</button>
        </div>
    </div>
</form>
