@extends('layouts.app')
@section('content')
<div class="middle-content container-xxl p-0">

    <x-bread-crumb title="Deportistas" :option="2"/>

    <div class="row layout-top-spacing">

    <div class="widget-content widget-content-area br-8">
            @include('player.table')
        </div>

        <div class="col-md-12">
        </div>

    </div>

</div>
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
                "scrollY":"550px",
                "scrollCollapse":true,
                "processing": true,
                "serverSide": true,
                "deferRender": true,
                "columns": [
                    {
                        "className": 'dt-control',
                        "orderable": false,
                        "data": null,
                        "defaultContent": ''
                    },
                    {
                        data: 'id', "render": function (data, type, row) {
                            return '<div class="usr-img-frame me-2 rounded-circle">'+
                                '<img alt="'+ row.full_names +'" class="img-fluid rounded-circle" src="'+row.photo_url+'"></div>'
                        }, 'searchable': false
                    },
                    {data: 'unique_code', name: 'unique_code'},
                    {data: 'identification_document', name: 'identification_document'},
                    {data: 'full_names', name: 'last_names'},

                    {data: 'gender', 'searchable': false},
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
                        }, 'searchable': false
                    }
                ],
                "columnDefs": [
                    {"searchable": false, "targets": [0, 1, 5, 8]},
                    {"orderable": false, "targets": [0, 1, 6, 7, 8]},
                    // {"width": "10px" , "targets": [7, 8] }
                ],
                initComplete: filterTable,
                "ajax": $.fn.dataTable.pipeline({
                    url: urlCurrent,
                    pages: 5 // number of pages to cache
                })
            });

            $('#table_players tbody').on('click', 'td.dt-control', function () {
                let tr = $(this).closest('tr');
                let row = active_table.row(tr);
                onClickDetails(tr, row);
            });
        });

        function filterTable() {
            // Apply the search
            let column = this.api().columns(6);
            $("<input type='search' class='' placeholder='Buscar F.Nacimiento' />")
                .appendTo($(column.header()).empty())
                .on('keyup change search', function () {
                    if (column.search() !== this.value) {
                        column.search(this.value)
                            .draw();
                    }
                });

            let start_date = this.api().columns(7);
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
            } else {
                // Open this row
                row.child(format(row.data())).show();
            }
        }

        const format = (d) => {
            let rows = "";
            d.people.forEach(function ({tutor, relationship_name, names, phone, mobile}) {
                let is_tutor = (tutor === 1) ? "ACUDIENTE" : "";
                let phones = (mobile == null) ?  phone : `${phone} ${mobile}`
                rows += '<tr>' +
                    '<th><strong>' + is_tutor + '</strong></th>'+
                    '<th><span>' + relationship_name + '</span></th><th>' + names + '</th>' +
                    '<th><span>Teléfonos:</span></th><th>' + phones + '</th>' +
                    '</tr>';
            });

            return '<table class="w-100">' + rows + '</table>';
        }

    </script>
@endsection
