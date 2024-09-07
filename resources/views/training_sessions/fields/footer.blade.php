<h6>Información General</h6>
<section>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group form-group-sm">
                <label for="back_to_calm">Vuelta a la calma</label>
                <span class="bar"></span>
                {!! html()->text('back_to_calm', null)->attributes(['class' => 'form-control form-control-sm', 'id' => 'back_to_calm', 'placeholder' => '5']) !!}
                <small class="form-text text-muted">Minutos</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group form-group-sm">
                <label for="players">N° Jugadores</label>
                <span class="bar"></span>
                {!! html()->text('players', null)->attributes(['class' => 'form-control form-control-sm', 'id' => 'players']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group form-group-sm">
                <label for="absences">Ausencias</label>
                <span class="bar"></span>
                {!! html()->textarea('absences', null)->attributes(['class' => 'form-control form-control-sm','size'=>'3x5','id' => 'absences']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group form-group-sm">
                <label for="incidents">Incidencias</label>
                <span class="bar"></span>
                {!! html()->textarea('incidents', null)->attributes(['class' => 'form-control form-control-sm','size'=>'3x5','id' => 'incidents']) !!}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group form-group-sm">
                <label for="feedback">Retro alimentación</label>
                <span class="bar"></span>
                {!! html()->textarea('feedback', null)->attributes(['class' => 'form-control form-control-sm','size'=>'3x5','id' => 'feedback']) !!}
                <small class="form-text text-muted">Feedback, estiramientos, relajación muscular, hidratación, etc...</small>
            </div>
        </div>
    </div>
</section>