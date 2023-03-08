<div class="modal" id="import_players">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
                {!! Form::open(['route' => ['import.players'], 'method' => 'post','class' => 'form-material m-t-0', 'id'=>'form_file', 'files'=> true]) !!}
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">Agregar Deportistas En Excel</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">

                    <div class="col-lg-12">

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="school_id">Escuela</label>
                                    <span class="bar"></span>
                                    {!! Form::select('school_id', $schools , null, ['id'=>'school_id','class' => 'form-control form-control-sm','placeholder' =>'Seleccione uno...','required']) !!}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">Archivo</label>
                                    <span class="bar"></span>
                                    <input type="file" name="file" id="file" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-info waves-effect text-left">Guardar</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
schools
