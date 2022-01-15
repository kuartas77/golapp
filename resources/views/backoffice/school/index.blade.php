@extends('layouts.app')
@section('content')
<x-bread-crumb title="Escuelas" :option="0" />
<x-row-card col-inside="12">
    @include('backoffice.school.table')
</x-row-card>
@endsection
@section('modals')
@include('modals.school')
@endsection
@section('scripts')
<script>
    const url_current = "{{ URL::current() }}";
    const url = "{{route('config.datatables.schools')}}";
    const validateCheck = (value) => {
        return value ? '<span class="label label-success">SI</span>' : '<span class="label label-warning">NO</span>';
    }
    $(document).ready(function() {
        let table = $('#schools-table').DataTable({
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
            "order": [
                [6, 'desc'],
            ],
            "columns": [{
                    data: 'logo',
                    "render": function(data, type, row) {
                        return "<img class='media-object img-rounded' src='" + row.logo_file + "' width='60' height='60' alt='" + row.name + "'>";
                    }
                },
                {
                    data: 'name'
                },
                {
                    data: 'agent'
                },
                {
                    data: 'address'
                },
                {
                    data: 'phone'
                },
                {
                    data: 'email'
                },
                {
                    data: 'is_enable',
                    "render": function(data, type, row) {
                        return validateCheck(data);
                    }
                },
                {
                    data: 'created_at',
                    "render": function(data, type, row) {
                        return moment(data).format('DD-MM-YYYY');
                    }
                },
                {
                    data: 'id',
                    "render": function(data, type, row, meta) {
                        return '<form method="POST" action="' + row.url_destroy + '" accept-charset="UTF-8"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="' + row.token + '"><div class="btn-group">' +
                            '<a href="' + row.url_show + '" class="btn btn-default btn-xs"><i class="fas fa-eye"></i></a>' +
                            '<a href="javascript:void(0)" class="edit btn btn-default btn-xs" data-slug="' + row.slug + '"><i class="fas fa-pencil-alt"></i></a></div></form>'
                    }
                },
            ]
        });

        $("#form_create").validate({
            rules: {
                name: {
                    required: true
                },
                agent: {
                    required: true
                },
                email: {
                    emails: true
                },
                address: {
                    required: true
                },
                phone: {
                    required: true
                },
                is_enable: {
                    required: true
                },
            }, submitHandler: function (form) {
                var data = new FormData();
                let form_data = $(form).serializeArray();
                let url = $(form).attr('action');

                $.each(form_data, function (key, input) {
                    data.append(input.name, input.value);
                });

                let file_data = $('input[name="logo"]')[0].files;
                if(file_data.length){
                    data.append('logo', $('input[name="logo"]')[0].files[0]);
                }

                console.log(data);
                $.ajax({
                    url: url,
                    method: "post",
                    processData: false,
                    contentType: false,
                    data: data,
                    success: function (data) {
                        Swal.fire({
                            type: 'success',
                            title: window.app_name,
                            text: 'Se ha creado la Empresa.',
                        });
                        $('#form_create')[0].reset();
                    },
                    error: function (e) {
                        Swal.fire({
                            type: 'error',
                            title: window.app_name,
                            text: 'Algo salío mal!',
                        })
                    }
                });
                // Redraw data table, causes data to be reloaded
                table.clearPipeline().draw();
                $('#create').modal('hide');
            }
        });

        $(document).on('click', 'a.edit', function() {
            let id = $(this).data('slug');

            $.get(`${url_current}/${id}`, function({name, agent, email, address, phone, is_enable}){
                resetModalForm(false, id);
                $("#name").val(name).attr('readonly', true);
                $("#agent").val(agent);
                $("#email").val(email);
                $("#address").val(address);
                $("#phone").val(phone);
                $("#is_enable").val(is_enable ? 1 : 0);
                $("#create").modal('show');
            });
        });

        $("#btn-add").on('click', function() {
            $("#method").val('')
            $("#name").val('').attr('readonly', false);
            $("#agent").val('');
            $("#email").val('');
            $("#address").val('');
            $("#phone").val('');
            $("#is_enable").val(is_enable);
            resetModalForm(true, 0);
        });


    });

    const resetModalForm = (create = true, id) => {
        let form = $("#form_create");
        let title = $("#modal_title");
        if (create) {
            title.html("Agregar Nueva Escuela");
            form.prop("action", url_current)
            form.prop("method", 'POST');
        } else {
            title.html("Actualizar Escuela.");
            form.prop("action", `${url_current}/${id}`)
            form.append("<input name='_method' id='method' value='PUT' type='hidden'>");
        }
    }
</script>
@endsection