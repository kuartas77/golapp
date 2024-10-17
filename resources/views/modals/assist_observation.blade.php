<div class="modal" id="modal_observation">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form_observation" class="">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Observaciónes</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="observation">Observaciónes por fecha de entrenamiento</label>
                                <span class="bar"></span>
                                <input type="hidden" name="id_row" id="id_row">
                                <textarea name="observations" id="observations" cols="30" rows="10" class="form-control form-control-sm" readonly></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal">Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
