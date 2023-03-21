<div class="modal" id="create">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{route('days.store')}}" id="form_create" class="form-material" method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title text-center" id="modal_title">Agregar Días de Entrenamiento</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">

                    <div class="col-12">
                        <h5 class="help-block text-center">Selecciona el número de clases en la semana.</h5>

                        <div class="clearfix"></div>

                        <div class="row text-center">

                            <div class="col">
                                <div class="form-group">
                                    <input type="radio" name="class" class="with-gap radio-col-cyan" value="1" required id="class_one" checked>
                                    <label for="class_one">1</label>
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <input type="radio" name="class" class="with-gap radio-col-cyan" value="2" required id="class_two">
                                    <label for="class_two">2</label>
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <input type="radio" name="class" class="with-gap radio-col-cyan" value="3" required id="class_three" >
                                    <label for="class_three">3</label>
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <input type="radio" name="class" class="with-gap radio-col-cyan" value="4" required id="class_four" >
                                    <label for="class_four">4</label>
                                </div>
                            </div>

                            {{--<div class="col">
                                <div class="form-group">
                                    <input type="radio" name="class" class="with-gap radio-col-cyan" value="5" required id="class_five" >
                                    <label for="class_five">5</label>
                                </div>
                            </div>--}}

                        </div>


                        <div class="row text-center">

                            <div class="col">
                                <div class="form-group">
                                    <label for="day_one">Clase 1</label>
                                    <span class="bar"></span>
                                    {!! Form::select('day_one', $week , null, ['id'=>'day_one','class' => 'form-control form-control-sm select classes','placeholder' =>'Seleccione uno...','required']) !!}
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <label for="day_two">Clase 2</label>
                                    <span class="bar"></span>
                                    {!! Form::select('day_two', $week , null, ['id'=>'day_two','class' => 'form-control form-control-sm select classes','placeholder' =>'Seleccione uno...','required']) !!}
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <label for="day_three">Clase 3</label>
                                    <span class="bar"></span>
                                    {!! Form::select('day_three', $week , null, ['id'=>'day_three','class' => 'form-control form-control-sm select classes','placeholder' =>'Seleccione uno...','required']) !!}
                                </div>
                            </div>

                            <div class="col">
                                <div class="form-group">
                                    <label for="day_four">Clase 4</label>
                                    <span class="bar"></span>
                                    {!! Form::select('day_four', $week , null, ['id'=>'day_four','class' => 'form-control form-control-sm select classes','placeholder' =>'Seleccione uno...','required']) !!}
                                </div>
                            </div>

                            {{--<div class="col">
                                <div class="form-group">
                                    <label for="day_five">Clase 5</label>
                                    <span class="bar"></span>
                                    {!! Form::select('day_five', $week , null, ['id'=>'day_five','class' => 'form-control form-control-sm select classes','placeholder' =>'Seleccione uno...','required']) !!}
                                </div>
                            </div>--}}

                        </div>

                        <div class="row">
                            <h3 class="text-themecolor text-center">Cantidad De Horarios:  <span id="num_class"></span></h3>
                        </div>

                        <div class="row schedules">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="schedule[1]" class="text-themecolor">Horario 1</label>
                                    <span class="bar"></span>
                                    <input type="text" name="schedule[1][value]" class="form-control form-control-sm" onkeypress="forceKeyPressUppercase()">
                                    <span class="text-muted">Ej: 04:30 pm</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <button class="btn btn-outline-success" onclick="addSchedule()" type="button"><i class="fa fa-plus" aria-hidden="true"></i></button>
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
