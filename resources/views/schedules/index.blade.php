@extends('layouts.app')
@section('title', 'Horarios')
@section('content')
    <x-bread-crumb title="Horarios" :option="0"/>
    <x-row-card col-inside="8" col-outside="2">
        @include('schedules.table')
    </x-row-card >
@endsection
@section('modals')
    @include('modals.create_schedule')
@endsection
@section('scripts')
    <script>
        let url_current = "{{URL::current()}}/";
        let url_store = "{{route('schedules.store')}}";
        let url_enabled = "{{route('schedules.enabled')}}";

        $("#schedule_start").bootstrapMaterialDatePicker({
            format: 'hh:mm A',
            shortTime: true,
            time: true,
            date: false,
            lang: 'es',
            clearButton: false,
            cancelText: 'Cancelar',
            okText: 'Aceptar',
        });

        $("#schedule_end").bootstrapMaterialDatePicker({
            format: 'hh:mm A',
            shortTime: true,
            time: true,
            date: false,
            lang: 'es',
            clearButton: false,
            cancelText: 'Cancelar',
            okText: 'Aceptar',
        });




        const resetModalForm = (create = true, id = null) => {
            let form = $("#form_create");
            let title = $("#modal_title");
            if (create) {
                title.html("Agregar Horario");
                form.prop("action", url_store)
                form.prop("method", 'POST');
                form.append("<input name='_method' value='POST' type='hidden'>");
                $('#schedule_id').val('')
                $('#schedule').val('')
            } else {
                title.html("Actualizar Horario.");
                form.prop("action", url_current + id)
                form.append("<input name='_method' value='PUT' type='hidden'>");
            }
        }

        const confirmDelete = (element, event) => {
            event.preventDefault();
            const form = $(element).closest('form');
            Swal.fire({
                title: '¿Deseas Eliminar Esto',
                text: "¡Esto No Se Podrá Revertir!",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.value) {
                    form.submit();
                }
            });
        }

        // $("#schedule").inputmask({ mask: "hh:mm t - hh:mm t" });

        $(document).ready(() => {

            $('#active_table').DataTable({
                "lengthMenu": [[15, 30, 50, 70, 100], [15, 30, 50, 70, 100]],
                "processing": true,
                "serverSide": true,
                "ajax": $.fn.dataTable.pipeline({
                    url: url_enabled,
                    pages: 5 // number of pages to cache
                }),
                "columns": [
                    {data: 'schedule'},
                    {
                        data: 'id',
                        "render": (data, type, row, meta) => {
                            return '<form method="POST" action="' + row.url_destroy + '" accept-charset="UTF-8">' +
                                '<input name="_method" type="hidden" value="DELETE">' +
                                '<input name="_token" type="hidden" value="' + row.token + '">' +
                                '<div class="btn-group">' +
                                '<a href="javascript:void(0)" class="edit btn btn-default btn-xs" data-id="' + data + '">' +
                                '<i class="fas fa-pencil-alt"></i>' +
                                '</a>' +
                                '<button type="submit" class="btn btn-danger btn-xs" onclick="confirmDelete(this, event)"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>'+
                                '</div>' +
                                '</form>'

                        }
                    },
                ],
                "order": [[0, "desc"]],
            });

        $("#form_create").validate({
            rules: {
                schedule_start: {required: true},
                schedule_end: {required: true}
            }
        });

        $("#btn-add").on('click', function () {
            resetModalForm(true);
        })

        $('#active_table tbody').on('click', 'a.edit', function () {
            let id = $(this).data('id');
            $.get(`${url_current}${id}/edit`, function (response) {
                if (response.data != null) {
                    let schedule = response.data.schedule.split(" - ")
                    resetModalForm(false, id);
                    $('#schedule_id').val(id)
                    $('#schedule_start').val(schedule[0])
                    $('#schedule_end').val(schedule[1])
                    $("#create").modal('show');
                }
            });
        });

    });

    </script>
    <!-- <script src="{{asset('js/day_schedules.js')}}"></script> -->
@endsection
