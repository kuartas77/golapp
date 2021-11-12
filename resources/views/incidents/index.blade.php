@extends('layouts.app')
@section('title', 'Incidencias')
@section('content')
    @include('templates.bread_crumb', ['title' => 'Incidencias', 'option' => 0])
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('incidents.table')
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modals')
    @include('modals.incident_cu')
@endsection
@section('scripts')
    <script>
        const urlCurrent = '{{URL::current()}}';
        $(document).ready(function () {
            $(".select2").select2();
            $("#form_create").validate();
            $("#active_table").DataTable({
                "lengthMenu": [[10, 30, 50, 70, -1], [10, 30, 50, 70, "Todos"]],
                "scrollX": true,
                "processing": true,
                "serverSide": true,
                "ajax": $.fn.dataTable.pipeline({
                    url: urlCurrent,
                    pages: 5 // number of pages to cache
                }),
                "columns": [
                    {data: 'professor.name'},
                    {data: 'count'},
                    {
                        data: 'id', "render": function (data, type, row) {
                            return '<a href="' + row.url_show + '" class="btn btn-warning btn-xs"><i class="fas fa-eye"></i></a>';
                        }
                    }
                ]

            });

            $("#btn-add").on('click', function () {
                $("#user_incident_id").val('').trigger('change');
                $("#incidence").val('');
                $("#description").val('');
            });
        });
    </script>
@endsection
