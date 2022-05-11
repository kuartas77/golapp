@extends('layouts.app')
@section('title', 'Torneos')
@section('content')
<x-bread-crumb title="Torneos" :option="0" />
<x-row-card col-inside="12">
    @include('tournaments.table')
</x-row-card>
@endsection
@section('modals')
@include('modals.create_tournament')
@endsection
@section('scripts')
<script>
    let url_current = '{{URL::current()}}';

    $(document).ready(() => {
        $('#active_table').DataTable({
            "lengthMenu": [
                [15, 30, 50, 70, 100],
                [15, 30, 50, 70, 100]
            ],
            "processing": true,
            "serverSide": true,
            "ajax": $.fn.dataTable.pipeline({
                url: url_current,
                pages: 5 // number of pages to cache
            }),
            "columns": [{
                    data: 'name'
                },
                {
                    data: 'id',
                    "render": (data, type, row, meta) => {
                        return '<form method="POST" action="' + row.url_destroy + '" accept-charset="UTF-8"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="' + row.token + '"><div class="btn-group">' +
                            '<a href="javascript:void(0)" class="edit btn btn-default btn-xs" data-id="' + row.id + '" data-name="' + row.name + '"><i class="fas fa-pencil-alt"></i></a></div></form>'
                    }
                },
            ],
            "order": [
                [0, "desc"]
            ],
        });

        $("#form_create").validate({
            rules: {
                name: {
                    required: true
                }
            },
            submitHandler(form) {
                form.submit();
            }
        });

        $(document).on('click', 'a.edit', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            resetModalForm(false, id);
            $("#tournament_id").val(id);
            $("#name").val(name);
            $("#create").modal('show');
        });
    });

    $("#btn-add").on('click', function() {
        $("#method").val('')
        $("#tournament_id").val('');
        $("#name").val('');
        resetModalForm(true, 0);
    });

    const resetModalForm = (create = true, id) => {
        let form = $("#form_create");
        let title = $("#modal_title");
        if (create) {
            title.html("Agregar Nuevo Torneo");
            form.prop("action", url_current)
            form.prop("method", 'POST');
        } else {
            title.html("Actualizar Torneo.");
            form.prop("action", `${url_current}/${id}`)
            form.append("<input name='_method' id='method' value='PUT' type='hidden'>");
        }
    }
</script>
@endsection