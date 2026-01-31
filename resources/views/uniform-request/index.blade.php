@extends('layouts.app')
@section('title', 'Solicitudes de uniformes')
@section('content')
<x-bread-crumb title="Solicitudes de uniformes" :option="0" />
<x-row-card col-inside="12">
    <p>Podrás encontrar las solicitudes pendientes de uniformes generadas desde la App ClubLink.</p>
    <span class="text-muted">
        Al dar click en crear una factura las solicitudes pendientes se agregarán a esta, sin importar la solicitud seleccionada
    </span>
    <div class="table-responsive-md">
        <table class="display compact cell-border" id="uniformRequestTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Deportista</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Cantidad</th>
                    <th>Talla</th>
                    <th>Notas</th>
                    <th>Solicitado en</th>
                    <th>Crear Factura</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</x-row-card>
@endsection
@section('modals')
@include('modals.image')
@endsection
@push('scripts')
<script>
    let active_table = $('#uniformRequestTable');
    const urlCurrent = "{{route('uniform-request.index')}}";
    $(document).ready(function() {

        function addSelectType(column) {
            let select = $('<select><option value="">Tipo...</option></select>')
            .appendTo($(column.header()).empty())
            .on('change', function () {
                let val = $.fn.dataTable.util.escapeRegex(
                    $(this).val()
                );

                column
                    .search(val ? val : '', true, false)
                    .draw();
            });

            select.append('<option value="UNIFORM">Uniforme</option>')
            select.append('<option value="BALL">Balón</option>')
            select.append('<option value="SOCKS">Medias</option>')
            select.append('<option value="SHOES">Guayos</option>')
            select.append('<option value="SHORTS">Pantaloneta</option>')
            select.append('<option value="JERSEY">Camisa</option>')
            select.append('<option value="OTHER">Otro</option>')
        }

        function filterTable() {

            this.api().columns(2).every(function () {
                let column = this;
                addSelectType(column)
            });
        }

        active_table = $('#uniformRequestTable').DataTable({
            "lengthMenu": [
                [10, 30, 50, 70, 100],
                [10, 30, 50, 70, 100]
            ],
            "order": [
                [0, "desc"]
            ],
            "processing": true,
            "serverSide": true,
            "deferRender": true,
            "fixedColumns": true,
            "ajax": $.fn.dataTable.pipeline({
                url: urlCurrent,
                pages: 5 // number of pages to cache
            }),
            initComplete: filterTable,
            "columns": [
                {
                    data: 'id', name: 'id',
                    searchable: false,
                    orderable: true
                },
                {
                    data: 'full_names',
                    name: 'full_names',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'type',
                    render: function(data, type, row) {
                        let badge = ''
                        switch (data) {
                            case 'UNIFORM':
                                badge = '<span class="badge badge-info">Uniforme</span>'
                                break;
                            case 'BALL':
                                badge = '<span class="badge badge-info">Balón</span>'
                                break;
                            case 'SOCKS':
                                badge = '<span class="badge badge-info">Medias</span>'
                                break;
                            case 'SHOES':
                                badge = '<span class="badge badge-info">Guayos</span>'
                                break;
                            case 'SHORTS':
                                badge = '<span class="badge badge-info">Pantaloneta</span>'
                                break;
                            case 'JERSEY':
                                badge = '<span class="badge badge-info">Camisa</span>'
                                break;
                            default:
                                badge = '<span class="badge badge-info">Otro (ver notas)</span>'
                                break;
                        }
                        return badge
                    },
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'status',
                    render: function(data, type, row) {
                        let badge = '<span class="badge badge-error">Cancelada</span>'

                        if (data === 'PENDING') {
                            badge = '<span class="badge badge-info">Pendiente</span>'
                        } else if (data === 'APPROVED') {
                            badge = '<span class="badge badge-success">Aprovada</span>'
                        } else if (data === 'REJECTED') {
                            badge = '<span class="badge badge-warning">Rechazada</span>'
                        }
                        return badge
                    },
                    searchable: false,
                    orderable: true,
                },
                {
                    data: 'quantity',
                    searchable: false,
                    orderable: true
                },
                {
                    data: 'size',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'additional_notes',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'created_at',
                    render: (data, type, row) => moment(data).format('DD-MM-YYYY'),
                    searchable: false,
                    orderable: true
                },
                {
                    data: 'inscription_id',
                    searchable: false,
                    orderable: false,
                    render: (data, type, row) => `<a href="/invoices/create/${data}" class="btn btn-success btn-xs" title="Factura"><i class="fas fa-file-alt"></i></a>`,

                }
            ]
        });
    })
</script>
@endpush