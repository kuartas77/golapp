@extends('layouts.app')
@section('title', 'Sesiones De Entrenamiento')
@section('content')
<x-bread-crumb title="Sesiones De Entrenamiento" :option="0" />
<x-row-card col-inside="12">
    @include('training_sessions.table')
</x-row-card>
@endsection
@section('modals')
    @include('modals.training_sessions')
@endsection
@section('scripts')
<script>
    const url_current = "{{ URL::current() }}";
    const url = "{{route('training_sessions.enabled')}}";
    let form_session = $("#form_session");

    $(document).ready(function() {
        let table = $('#active_table').DataTable({
            "lengthMenu": [
                [30, -1],
                [30, "Todos"]
            ],
            "processing": true,
            "serverSide": true,
            "ajax": $.fn.dataTable.pipeline({
                url: url,
                pages: 5 // number of pages to cache
            }),
            "columns": [
                { data: 'creator', name: 'creator' },
                { data: 'group', name: 'group'},
                { data: 'training_ground', name: 'training_ground' },
                { data: 'period', name: 'period' },
                { data: 'session', name: 'session' },
                { data: 'date', name: 'date' },
                { data: 'hour', name: 'hour' },
                { data: 'tasks', name: 'tasks' },
                { data: 'created_at', name: 'created_at' },
                { data: 'buttons', name: 'buttons' },
            ]
        });

        form_session.validate({
            rules:{
                training_group_id: {required: true},
                period: {required: true, number: true},
                session: {required: true, number: true},
                date: {required: true},
                hour: {required: true},
                training_ground: {required: true},
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