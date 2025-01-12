@extends('layouts.app')
@section('title', 'Horarios')
@section('content')
    <x-bread-crumb title="Horarios" :option="0"/>
    <x-row-card col-inside="12" >
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

        function forceKeyPressUppercase(e)
        {
            var charInput = e.keyCode;
            if((charInput >= 97) && (charInput <= 122)) { // lowercase
            if(!e.ctrlKey && !e.metaKey && !e.altKey) { // no modifier key
                var newChar = charInput - 32;
                var start = e.target.selectionStart;
                var end = e.target.selectionEnd;
                e.target.value = e.target.value.substring(0, start) + String.fromCharCode(newChar) + e.target.value.substring(end);
                e.target.setSelectionRange(start+1, start+1);
                e.preventDefault();
            }
            }
        }

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
                "scrollX": true,
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
                schedules: {required: true}
            }
        });

        $("#btn-add").on('click', function () {
            resetModalForm(true);
        })

        $('#active_table tbody').on('click', 'a.edit', function () {
            let id = $(this).data('id');
            $.get(`${url_current}${id}/edit`, function (response) {
                if (response.data != null) {
                    resetModalForm(false, id);
                    $('#schedule_id').val(id)
                    $('#schedule').val(response.data.schedule)
                    $("#create").modal('show');
                }
            });
        });

    });

    </script>
    <!-- <script src="{{asset('js/day_schedules.js')}}"></script> -->
@endsection
