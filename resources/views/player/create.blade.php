@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Agregar Deportista" :option="0"/>
    <x-row-card-eight>
        <div class="wizard-content">
            {!! Form::open(['route' => 'players.store', 'id'=>'form_player', 'files'=>true, 'class'=>'validation-wizard wizard-circle']) !!}
                @include('player.fields.basic_information')
                @include('player.fields.family_information', ['people'=> [1,2,3]])
            {!! Form::close() !!}
        </div>
    </x-row-card-eight>
@endsection
@section('scripts')
    <script>
        let url_document_exists = "{{ route('autocomplete.document_exists') }}";
        let url_verify_unique_code = "{{ route('autocomplete.verify_code') }}";
        let url_autocomplete = "{{ route('autocomplete.fields') }}";

        optionsDateTimePicker.timePicker24Hour = false;
        optionsDateTimePicker.locale.format = 'YYYY-MM-DD';
        optionsDateTimePicker.timePicker = false;
        optionsDateTimePicker.autoUpdateInput = false;
        jQuery.validator.addClassRules('chk-col-blue', {
            checkone: true
        });

        const form_player = $("#form_player");

        $(document).ready(function () {

            form_player.validate({
                rules: {
                    unique_code : {required: true},
                    names : {required: true},
                    last_names : {required: true},
                    identification_document : {required: true},
                    gender : {required: true},
                    date_birth : {required: true},
                    place_birth : {required: true},
                    rh : {required: true},
                    eps : {required: true},
                    email : {required: true},
                    address : {required: true},
                    municipality : {required: true},
                    neighborhood : {required: true},
                    zone : {},
                    commune : {},
                    phones : {required: true},
                    mobile : {required: true},
                    school : {required: true},
                    degree : {required: true},
                    position_field : {required: true},
                    dominant_profile : {required: true},
                    "people[0][relationship]": {required: true},
                    "people[0][names]": {required: true},
                    "people[0][phone]": {
                        required: function () {
                            return $('input[name="people[0][mobile]"]').val() === "";
                        }
                    },
                    "people[0][mobile]": {
                        required: function () {
                            return $('input[name="people[0][phone]"]').val() === "";
                        }
                    },
                    "people[0][identification_card]": {
                        required: function () {
                            return $('input[name="people[0][tutor]"]').is(":checked");
                        }, numbers:true
                    },
            
                    "people[1][relationship]": {required: true},
                    "people[1][names]": {required: true},
                    "people[1][phone]": {
                        required: function () {
                            return $('input[name="people[1][mobile]"]').val() === "";
                        }
                    },
                    "people[1][mobile]": {
                        required: function () {
                            return $('input[name="people[1][phone]"]').val() === "";
                        }
                    },
                    "people[1][identification_card]": {
                        required: function () {
                            return $('input[name="people[1][tutor]"]').is(":checked");
                        }, numbers:true
                    },
                    "people[2][relationship]": {required: true},
                    "people[2][names]": {required: true},
                    "people[2][phone]": {
                        required: function () {
                            return $('input[name="people[2][mobile]"]').val() === "";
                        }
                    },
                    "people[2][mobile]": {
                        required: function () {
                            return $('input[name="people[2][phone]"]').val() === "";
                        }
                    },
                    "people[2][identification_card]": {
                        required: function () {
                            return $('input[name="people[2][tutor]"]').is(":checked");
                        }, numbers:true
                    },
                }
            });

            form_player.steps({
                headerTag: "h6",
                bodyTag: "section",
                transitionEffect: "fade",
                stepsOrientation: "horizontal",
                titleTemplate: '<span class="step">#index#</span> #title#',
                autoFocus: true,
                enableAllSteps: true,
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
                            form_player.trigger('submit')
                        }
                    })
                }
            }); 
            
            events();
        });

        function events() {
            // campos los cuales se van a buscar en la tabla maestra para autocompletado
            campos = ['colegio_escuela', 'lugar_nacimiento', 'barrio', 'eps', 'zona', 'comuna', 'grado'];
            $.get(url_autocomplete, {fields: campos}, function (result) {
                $('#place_birth').typeahead({
                    source: result.lugar_nacimiento,
                    scrollBar: true
                });

                $('#school').typeahead({
                    source: result.colegio_escuela,
                    scrollBar: true
                });


                $('#municipality').typeahead({
                    source: result.lugar_nacimiento,
                    scrollBar: true
                });

                $('#neighborhood').typeahead({
                    source: result.barrio,
                    scrollBar: true
                });

                $('#eps').typeahead({
                    source: result.eps,
                    scrollBar: true
                });

                $('#zone').typeahead({
                    source: result.zona,
                    scrollBar: true
                });

                $('#commune').typeahead({
                    source: result.comuna,
                    scrollBar: true
                });

                $('#degree').typeahead({
                    source: result.grado,
                    scrollBar: true
                });
            });

            $(".select2").select2({allowClear: true});
            $('.date').inputmask("yyyy-mm-dd");
            $(".form-control").attr('autocomplete', 'off');

            $('#date_birth').daterangepicker(optionsDateTimePicker).on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });

            $("#unique_code").on('keyup', function () {
                let element = $(this);
                if (element.val().length >= 7) {
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
