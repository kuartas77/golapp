<form action="#" class="form-horizontal form-material" id="form_payments">
    <div class="row form-body">
        <div class="col-md-3">
            <div class="form-group">
                <label for="tournament_id">Torneos</label>
                <span class="bar"></span>
                {!! Form::select('tournament_id', $tournaments, null,['id'=>'tournament_id','class' => 'form-control form-control-sm','placeholder' => 'Seleccionar...', 'required']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="competition_group_id">Grupos De Competencia</label>
                <span class="bar"></span>
                {!! Form::select('competition_group_id', [], null,['id'=>'competition_group_id','class' => 'form-control form-control-sm','placeholder' => 'Seleccionar...', 'required']) !!}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                <label for="unique_code">Código Único</label>
                <span class="bar"></span>
                {!! Form::text('unique_code', null,['class' => 'form-control form-control-sm','placeholder' => 'Ej: 20190000']) !!}
            </div>
        </div>

        <div class="col-md-3">
            <button class="btn waves-effect waves-light btn-rounded btn-info mt-4" id="busqueda">Buscar</button>
            <button type="button" class="btn waves-effect waves-light btn-rounded btn-success mt-4" id="createTournamentPay">Crear Pagos</button>
        </div>
    </div>
</form>