<div class="modal" id="create">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{route('tournaments.store')}}" id="form_create" class="form-material m-t-0" method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Torneos</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">

                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre</label>
                                    <span class="bar"></span>
                                    <input type="hidden" name="id" id="tournament_id">
                                    <input type="text" name="name" id="name" class="form-control" required
                                           autocomplete="off">
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
