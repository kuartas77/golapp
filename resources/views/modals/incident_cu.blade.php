<div class="modal" id="create">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{route('incidents.store')}}" id="form_create" class="form-material m-t-0"
                  method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Agregar Incidencia</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">

                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_incident_id">Formador</label>
                                    <span class="bar"></span>
                                    {{ html()->select('user_incident_id', $users, null)->attributes(['id'=>'user_incident_id','class' => 'form-control form-control-sm select2','required'])->placeholder('Selecciona...') }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="incidence">Titulo</label>
                                    <span class="bar"></span>
                                    <input type="text" name="incidence" id="incidence" class="form-control" required
                                           autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="schedule_id">Descripción</label>
                                    <span class="bar"></span>
                                    <textarea name="description" id="description" cols="30" rows="10" class="form-control" required></textarea>
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
