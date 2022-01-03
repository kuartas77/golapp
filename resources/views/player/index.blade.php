@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Deportistas" :option="0"/>
    <x-row-card col-inside="12" >
    @include('player.table')
    </x-row-card >
@endsection
@section('modals')
    @include('modals.create_inscription')
@endsection
@section('scripts')
    <script>
        const isAdmin = {{isAdmin()}};
        const urlCurrent = "{{route('players.enabled')}}";
        $(document).ready(function () {
            const active_table = $('#table_players').DataTable({
                "lengthMenu": [[10, 30, 50, 70, 100], [10, 30, 50, 70, 100]],
                "order": [[2, "desc"]],
                "scrollX": true,
                "processing": true,
                "serverSide": true,
                "deferRender": true,
                "fixedColumns": true,
                "columns": [
                    {
                        "className": 'details-control',
                        "orderable": false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        data: 'id', "render": function (data, type, row) {
                            return "<img class='media-object img-rounded' src='" + row.photo + "' width='60' height='60' alt='" + row.full_names + "'>";
                        }
                    },
                    {data: 'unique_code'},
                    {data: 'created_at'},
                    {data: 'date_birth'},
                    {data: 'full_names'},
                    {data: 'gender'},
                    {data: 'identification_document'},
                    {data: 'mobile'},
                    {
                        data: 'id',
                        "render": function (data, type, row) {
                            let edit = "";
                            if(isAdmin){
                                edit = '<a href="' + row.url_edit + '" class="btn btn-warning btn-xs"><i class="fas fa-pencil-alt"></i></a>';
                            }
                            return '<div class="btn-group">' +
                                '<a href="' + row.url_show + '" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>' +
                                edit +
                                '<a href="' + row.url_impression + '" target="_blank" class="btn btn-info btn-xs"><i class="fas fa-print" aria-hidden="true"></i></a></div>';
                        }
                    }
                ],
                "ajax": $.fn.dataTable.pipeline({
                    url: urlCurrent,
                    pages: 5 // number of pages to cache
                })
            });

            $('#table_players tbody').on('click', 'td.details-control', function () {
                let tr = $(this).closest('tr');
                let row = active_table.row(tr);
                onClickDetails(tr, row);
            });
        });

        const onClickDetails = (tr, row) => {
            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            } else {
                // Open this row
                row.child(format(row.data())).show();
                tr.addClass('shown');
            }
        }

        const format = (d) => {
            let rows = "";
            d.people.forEach(function (people) {
                let tutor = people.is_tutor ? "ACUDIENTE" : "";
                rows += '<tr>' +
                    '<th><strong>' + tutor + '</strong> <span>' + people.relationship_name + '</span></th><td>' + people.names + '</td>' +
                    '<th><span>teléfonos:</span></th><td>' + people.phone + ' - ' + people.mobile + '</td>' +
                    '</tr>';
            });

            return '<table class="w-100">' + rows + '</table>';
        }

    </script>
@endsection
