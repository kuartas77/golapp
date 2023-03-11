<div class="modal" id="modal_search_member">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form_search" class="">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Busca el deportista por el Código, para agregarlo al
                        listado de competencia.</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="unique_code">Código Unico</label>(<span class="text-danger">*</span>)
                                <span class="bar"></span>
                                <input id="unique_code" name="unique_code" type="text" placeholder="Ingresa el código"
                                       class="form-control form-control-sm" autocomplete="off">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="btn_search">&nbsp;</label><br>
                                <button id="btn_search" class="btn btn-primary">Buscar</button>
                            </div>
                        </div>

                        <div class="col-md-8 hide" id="member_name_add">
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="name_deport">Nombre</label>
                                <input id="member_name" name="member_name" type="text"
                                       class="form-control form-control-sm" autocomplete="off" disabled="true" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="btn_search">&nbsp;</label><br>
                            <span class="text-muted">Se Agregará Al Inicio De La Tabla</span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal"
                            id="cancel_add">Cancelar
                    </button>
                    <button type="button" class="btn btn-info waves-effect text-left" id="accept_add" disabled>Agregar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
