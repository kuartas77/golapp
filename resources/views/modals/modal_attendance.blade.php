<div class="modal" id="modal_attendance" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form_attendance" class="form-material m-t-0">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title text-uppercase"><strong>Observaciones:</strong>&nbsp;<span id="player_name"></span></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <div class="row col-12 ">


                        <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                            <div class="form-group">
                                <label for="attendance_number">Entrenamiento #:</label>
                                <input id="attendance_number" name="attendance_number" type="text"
                                    class="form-control form-control-sm" autocomplete="off" readonly>
                                <input type="hidden" name="attendance_id" id="attendance_id">
                                <input type="hidden" name="attendance_day" id="attendance_day">
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                            <div class="form-group">
                                <label for="attendance_name">Día:</label>
                                <input id="attendance_name" name="attendance_name" type="text"
                                    class="form-control form-control-sm" autocomplete="off" readonly>
                            </div>
                        </div>

                        <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                            <div class="form-group">
                                <label for="attendance_date">Fecha:</label>
                                <input id="attendance_date" name="attendance_date" type="text"
                                    class="form-control form-control-sm" autocomplete="off" readonly>
                            </div>
                        </div>

                        {{--<div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                            <div class="form-group">
                                <label for="select_attendance">¡Selecciona!:</label>
                                {!! html()->select(null, $optionAssist, null)->attributes(['class' => "form-control form-control-sm", "id" => 'select_attendance', 'required'])->placeholder('Selecciona...') !!}
                            </div>
                        </div>--}}

                        <div class="col-md-12 col-sm-12 col-lg-12 col-xs-12">
                            <div class="form-group">
                                <label for="observation">Observaciónes para el deportista en el entrenamiento.</label>
                                <span class="bar"></span>
                                <textarea name="observations" id="single_observation" cols="30" rows="10" class="form-control form-control-sm"></textarea>
                            </div>
                        </div>

                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal">Cerrar
                    </button>
                    <button type="submit" class="btn btn-info waves-effect text-left" id="btn_attendance">Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>

</script>
@endpush