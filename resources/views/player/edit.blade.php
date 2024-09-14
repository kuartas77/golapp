@extends('layouts.app')
@section('content')
    <x-bread-crumb title="{{__('messages.player_title_edit', ['unique_code' => $player->unique_code])}}" :option="0"/>
    <x-row-card col-outside="2" col-inside="8">
    <div class="wizard-content">
        {{html()->modelForm($player, 'patch', route('players.update',[$player->unique_code]))->attributes(['id'=>'form_player', 'accept-charset' => 'UTF-8', 'enctype' => "multipart/form-data", 'class'=>'validation-wizard wizard-circle'])->open()}}
            @include('player.fields.basic_information')
            @include('player.fields.family_information', ['peoples'=> $player->people ?? [1]])
        {{ html()->closeModelForm() }}
    </div>
    </x-row-card>
@endsection
@section('scripts')
    <script>
        let url_document_exists = "{{ route('autocomplete.document_exists') }}";
        let url_verify_unique_code = "{{ route('autocomplete.verify_code') }}";
        let url_autocomplete = "{{ route('autocomplete.fields') }}";

        jQuery.validator.addClassRules('chk-col-blue', {
            checkone: true
        });

        form_player = $("#form_player");

        $(document).ready(function () {

            form_player.validate({
                rules: {
                    unique_code : {},
                    names : {required: true, maxlength:50},
                    last_names : {required: true, maxlength:50},
                    identification_document : {required: true},
                    document_type : {required: false, maxlength:50},
                    gender : {required: true},
                    date_birth : {required: true},
                    place_birth : {required: true, maxlength:50},
                    rh : {},
                    eps : {required: true, maxlength:50},
                    email : {required: true, maxlength:50},
                    address : {required: true, maxlength:50},
                    municipality : {required: true, maxlength:50},
                    neighborhood : {required: true, maxlength:50},
                    mobile : {required: true, maxlength:50},
                    school : {maxlength:50},
                    degree : {},
                    position_field : {},
                    dominant_profile : {},
                    medical_history : {},
                    jornada : {},
                    student_insurance : {},
                    "people[0][relationship]": {required: true, maxlength:50},
                    "people[0][names]": {required: true, maxlength:50},
                    "people[0][identification_card]":{required: true, numbers:true},
                    "people[0][mobile]": {required: true, maxlength:50},
                    "people[0][business]": {required: true, maxlength:50},
                    "people[0][profession]": {required: true, maxlength:50},
                    "people[0][email]": {required: true, emails:true, maxlength:50},
                },
            });

            form_player.steps({
                headerTag: "h6",
                bodyTag: "section",
                transitionEffect: "fade",
                stepsOrientation: "horizontal",
                titleTemplate: '<span class="step">#index#</span> #title#',
                transitionEffectSpeed: 300,
                autoFocus: true,
                enableAllSteps: true,
                saveState: true,
                labels: {
                    finish: "Guardar",
                    next: "Siguiente",
                    previous: "Anterior"
                }
                , onStepChanging: function (event, currentIndex, newIndex) {
                    return currentIndex > newIndex || (currentIndex < newIndex &&
                    (form_player.find(".body:eq(" + newIndex + ") label.error").remove(),
                        form_player.find(".body:eq(" + newIndex + ") .error").removeClass("error")),
                        form_player.validate().settings.ignore = ":disabled,:hidden", form_player.valid())
                }
                , onFinishing: function (event, currentIndex) {
                    return form_player.validate().settings.ignore = ":disabled", form_player.valid()
                }
                , onFinished: function (event, currentIndex) {

                    Swal.fire({
                        title: 'Atención',
                        text: "¿Guardar Deportista?",
                        type: 'warning',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí',
                        cancelButtonText: 'No'
                    }).then((result) => {
                        if (result?.value !== undefined) {
                            form_player.submit()
                        }
                    })
                }
            });

            events();
        });

        function events() {
            // campos los cuales se van a buscar en la tabla maestra para autocompletado
            // campos los cuales se van a buscar en la tabla maestra para autocompletado
            let fields = ['school', 'place_birth', 'neighborhood', 'eps'];
            $.get(url_autocomplete, {fields: fields}, function (result) {
                $('#place_birth').typeahead({
                    source: result.place_birth,
                    scrollBar: true
                });

                $('#school').typeahead({
                    source: result.school,
                    scrollBar: true
                });

                $('#municipality').typeahead({
                    source: result.place_birth,
                    scrollBar: true
                });

                $('#neighborhood').typeahead({
                    source: result.neighborhood,
                    scrollBar: true
                });

                $('#eps').typeahead({
                    source: result.eps,
                    scrollBar: true
                });
            });

            $('#date_birth').inputmask("yyyy-mm-dd");
            $('#email').inputmask('email');
            // $('#phones').inputmask("9999999999");
            $('#unique_code').inputmask("999999[9999]");
            $('#identification_document').inputmask("999999[9999]");
            $(".form-control").attr('autocomplete', 'off');

            $("#unique_code").on('keyup', function () {
                let element = $(this);
                if (element.val().length >= 8) {
                    $.get(url_verify_unique_code, {'unique_code': element.val()}, function (response) {
                        if (response.data === true) {
                            element.val('').focus();
                            Swal.fire({
                                title: 'Atención',
                                text: "Este Código Ya Se Encuentra Registrado Con Otro Deportista.",
                                type: 'info'
                            });
                        }
                    });
                }
            });

            $("#identification_document").on('keyup', function () {
                let element = $(this);
                if (element.val().length >= 8) {
                    $.get(url_document_exists, {'doc': element.val()}, function (response) {
                        if (response.data === true) {
                            element.val('').focus();
                            Swal.fire({
                                title: 'Atención',
                                text: "Este Número de Documento ya se encuentra registrado con otro deportista.",
                                type: 'info'
                            });
                        }
                    });
                }
            });

            $('#file-upload').on('change', function(){
                readFile(this);
            });
        }

        function readFile(input) {
            let label = $(input).next('label.custom-file-label')
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    $('#player-img').attr('src', e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
                // label.empty().html(input.files[0].name)
                label.empty().html('Seleccionada.')
            }else{
                label.empty().html("Seleccionar...")
                $('#player-img').attr('src', 'http://golapp.local/img/user.png');
            }
        }
    </script>
@endsection
