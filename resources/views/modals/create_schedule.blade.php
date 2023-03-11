<div class="modal" id="create">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{route('schedules.store')}}" id="form_create" class="form-material" method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title text-center" id="modal_title">Agregar Horario de Entrenamiento</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">

                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="schedule">Horario</label><span class="text-danger">*</span>
                                    <span class="bar"></span>
                                    <input type="hidden" name="id" id="schedule_id">
                                    <input type="text" name="schedule" id="schedule" class="form-control" required
                                           autocomplete="off" onkeypress="forceKeyPressUppercase()">
                                    <span class="text-muted">Ej: 04:30PM - 05:30PM</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal">Cerrar
                    </button>
                    <button type="submit" class="btn btn-info waves-effect text-left">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
