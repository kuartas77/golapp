@extends('layouts.app')
@section('title', 'Control De Competencias')
@section('content')
<x-bread-crumb title="Control De Competencia" :option="0"/>
    <x-row-card col-inside="12" >
        <a class="float-right btn waves-effect waves-light btn-rounded btn-info" id="btn-add" href="{{route('matches.create')}}">
            <i class="fa fa-plus" aria-hidden="true"></i>
            @lang('messages.match_add')
        </a>
        <div class="row">
            <div class="form-group col-sm-3 ">
                {!! Form::selectYear('year', $minYear, now()->year, now()->year,['class' => 'form-control input-sm sl', 'placeholder' => 'Seleccione...', 'id' => 'year']) !!}
            </div>
        </div>

        @include('competition.match.table')
    </x-row-card >
@endsection
@section('scripts')
    <script>
        const url_current = "{{URL::current()}}";
        const url_create = "{{route('matches.create')}}";
        const competitionGroups = @json($competitionGroups);
        let actualYear = {{now()->year}};
        let selectYear = {{now()->year}};

        $(document).ready(function () {
            $('#partidos-table').DataTable({
                "lengthMenu": [[10, 20, 30, -1], [10, 20, 30, "Todos"]],
                "processing": true,
                "serverSide": true,
                "ajax": $.fn.dataTable.pipeline({
                    url: url_current,
                    pages: 5 // number of pages to cache
                }),
                "order": [[2, 'desc'],],
                "columns": [
                    {data: 'tournament.name'},
                    {data: 'competition_group.name'},
                    {data: 'date'},
                    {data: 'hour'},
                    {data: 'place'},
                    {data: 'num_match'},
                    {data: 'rival_name'},
                    {data: 'final_score', render: function (data, type, row, meta) {
                        return `${data.soccer} - ${data.rival}`;
                    }},
                    {data: 'general_concept_short'},
                    {
                        data: 'id', render: function (data, type, row, meta) {
                            btnEdit = selectYear !== actualYear ? '' : '<a href="' + row.url_edit + '" class="btn btn-info btn-xs"><i class="fas fa-pencil-alt" aria-hidden="true"></i></a>';
                            btnDelete = selectYear !== actualYear ? '' : '<button type="submit" class="btn btn-danger btn-xs" onclick="confirmDelete(this, event)"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>';

                            return '<form method="POST" action="' + row.url_destroy + '" accept-charset="UTF-8"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="' + window.token.csrfToken + '"><div class="btn-group">' +
                                '<a href="' + row.url_show + '" class="btn btn-success btn-xs" target="_blank"><i class="fas fa-print" aria-hidden="true"></i></a>' +
                                btnEdit +
                                btnDelete +
                                '</div></form>';
                        }
                    },
                ]
            });
        });

        $('body').on('change', 'select.sl', function () {
            const ruta = url_current + "?year_=" + $('#year').val();
            selectYear = Number($('#year').val());
            $('table.dataTable').DataTable().ajax.url($.fn.dataTable.pipeline({url: ruta}));
            $('table.dataTable').DataTable().clearPipeline().draw();
        });

        const confirmDelete = (element, event) => {
            event.preventDefault();
            const form = $(element).closest('form');
            Swal.fire({
                title: '¿Deseas Eliminar Esta Competencia?',
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

        $("#btn-add").on('click', (event) => {
            event.preventDefault();
            Swal.fire({
                title: 'Seleccione Un Grupo De Competencia',
                type: "info",
                input: 'select',
                inputOptions: competitionGroups,
                inputPlaceholder: 'Selecciona...',
                allowOutsideClick: true,
                allowEscapeKey: true,
                inputValidator: function (value) {
                    return new Promise(function (resolve) {
                        if (value !== '') {
                            resolve();
                        } else {
                            resolve('Necesitas Seleccionar Uno.');
                        }
                    });
                }
            }).then(function (result) {
                if (result?.value !== undefined) {
                    location.assign(url_create + '?competition_group=' + result.value);
                }
            });
        });
    </script>
@endsection
