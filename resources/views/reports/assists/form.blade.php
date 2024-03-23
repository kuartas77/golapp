<form action="{{route('reports.assists.report')}}" id="form_report_assists" class="form-horizontal form-material" method="POST">
                @csrf
    <div class="row form-body">
        <div class="col-md-2"></div>
        <div class="col-md-2">
            <div class="form-group">
                <label for="unique_code">Año</label>
                <span class="bar"></span>
                {{ html()->select('year', $years, null)->attributes(['id'=>'year','class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                <label for="training_group_id">Grupo De Entrenamiento</label>
                <span class="bar"></span>
                {{ html()->select('training_group_id', [], null)->attributes(['id'=>'training_group_id','class' => 'form-control form-control-sm'])->placeholder('Selecciona...') }}
            </div>
        </div>

        <div class="col-md-4">
            <button class="btn waves-effect waves-light btn-rounded btn-info mt-4" id="busqueda">Exportar</button>
        </div>
    </div>
</form>