optionsDateTimePicker.timePicker24Hour = false;
optionsDateTimePicker.locale.format = 'YYYY-MM-DD';
optionsDateTimePicker.timePicker = false;
optionsDateTimePicker.autoUpdateInput = false;

form_inscripcion = $("#form-inscripcion");

form_inscripcion.steps({
    headerTag: "h6",
    bodyTag: "section",
    transitionEffect: "fade",
    stepsOrientation: "vertical",
    titleTemplate: '<span class="step">#index#</span> #title#',
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
        (form_inscripcion.find(".body:eq(" + newIndex + ") label.error").remove(),
            form_inscripcion.find(".body:eq(" + newIndex + ") .error").removeClass("error")),
            form_inscripcion.validate().settings.ignore = ":disabled,:hidden", form_inscripcion.valid())
    }
    , onFinishing: function (event, currentIndex) {
        return form_inscripcion.validate().settings.ignore = ":disabled", form_inscripcion.valid()
    }
    , onFinished: function (event, currentIndex) {

        Swal.fire({
            title: '¿Deseas envíar un correo con el adjunto de la inscripción?',
            text: "¡Cualquiera de las opciones guardara los datos!",
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
                form_inscripcion.append('<input type="hidden" name="send" value="send" />');
                form_inscripcion.trigger('submit')
            } else {
                form_inscripcion.trigger('submit')
            }
        })
    }
});
jQuery.validator.addClassRules('chk-col-blue', {
    checkone:true
});

form_inscripcion.validate({
    errorElement: 'span',
    rules: {
        "people[0][relationship]":{required:true},
        "people[0][names]":{required:true},
        "people[0][phone]":{required:function(){
                return $('input[name="people[0][mobile]"]').val() === "";
            }},
        "people[0][mobile]":{required:function(){
                return $('input[name="people[0][phone]"]').val() === "";
            }},
        "people[0][identification_card]":{required:function(){
                return $('input[name="people[0][tutor]"]').is(":checked");
            }},

        "people[1][relationship]":{required:true},
        "people[1][names]":{required:true},
        "people[1][phone]":{required:function(){
                return $('input[name="people[1][mobile]"]').val() === "";
            }},
        "people[1][mobile]":{required:function(){
                return $('input[name="people[1][phone]"]').val() === "";
            }},
        "people[1][identification_card]":{required:function(){
                return $('input[name="people[1][tutor]"]').is(":checked");
            }},

        "people[2][relationship]":{required:true},
        "people[2][names]":{required:true},
        "people[2][phone]":{required:function(){
                return $('input[name="people[2][mobile]"]').val() === "";
            }},
        "people[2][mobile]":{required:function(){
                return $('input[name="people[2][phone]"]').val() === "";
            }},
        "people[2][identification_card]":{required:function(){
                return $('input[name="people[2][tutor]"]').is(":checked");
            }},
    }
});

$(document).ready(function () {
    $('.dropify').dropify({
        error: {
            'imageFormat': 'El formato de imagen no está permitido, solo (jpg,jpeg,png).'
        },
        messages: {
            'default': 'Arrastre y suelte un archivo aquí o haga clic',
            'replace': 'Arrastra y suelta o haz clic para reemplazar',
            'remove': 'Eliminar',
            'error': 'Vaya, sucedió algo mal.'
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

    $("#start_date").daterangepicker(optionsDateTimePicker).on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });

    $('#date_birth').daterangepicker(optionsDateTimePicker).on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
        let year = picker.startDate.format('YYYY');
        $.get(url_filter_training_group, {'year': year}, function (response) {
            $('#training_group_id').empty();
            $('#training_group_id').append(new Option("Seleccione...", ""));
            $.each(response, function (index, value) {
                $('#training_group_id').append(new Option(value, index));
            });
            $("#training_group_id").trigger('blur');
        });
    });

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

}


$("#training_group_id").on('change', function (e) {
    let training_group_id = e.target.value;
    $.get(url_availability_training_group + training_group_id, function (data) {
        $('#mensaje').empty();
        $('#conten-m').removeClass('hidden');
        $('#mensaje').append(data);
    });
});

$("#competition_group_id").on('change', function (e) {
    let grupo_id = e.target.value;
    $.get(url_availability_competition_groups + grupo_id, function (data) {
        $('#mensaje-c').empty();
        $('#conten-m-c').removeClass('ocultar');
        $('#mensaje-c').append(data);
    });
});
