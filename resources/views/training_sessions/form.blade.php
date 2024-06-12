<div class="row">
    <div class="col-md-6">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group form-group-sm">
                    <label for="pariod">Periodo</label>
                    <span class="bar"></span>
                    <input type="text" name="pariod" id="pariod" class="form-control" required autocomplete="off" value="">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group form-group-sm">
                    <label for="session">Sesión </label>
                    <span class="bar"></span>
                    <input type="text" name="session" id="session" class="form-control" required autocomplete="off" value="">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group form-group-sm">
                    <label for="training_ground">Lugar </label>
                    <span class="bar"></span>
                    <input type="text" name="training_ground" id="training_ground" class="form-control" required autocomplete="off" value="">
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group form-group-sm">
                    <label for="date">Fecha </label>
                    <span class="bar"></span>
                    <input type="text" name="date" id="date" class="form-control" required autocomplete="off" value="">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group form-group-sm">
                    <label for="hour">Hora </label>
                    <span class="bar"></span>
                    <input type="text" name="hour" id="hour" class="form-control" required autocomplete="off" value="">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group form-group-sm">
                    <label for="material">Materiales</label>
                    <span class="bar"></span>
                    <textarea name="material" id="material" rows="3" class="form-control form-control-sm"></textarea>
                </div>
            </div>
        </div>
    </div>

</div>


<div class="row">
    <div class="col-md-6 col-sm-12 col-lg-6">
        @include('training_sessions.task', ['task' => 1])
    </div>
    <div class="col-md-6 col-sm-12 col-lg-6">
        @include('training_sessions.task', ['task' => 2])
    </div>
    <div class="col-md-6 col-sm-12 col-lg-6">
        @include('training_sessions.task', ['task' => 3])
    </div>

    <div class="col-md-6 col-sm-12 col-lg-6">
        <fieldset class="border p-2">
            <legend class="w-auto">----</legend>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group form-group-sm">
                        <label for="material">Vuelta a la calma</label>
                        <span class="bar"></span>
                        <input type="text" name="pariod" id="pariod" class="form-control" required autocomplete="off" value="">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group form-group-sm">
                        <label for="material">N° Jugadores</label>
                        <span class="bar"></span>
                        <input type="text" name="pariod" id="pariod" class="form-control" required autocomplete="off" value="">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group form-group-sm">
                        <label for="material">Ausencias</label>
                        <span class="bar"></span>
                        <textarea name="material" id="material" rows="2" class="form-control form-control-sm"></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group form-group-sm">
                        <label for="material">Incidencias</label>
                        <span class="bar"></span>
                        <textarea name="material" id="material" rows="2" class="form-control form-control-sm"></textarea>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group form-group-sm">
                        <label for="material">Retro alimentación</label>
                        <span class="bar"></span>
                        <textarea name="material" id="material" rows="3" class="form-control form-control-sm"></textarea>
                    </div>
                </div>
            </div>

    </div>
    </fieldset>
</div>