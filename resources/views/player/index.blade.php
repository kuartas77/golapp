@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Deportistas" :option="2"/>
    <x-row-card col-inside="12" >
    @include('player.table')
    </x-row-card >
@endsection
@section('modals')
    @include('modals.create_inscription')
    @hasanyrole('super-admin')
        @include('modals.import_players')
    @endhasanyrole
@endsection
@section('scripts')
    <script>
        const isAdmin = {{ $admin }};
        const urlCurrent = "{{route('players.enabled')}}";
        let active_table = $('#table_players');
        $(document).ready(function () {
            active_table = $('#table_players').DataTable({
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
                            return "<img class='media-object img-rounded' src='" + row.photo_url + "' width='90' height='60' alt='" + row.full_names + "'>";
                        }
                    },
                    {data: 'unique_code', name: 'unique_code'},
                    {data: 'identification_document', name: 'identification_document'},
                    {data: 'full_names', name: 'full_names'},
                    {data: 'mobile'},
                    {data: 'gender'},
                    {data: 'date_birth', name: 'date_birth'},
                    {data: 'created_at', name: 'created_at'},
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
                "columnDefs": [
                    {"searchable": false, "targets": [0, 1, 5, 6, 9]},
                    {"orderable": false, "targets": [0, 1, 7, 8, 9]},
                    {"width": "1%" , "targets": [7, 8] }
                ],
                initComplete: filterTable,
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

        function filterTable() {
            // Apply the search
            let column = this.api().columns(7);
            $("<input type='search' class='' placeholder='Buscar F.Nacimiento' />")
                .appendTo($(column.header()).empty())
                .on('keyup change search', function () {
                    if (column.search() !== this.value) {
                        column.search(this.value)
                            .draw();
                    }
                });
            
            let start_date = this.api().columns(8);
            $("<input type='search' class='' placeholder='Buscar F.Registro' />")
                .appendTo($(start_date.header()).empty())
                .on('keyup change search', function () {
                    if (start_date.search() !== this.value) {
                        start_date.search(this.value)
                            .draw();
                    }
                });
            $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
        }

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
            d.people.forEach(function ({tutor, relationship_name, names, phone, mobile}) {
                let is_tutor = tutor === 1 ? "ACUDIENTE" : "";
                rows += '<tr>' +
                    '<th><strong>' + is_tutor + '</strong></th>'+
                    '<th><span>' + relationship_name + '</span></th><th>' + names + '</th>' +
                    '<th><span>Teléfonos:</span></th><th>' + phone + ' - ' + mobile + '</th>' +
                    '</tr>';
            });

            return '<table class="w-100">' + rows + '</table>';
        }

    </script>
@endsection
