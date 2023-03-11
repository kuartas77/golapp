<div class="modal" id="create">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{route('competition_groups.store')}}" id="form_create" class="form-material m-t-0" method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Agregar Nuevo Grupo</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">

                    <div class="col-lg-12">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Nombre</label>(<span class="text-danger">*</span>)
                                    <span class="bar"></span>
                                    <input type="text" name="name" id="name" class="form-control" required
                                           autocomplete="off">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="user_id">Formador</label>(<span class="text-danger">*</span>)
                                    <span class="bar"></span>
                                    {!! Form::select('user_id', $users , null, ['id'=>'user_id','class' => 'form-control form-control-sm select2','placeholder' =>'Seleccione uno...','required']) !!}
                                </div>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="day_id">Torneo</label>(<span class="text-danger">*</span>)
                                    <span class="bar"></span>
                                    {!! Form::select('tournament_id', $tournaments , null, ['id'=>'tournament_id','class' => 'form-control form-control-sm select2','placeholder' =>'Seleccione uno...','required']) !!}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="year">Categoria</label>(<span class="text-danger">*</span>)
                                <span class="bar"></span>
                                <select name="year" id='year' class="form-control form-control-sm select2">
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
