@extends('layouts.app')
@section('title', 'Facturas')
@section('content')
<x-bread-crumb title="Facturas" :option="0" />
<x-row-card col-inside="12">
    <div class="table-responsive-md">
        <table class="display compact cell-border" id="invoicesTable">
            <thead>
                <tr>
                    <th># Factura</th>
                    <th>Deportista</th>
                    <th>Grupo</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Pagado</th>
                    <th class="text-center">Estado</th>
                    <th>Fecha</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th>Totales:</th>
                    <th></th>
                    <th></th>
                    <th class="text-right"></th>
                    <th class="text-right"></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</x-row-card>
@endsection
@push('scripts')
<script>
    const isAdmin = {{auth()->user()->hasAnyRole(['super-admin', 'school']) ? 1 : 0}};
    const urlCurrent = "{{route('invoices.index')}}";
    let active_table = $('#invoicesTable');
    $(document).ready(function() {
        active_table = $('#invoicesTable').DataTable({
            "lengthMenu": [
                [10, 30, 50, 70, 100],
                [10, 30, 50, 70, 100]
            ],
            "order": [
                [6, "desc"]
            ],
            "processing": true,
            "serverSide": true,
            "deferRender": true,
            "fixedColumns": true,
            "columns": [
                {
                    data: 'invoice_number',
                    name: 'invoice_number',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'student_name',
                    name: 'student_name',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'training_group.name',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'total_amount',
                    searchable: false,
                    orderable: false,
                    render: (data, type, row) => `$${formatMoney(data)}`
                },
                {
                    data: 'paid_amount',
                    searchable: false,
                    orderable: false,
                    render: (data, type, row) => `$${formatMoney(data)}`
                },
                {
                    data: 'status',
                    "render": function(data, type, row) {
                        let badge = '<span class="badge badge-secondary">Cancelada</span>'

                        if (data === 'paid') {
                            badge = '<span class="badge badge-success">Pagada</span>'
                        } else if (data === 'partial') {
                            badge = '<span class="badge badge-warning">Parcial</span>'
                        } else if (data === 'pending') {
                            badge = '<span class="badge badge-danger">Pendiente</span>'
                        }
                        return badge
                    },
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'created_at',
                    searchable: false,
                    render: (data, type, row) => moment(data).format('DD-MM-YYYY')
                },
                {
                    data: 'id',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row) {
                        let deleteButton = ''
                        const url = urlCurrent + '/' + data

                        if (isAdmin) {
                            deleteButton = '<button class="btn btn-danger btn-xs disable-invoice"><i class="fas fa-trash-alt"></i></button>'
                        }

                        return '<form method="POST" action="' + row.url_destroy + '" accept-charset="UTF-8"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="' + window.token.csrfToken + '"><div class="btn-group">' +
                            '<a href="' + url + '" class="btn btn-success btn-xs"><i class="fas fa-eye"></i></a>' +
                            '<a href="' + row.url_print + '" target="_blank" class="btn btn-info btn-xs"><i class="fas fa-print" aria-hidden="true"></i></a>' +
                            deleteButton +
                            '</div></form>';
                    }
                },
            ],
            "columnDefs": [{
                    targets: [3, 4, ],
                    className: 'dt-body-right',
                },
                {
                    targets: [5, ],
                    className: 'dt-body-center',
                }
            ],
            "ajax": $.fn.dataTable.pipeline({
                url: urlCurrent,
                pages: 5 // number of pages to cache
            }),
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
                    .column(3)
                    .data()
                    .reduce((a, b) => intVal(a) + intVal(b), 0);

                const payment = api
                    .column(4)
                    .data()
                    .reduce((a, b) => intVal(a) + intVal(b), 0);

                api.column(3).footer().innerHTML = `$${formatMoney(total)}`;
                api.column(4).footer().innerHTML = `$${formatMoney(payment)}`;
            }
        })

        $('#invoicesTable tbody').on('click', '.disable-invoice', function(event) {
            event.preventDefault();
            let message = "";
            const form = $(this).closest('form');
            Swal.fire({
                title: app_name,
                text: `¿Estas Seguro Que Quieres Eliminar Esta Factura?`,
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
            })
        });

    })
</script>
@endpush