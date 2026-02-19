@extends('layouts.app')
@section('title', 'Items Facturas')
@section('content')
<x-bread-crumb title="Items Facturas" :option="0" />
<x-row-card col-inside="12">
    <p>En esta página podrás encontrar los items de todas las facturas.</p>
    <a class="float-right btn waves-effect waves-light btn-rounded btn-info" href="{{route('export.items.invoices')}}" id="export-pdf" target="_blank">
        <i class="fa fa-print" aria-hidden="true"></i> Exportar pendientes en PDF
    </a>
    <div class="table-responsive-md">
        <table class="display compact cell-border" id="invoicesTable">
            <thead class="thead-light">
                <tr>
                    <th>Factura</th>
                    <th>Creado</th>
                    <th>Deportista</th>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Metodo Pago</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Precio Unitario</th>
                    <th class="text-right">Total</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th>Totales:</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th class="text-right"></th>
                    <th class="text-right"></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</x-row-card>
@endsection
@push('scripts')
<script>
    const urlCurrent = "{{route('items.invoices.index')}}";
    let active_table = $('#invoicesTable');

    function addSelectStatus(column) {
        let select = $('<select><option value="">Estado</option></select>')
        .appendTo($(column.header()).empty())
        .on('change', function () {
            let val = $.fn.dataTable.util.escapeRegex(
                $(this).val()
            );

            column
                .search(val ? val : '', true, false)
                .draw();
        });

        select.append('<option value="1">Pagado</option>')
        select.append('<option value="0">Pendiente</option>')
    }

    function addSelectPaymentMethod(column) {
        let select = $('<select><option value="">Método Pago</option></select>')
        .appendTo($(column.header()).empty())
        .on('change', function () {
            let val = $.fn.dataTable.util.escapeRegex(
                $(this).val()
            );

            column
                .search(val ? val : '', true, false)
                .draw();
        });

        select.append('<option value="cash">Efectivo</option>')
        select.append('<option value="card">Tarjeta</option>')
        select.append('<option value="transfer">Transferencia</option>')
        select.append('<option value="check">Cheque</option>')
        select.append('<option value="other">Otro</option>')
    }

    function filterTable() {

        let invoice = this.api().columns(0);
        $("<input type='search' class='' placeholder='Factura' />")
            .appendTo($(invoice.header()).empty())
            .on('keyup search', function () {
                if (invoice.search() !== this.value) {
                    invoice.search(this.value)
                        .draw();
                }
            });

        let created = this.api().columns(1);
        $("<input type='date' class='' placeholder='Fecha' />")
            .appendTo($(created.header()).empty())
            .on('change', function () {
                if (created.search() !== this.value) {
                    created.search(this.value)
                        .draw();
                }
            });

        let player = this.api().columns(2);
        $("<input type='search' class='' placeholder='Deportista' />")
            .appendTo($(player.header()).empty())
            .on('keyup search', function () {
                if (player.search() !== this.value) {
                    player.search(this.value)
                        .draw();
                }
            });
        let description = this.api().columns(4);
        $("<input type='search' class='' placeholder='Descripción' />")
            .appendTo($(description.header()).empty())
            .on('keyup search', function () {
                if (description.search() !== this.value) {
                    description.search(this.value)
                        .draw();
                }
            });

        // Apply the search
        this.api().columns(5).every(function () {
            let column = this;
            addSelectPaymentMethod(column)
        });
        this.api().columns(9).every(function () {
            let column = this;
            addSelectStatus(column)
        });

        $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
    }

    $(document).ready(function() {
        active_table = $('#invoicesTable').DataTable({
            dom: 'litp',//lftip
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
            "columnDefs": [{
                    targets: [7, 8],
                    className: 'dt-body-right',
                },
                {
                    targets: [3, 9],
                    className: 'dt-body-center dt-head-center',
                }
            ],
            columns: [
                {
                    data: 'invoice.invoice_number',
                    name: 'invoice.invoice_number',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'created_at',
                    name: 'invoice_items.created_at',
                    searchable: true,
                    orderable: false,
                    render: (data, type, row) => moment(data).format('DD/MM/YYYY')
                },
                {
                    data: 'invoice.student_name',
                    name: 'invoice.student_name',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'type',
                    name: 'type',
                    searchable: false,
                    orderable: false,
                    render: (data, type, row) => data === 'monthly' ? `Mensualidad`: data == 'enrollment' ? 'Inscripción' : 'Item'
                },
                {
                    data: 'description',
                    name: 'description',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'payment_method',
                    name: 'payment_method',
                    searchable: true,
                    render: (data, type, row) => {
                        let badge = ''
                        switch (data) {
                            case 'cash':
                                badge = '<span class="badge">Efectivo</span>'
                                break;
                            case 'card':
                                badge = '<span class="badge">Tarjeta</span>'
                                break;
                            case 'transfer':
                                badge = '<span class="badge">Transferencia</span>'
                                break;
                            case 'check':
                                badge = '<span class="badge">Cheque</span>'
                                break;
                            case 'other':
                                badge = '<span class="other">Otro</span>'
                                break;

                            default:
                                break;
                        }
                        return badge
                    },
                    orderable: false
                },
                {
                    data: 'quantity',
                    name: 'quantity',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'unit_price',
                    name: 'unit_price',
                    searchable: false,
                    orderable: false,
                    render: (data, type, row) => `$${formatMoney(data)}`
                },
                {
                    data: 'total',
                    name: 'total',
                    searchable: false,
                    orderable: false,
                    render: (data, type, row) => `$${formatMoney(data)}`
                },
                {
                    data: 'is_paid',
                    name: 'is_paid',
                    searchable: true,
                    orderable: false,
                    render: function(data, type, row) {
                        let badge = '<span class="badge badge-warning">Pendiente</span>'

                        if (data == '1') {
                            badge = '<span class="badge badge-success">Pagada</span>'
                        }
                        return badge
                    },
                },
            ],
            initComplete: filterTable,
            footerCallback: function(row, data, start, end, display) {
                let api = this.api();

                // Helper function to remove formatting (like currency symbols) and convert to a number
                let intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i :
                        0;
                };

                // Calculate total for column 4 (e.g., Quantity)
                const total = api
                    .column(7)
                    .data()
                    .reduce((a, b) => intVal(a) + intVal(b), 0);

                const payment = api
                    .column(8)
                    .data()
                    .reduce((a, b) => intVal(a) + intVal(b), 0);

                api.column(7).footer().innerHTML = `$${formatMoney(total)}`;
                api.column(8).footer().innerHTML = `$${formatMoney(payment)}`;
            }

        })
    })


</script>
@endpush