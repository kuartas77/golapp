@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Agregar Deportista" :option="0"/>
    <x-row-card col-inside="10 col-sm-12 col-md-10" col-outside="1">
        {!! Form::open(['route' => 'players.store', 'id'=>'form_player', 'files'=>true, 'class'=>'form-material m-t-0']) !!}
        <div class="form-body">
            @include('player.fields.basic_information')
            @include('player.fields.family_information')
        </div>
        <div class="form-actions m-t-0 text-center">
            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info">Guardar</button>
        </div>
        {!! Form::close() !!}
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
                    rh : {},
                    eps : {required: true},
                    email : {required: false},
                    address : {required: true},
                    municipality : {required: true},
                    neighborhood : {required: true},
                    phones : {required: true},
                    school : {required: true},
                    degree : {required: false},
                    position_field : {},
                    dominant_profile : {},
                    "people[0][relationship]": {required: true},
                    "people[0][names]": {required: true},
                    "people[0][phone]": {
                        required: function () {
                            return $('input[name="people[0][mobile]"]').val() === "";
                        }
                    },
                    "people[0][identification_card]": {
                        required: function () {
                            return $('input[name="people[0][tutor]"]').is(":checked");
                        }, numbers:true
                    },
                },
                submitHandler: function (form) {
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
                            form.submit()
                        }
                    })
                }
            });
            
            events();
        });

        function events() {
            // campos los cuales se van a buscar en la tabla maestra para autocompletado
            let fields = ['school', 'place_birth', 'neighborhood', 'eps'];
            $.get(url_autocomplete, {fields: fields}, function (result) {
                $('#place_birth').typeahead({
                    source: result.place_birth,
                    theme: 'bootstrap4',
                    scrollHeight: 10
                });

                $('#school').typeahead({
                    source: result.school,
                    theme: 'bootstrap4',
                });

                $('#municipality').typeahead({
                    source: result.place_birth,
                    theme: 'bootstrap4',
                });

                $('#neighborhood').typeahead({
                    source: result.neighborhood,
                    theme: 'bootstrap4',
                });

                $('#eps').typeahead({
                    source: result.eps,
                    theme: 'bootstrap4',
                });

            });

            $('#date_birth').inputmask("yyyy-mm-dd");
            $(".form-control").attr('autocomplete', 'off');

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
