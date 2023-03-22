<div class="modal" id="create">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{route('training_groups.store')}}" id="form_create" class="form-material m-t-0" method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Agregar Nuevo Grupo</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">

                    <div class="col-lg-12">
                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name">Nombre del grupo</label>(<span class="text-danger">*</span>)
                                    <span class="bar"></span>
                                    <input type="text" name="name" id="name" class="form-control" required
                                           autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="stage">Lugar de entrenamiento</label>
                                    <span class="bar"></span>
                                    <input type="text" name="stage" id="stage" class="form-control" required
                                           autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <label for="days">Días</label>(<span class="text-danger">*</span>)
                                <span class="bar"></span>
                                <select name="days[]" id='days' multiple='multiple'>
                                    @foreach($days as $key => $value)
                                        <option value='{{$key}}'>{{$value}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="schedules">Horarios</label>(<span class="text-danger">*</span>)
                                <span class="bar"></span>
                                <select name="schedules[]" id='schedules' multiple='multiple'>
                                    @foreach($schedules as $key => $value)
                                        <option value='{{$key}}'>{{$value}}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <br>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="user_id">Formador(es)</label>(<span class="text-danger">*</span>)
                                <span class="bar"></span>
                                <select name="user_id[]" id='user_id' multiple='multiple'>
                                    @foreach($users as $key => $value)
                                        <option value='{{$key}}'>{{$value}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="years">Categoria(s)</label>(<span class="text-danger">*</span>)
                                <span class="bar"></span>
                                <select name="years[]" id='years' multiple='multiple'>
                                    @for($i = now()->subYears(18)->year;$i <= now()->subYears(2)->year ; $i++ )
                                        <option value='{{$i}}'>{{$i}}</option>
                                    @endfor
                                </select>
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
