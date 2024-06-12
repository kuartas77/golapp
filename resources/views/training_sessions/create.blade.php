@extends('layouts.app')
@section('title', 'Sesion De Entrenamiento')
@section('content')
<x-bread-crumb title="Sesion De Entrenamiento" :option="0" />
<x-row-card col-inside="6" col-outside="3">
    <div class="wizard-content">
        <form action="{{route('training-sessions.store')}}" id="form_session" class="form-material m-t-0 validation-wizard wizard-circle" method="POST">
            @csrf
            @include('training_sessions.fields.header')

            @foreach($numberTasks as $task)

            @include('training_sessions.fields.task', ['task' => $task])

            @endforeach

            @include('training_sessions.fields.footer')
        </form>
    </div>

</x-row-card>
@endsection
@section('scripts')
<script>

// window.onbeforeunload = function() {
//     return "Es posible que los cambios no se guarden.";
// }
let form_session = $("#form_session");

$(document).ready(function () {
    form_session.validate({
        rules:{
            // training_group_id: {required: true},
            // period: {required: true, number: true},
            // session: {required: true, number: true},
            // date: {required: true},
            // hour: {required: true},
            // training_ground: {required: true},
            back_to_calm: {number: true},
            players: {number: true},
            'ts[]': {number: true},
            'sr[]': {maxlength: 15},
            'tt[]': {number: true},
        }
    });

    form_session.steps({
        headerTag: "h6",
        bodyTag: "section",
        transitionEffect: "fade",
        stepsOrientation: "horizontal",
        titleTemplate: '<span class="step">#index#</span> #title#',
        autoFocus: true,
        saveState: true,
        enableAllSteps: true,
        labels: {
            finish: "Guardar",
            next: "Siguiente",
            previous: "Anterior"
        },
        onInit: function(event, currentIndex){
            $('#date').inputmask("yyyy-mm-dd");
            $(".form-control").attr('autocomplete', 'off');
            $(".timepicker").bootstrapMaterialDatePicker({
                format: 'hh:mm A',
                shortTime: true,
                time: true,
                date: false,
                lang: 'es',
                clearButton: false,
                cancelText: 'Cancelar',
                okText: 'Aceptar',
            });
        }
        , onStepChanging: function (event, currentIndex, newIndex) {
            return currentIndex > newIndex || (currentIndex < newIndex &&
            (form_session.find(".body:eq(" + newIndex + ") label.error").remove(),
                form_session.find(".body:eq(" + newIndex + ") .error").removeClass("error")),
                form_session.validate().settings.ignore = ":disabled,:hidden", form_session.valid())
        }
        , onFinishing: function (event, currentIndex) {
            return form_session.validate().settings.ignore = ":disabled", form_session.valid()
        }
        , onFinished: function (event, currentIndex) {
            Swal.fire({
                title: 'Atención',
                text: "¿Guardar Sesión de entrenamiento?",
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
                    form_session.submit()
                }
            })
        }
    });
});
</script>
@endsection