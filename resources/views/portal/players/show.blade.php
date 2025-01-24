@extends('layouts.portal.public')
@section('title', __('messages.player_title', ['unique_code'=> $player->unique_code]))
@section('content')
<div class="container-fluid">
    <div class="row">
        @include('portal.players.show.card_person')
        @include('portal.players.show.card_info')
        @include('portal.players.show.card_inscriptions')
    </div>
</div>

<div class="modal" id="modal_update_player">
    <div class="modal-dialog modal-xl mw-80 w-100">
        <div class="wizard-content">
            <div class="modal-content">
                <div class="modal-body">
                    {{html()->modelForm(auth()->user(), 'put', route('portal.player.update', [auth()->user()->unique_code]))->attributes(['id' => 'form_update_player', 'accept-charset' => 'UTF-8', 'enctype' => "multipart/form-data", 'class' => 'validation-wizard wizard-circle'])->open()}}

                    @include('portal.inscriptions.fields.step_1')
                    @include('portal.inscriptions.fields.step_2')

                    {{ html()->closeModelForm() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
    let imgUser = "{{asset('img/user.png')}}";
    let url_autocomplete = "{{ route('portal.autocomplete.fields') }}";
    const form_update_player = $("#form_update_player");
    const MinDateBirth = moment().subtract(18, 'year'); //TODO: settings
    const MaxDateBirth = moment().subtract(4, 'year'); //TODO: settings

    $(function() {
        $(".preloader").fadeOut()
        let inputEmail = $("#email");
        let email = inputEmail.val()
        inputEmail.val(email.toLowerCase().trim())
    })

    form_update_player.validate({
        rules: {
            names : {required: true, maxlength:50},
            last_names : {required: true, maxlength:50},
            date_birth : {required: true, dateLessThan: MaxDateBirth.format('YYYY-MM-DD'), dateGreaterThan: MinDateBirth.format('YYYY-MM-DD')},
            place_birth : {required: true, maxlength:50},
            identification_document : {required: true, maxlength:50},
            document_type : {required: true},
            gender : {required: true},
            email : {required: true, emails:true},
            mobile : {required: true, maxlength:50},
            medical_history : {required: false, maxlength:200},

            address: {required: true, maxlength:50},
            municipality: {required: true, maxlength:50},
            neighborhood: {required: true, maxlength:50},
            rh: {required: true},
            eps: {required: true, maxlength:50},
            student_insurance: { maxlength:50},
            school: {required: false, maxlength:50},
            degree: {required: (element) => $("#school").val().length > 0 },
            jornada: {required: (element) => $("#school").val().length > 0},
        },
    })

    form_update_player.steps({
        headerTag: "h6",
        bodyTag: "section",
        transitionEffect: "fade",
        stepsOrientation: "horizontal",
        titleTemplate: '<span class="step">#index#</span> #title#',
        autoFocus: true,
        enableAllSteps: true,
        enableCancelButton: true,
        labels: {
            finish: "Guardar",
            next: "Siguiente",
            previous: "Anterior",
            cancel: "Cancelar"
        },
        onInit: function(event, currentIndex){
            $('.date').inputmask("yyyy-mm-dd");
            $(".form-control").attr('autocomplete', 'off');
            events()
            $("#date_birth").bootstrapMaterialDatePicker({
                time: false,
                clearButton: false,
                lang: 'es',
                cancelText: 'Cancelar',
                okText: 'Aceptar',
                minDate: MinDateBirth,//TODO: settings
                maxDate: MaxDateBirth// TODO: settings
            });
            $('#file-upload').on('change', function(){
                readFile(this);
            });
        },
        onStepChanging: function(event, currentIndex, newIndex) {
            return currentIndex > newIndex || (currentIndex < newIndex &&
                (form_update_player.find(".body:eq(" + newIndex + ") label.error").remove(),
                    form_update_player.find(".body:eq(" + newIndex + ") .error").removeClass("error")),
                form_update_player.validate().settings.ignore = ":disabled,:hidden", form_update_player.valid())
        },
        onFinishing: function(event, currentIndex) {
            return form_update_player.validate().settings.ignore = ":disabled", form_update_player.valid()
        },
        onFinished: function(event, currentIndex) {

            Swal.fire({
                title: window.app_name,
                text: '¿Deseas envíar el formulario y actualizar la ficha del deportista?',
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
                    form_update_player.submit();
                }
            })
        },
        onCanceled: function (event) {
            $('#modal_update_player').modal('hide');
        }
    });

    function events() {
            // campos los cuales se van a buscar en la tabla maestra para autocompletado
        let fields = ['colegio_escuela', 'lugar_nacimiento', 'barrio', 'eps', 'zona', 'comuna', 'grado'];
        $.get(url_autocomplete, {fields: fields}, function (result) {
            $('#place_birth').typeahead({
                source: result.lugar_nacimiento,
                scrollBar: true,
                appendTo: "#modal_update_player"
            });

            $('#school').typeahead({
                source: result.colegio_escuela,
                scrollBar: true,
                appendTo: "#modal_update_player"
            });

            $('#municipality').typeahead({
                source: result.lugar_nacimiento,
                scrollBar: true,
                appendTo: "#modal_update_player"
            });

            $('#neighborhood').typeahead({
                source: result.barrio,
                scrollBar: true,
                appendTo: "#modal_update_player"
            });

            $('#eps').typeahead({
                source: result.eps,
                scrollBar: true,
                appendTo: "#modal_update_player"
            });
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