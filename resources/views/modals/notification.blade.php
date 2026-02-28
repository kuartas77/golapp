<div class="modal" id="notification_modal" aria-hidden="true">
    <div class="modal-dialog modal-xl w-100">
        <div class="modal-content">
            <form action="#" id="form_notification_modal" class="form-material m-t-0" method="POST" aria-hidden="true">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="text-uppercase"><strong>Notificación</strong></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body m-l-20 m-r-20">

                    <span class="text-muted">
                        Las notificaciones se envían a unos tópicos a los cuales los usuarios de GOLAPPLINK se subscriben al momento de ingresar a
                        la App, estos tópicos se conforman por el nombre seguido de un guión y el nombre de la escuela.
                    </span>

                    <br>

                    <div class="row">

                        <div class="col-md-12 col-lg-4 col-xl-4">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notification_type">Notificación para:</label>(<span class="text-danger">*</span>)
                                    <span class="bar"></span>
                                    <select class="form-control form-control-sm" id="notification_type" name="notification_type">
                                        <option value="general">General "Todos los jugadores activos"</option>
                                        <option value="categories">Categorias</option>
                                        <option value="training_groups">Grupos de Entrenamiento</option>
                                        <option value="competition_groups">Grupos de Competencia</option>
                                        <!-- <option value="players">Jugadores</option> -->
                                    </select>

                                </div>
                            </div>

                            <div class="col-md-12 hide" id="container_categories">
                                <div class="form-group">
                                    <label for="categories">Categorias:</label>(<span class="text-danger">*</span>)
                                    <span class="bar"></span>
                                    <select class="form-control form-control-sm select2" id="categories" name="categories[]" multiple required>
                                        @foreach($topicCategories as $category)
                                        <option value="{{$category['topic']}}">{{$category['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12 hide" id="container_training_groups">
                                <div class="form-group">
                                    <label for="training_groups">Grupos de Entrenamiento:</label>(<span class="text-danger">*</span>)
                                    <span class="bar"></span>
                                    <select class="form-control form-control-sm select2" id="training_groups" name="training_groups[]" multiple required>
                                        @foreach($topicGroups as $group)
                                        <option value="{{$group['topic']}}">{{$group['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12 hide" id="container_competition_groups">
                                <div class="form-group">
                                    <label for="competition_groups">Grupos de Competencia:</label>(<span class="text-danger">*</span>)
                                    <span class="bar"></span>
                                    <select class="form-control form-control-sm select2" id="competition_groups" name="competition_groups[]" multiple required>
                                        @foreach($topicCompetitionGroups as $group)
                                        <option value="{{$group['topic'] }}">{{$group['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12 hide" id="container_players">
                                <div class="form-group">
                                    <label for="players">Jugadores:</label>(<span class="text-danger">*</span>)
                                    <span class="bar"></span>
                                    <select class="form-control form-control-sm select2" id="players" name="players[]" multiple required>
                                        @foreach($topicUniqueCodes as $player)
                                        <option value="{{$player['topic'] }}">{{$player['name']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-12 col-lg-8 col-xl-8">

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notification_title">Titulo:</label>(<span class="text-danger">*</span>)
                                    <span class="bar"></span>
                                    <input type="text" name="notification_title" id="notification_title"
                                        class="form-control form-control-sm" placeholder="Titulo" required>

                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notification_body">Mensaje:</label>(<span class="text-danger">*</span>)
                                    <span class="bar"></span>
                                    <textarea name="notification_body" id="notification_body" rows="5"
                                        class="form-control form-control-sm" placeholder="Mensaje" required></textarea>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger waves-effect text-left close_notification_modal" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-info waves-effect text-left" >Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    $('#notification_type').on('change', function(){
        const value = $(this).val()
        $('[id^="container_"]').addClass('hide');
        $('#container_' + value).removeClass('hide');
        $('#'+value).val(null).trigger('change');
    })

    $(".select2").select2({
        dropdownParent: $('#notification_modal'),
        placeholder: "Selecciona...",
        allowClear: true
    });

    $(".close_notification_modal").on('click', function(){
        $('#form_notification_modal').get(0).reset();
        $('#categories').val(null).trigger('change');
        $('#training_groups').val(null).trigger('change');
        $('#competition_groups').val(null).trigger('change');
        $('#players').val(null).trigger('change');

        $('#notification_type')[0].selectedIndex = 0;
        $('#notification_type').trigger('change');
    })

    $('#form_notification_modal').validate()
</script>
@endpush