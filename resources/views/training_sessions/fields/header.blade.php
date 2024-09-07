<h6>Información General</h6>
<section>
    <div class="row">
        <div class="col-md-12">
            <div class="row">

                <div class="col-md-6">
                    <div class="form-group form-group-sm">
                        <label for="training_group_id">Grupo de entrenamiento:</label>
                        <span class="bar"></span>
                        {{ html()->select('training_group_id', $training_groups, null)->attributes(['class' => 'form-control form-control-sm select2', 'id'=>'training_group_id'])->placeholder('Selecciona...') }}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group form-group-sm">
                        <label for="period">Periodo</label>
                        <span class="bar"></span>
                        {!! html()->text('period', null)->attributes(['class' => 'form-control form-control-sm', 'id' => 'period']) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group form-group-sm">
                        <label for="session">Sesión </label>
                        <span class="bar"></span>
                        {!! html()->text('session', null)->attributes(['class' => 'form-control form-control-sm', 'id' => 'session']) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="row">

                <div class="col-md-3">
                    <div class="form-group form-group-sm">
                        <label for="date">Fecha </label>
                        <span class="bar"></span>
                        {!! html()->text('date', null)->attributes(['class' => 'form-control form-control-sm', 'id' => 'date']) !!}
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group form-group-sm">
                        <label for="hour">Hora </label>
                        <span class="bar"></span>
                        {!! html()->text('hour', null)->attributes(['class' => 'form-control form-control-sm timepicker', 'id' => 'hour']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group form-group-sm">
                        <label for="training_ground">Lugar </label>
                        <span class="bar"></span>
                        {!! html()->text('training_ground', null)->attributes(['class' => 'form-control form-control-sm', 'id' => 'training_ground']) !!}
                    </div>
                </div>

            </div>


            <div class="row">
                <div class="col-md-12">
                    <div class="form-group form-group-sm">
                        <label for="material">Materiales Utilizados</label>
                        <span class="bar"></span>
                        {!! html()->textarea('material', null)->attributes(['class' => 'form-control form-control-sm', 'size'=>'3x5','id' => 'material']) !!}
                        <small class="form-text text-muted">Picas, Conos, Chinos, Balones, Mini, Port, Vallas, Aros, Petos, Otros...</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="form-group form-group-sm">
                        <label for="warm_up">Calentamiento</label>
                        <span class="bar"></span>
                        {!! html()->textarea('warm_up', null)->attributes(['class' => 'form-control form-control-sm','size'=>'3x5','id' => 'warm_up']) !!}
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>