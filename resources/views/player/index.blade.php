@extends('layouts.app')
@section('content')
<div class="middle-content container-xxl p-0">

    <x-bread-crumb title="Deportistas" :option="2"/>

    <div class="row layout-top-spacing">

        <div class="widget-content widget-content-area br-8">
            @include('player.table')
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
                                '<img alt="'+ row.full_names +'" class="img-fluid rounded-circle" src="'+row.photo_url+'" width="40px" height="60px"></div>'
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
                                edit = '<a href="' + row.url_edit + '" class="btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg></a>';
                            }
                            return '<div class="action-btns">' +
                                '<a href="' + row.url_show + '" class="btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></a>' +
                                edit +
                                '<a href="' + row.url_impression + '" target="_blank" class="btn-sm"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1"/></svg></a></div>';
                        }, 'searchable': false
                    }
                ],
                "columnDefs": [
                    {"searchable": false, "targets": [0, 1, 5, 8]},
                    {"orderable": false, "targets": [0, 1, 6, 7, 8]},
                    // {"width": "10px" , "targets": [7, 8] }
                ],
                "createdRow": function (row, data, dataIndex) {
                    $(row).find('td').addClass('text-center')
                },
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
