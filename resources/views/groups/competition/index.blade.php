@extends('layouts.app')
@section('title', 'Grupos Competencia')
@section('content')
    <x-bread-crumb title="Grupos Competencia" :option="0"/>
    <x-row-card col-inside="12" >
        @include('groups.competition.table')
    </x-row-card >
@endsection
@section('modals')
    @include('modals.create_competition_groups')
@endsection
@section('scripts')
    <script defer>
        let url_current = '{{URL::current()}}/';
        let url_enabled = "{{route('competition_groups.enabled')}}";
        let url_disabled = "{{route('competition_groups.retired')}}";

        $(document).ready(function () {
            $(".select2").select2();

            $('#active_table').DataTable({
                "lengthMenu": [[15, 30, 50, 70, 100], [15, 30, 50, 70, 100]],
                "processing": true,
                "serverSide": true,
                "ajax": $.fn.dataTable.pipeline({
                    url: url_enabled,
                    pages: 5 // number of pages to cache
                }),
                "columns": [
                    {data: 'full_name_group'},
                    {data: 'professor.name'},
                    {
                        data: 'id',
                        "render": function (data, type, row, meta) {
                            return '<form method="POST" action="' + row.url_destroy + '" accept-charset="UTF-8"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="' + row.token + '"><div class="btn-group">'
                                //+'<a href="' + row.url_show + '" class="btn btn-default btn-xs"><i class="fas fa-eye"></i></a>'
                                + '<a href="javascript:void(0)" class="edit btn btn-default btn-xs" data-id="' + row.id + '"><i class="fas fa-pencil-alt"></i></a></div></form>'
                        }
                    },
                ],
                "order": [[0, "desc"]],
            });

            $('#disabled_table').DataTable({
                "lengthMenu": [[15, 30, 50, 70, 100], [15, 30, 50, 70, 100]],
                "processing": true,
                "serverSide": true,
                "ajax": $.fn.dataTable.pipeline({
                    url: url_disabled,
                    pages: 5 // number of pages to cache
                }),
                "columns": [
                    {data: 'full_name_group'},
                    {data: 'professor.name'},
                ],
                "order": [[0, "desc"]]
            });

            $("#form_create").validate({
                rules: {
                    name: {required: true},
                    user_id: {required: true},
                    tournament_id: {required: true},
                    year: {required: true}
                }, submitHandler(form) {
                    form.submit();
                }
            });

            $('#active_table tbody').on('click', 'a.edit', function () {
                let id = $(this).data('id');
                $.get(url_current + id + '/edit', function (response) {
                    if (response.data != null) {
                        resetModalForm(false, id);

                        $("#name").val(response.data.name)
                        $("#user_id").val(response.data.user_id).trigger('change');
                        $("#year").val(response.data.year).trigger('change');
                        $("#tournament_id").val(response.data.tournament_id).trigger('change');
                        $("#create").modal('show');
                    }
                });
            });
        });

        $(".btn-create").on('click', function(){
            $("#method").val('')
            $("#name").val('')
            $("#user_id").val('').trigger('change');
            $("#year").val('').trigger('change');
            $("#tournament_id").val('').trigger('change');
        })

        function resetModalForm(create = true, id) {
            let form = $("#form_create");
            let title = $("#modal_title");
            if (create) {
                title.html("Agregar Nuevo Grupo");
                form.prop("action", url_current)
                form.prop("method", 'POST');
            } else {
                title.html("Actualizar Grupo De Competencia.");
                form.prop("action", url_current + id)
                form.append("<input name='_method' value='PUT' type='hidden'>");
            }
        }
    </script>
@endsection
