@extends('layouts.app')
@section('title', 'Solicitudes de Pago')
@section('content')
<x-bread-crumb title="Solicitudes de Pago" :option="0" />
<x-row-card col-inside="12">
    <p>Podrás encontrar todos los comprobantes de pago subidas desde la App GOLAPPLINK.</p>
    <div class="table-responsive-md">
        <table class="display compact cell-border" id="paymentRequestTable">
            <thead>
                <tr>
                    <th>Factura</th>
                    <th>Deportista</th>
                    <th>Grupo</th>
                    <th class="text-right">Monto Factura</th>
                    <th>Enviado en</th>
                    <th>Método</th>
                    <th>Referencia</th>
                    <th class="text-right">Monto Comprobante</th>
                    <th>Comprobante</th>
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
    let active_table = $('#paymentRequestTable');
    const urlCurrent = "{{route('payment-request.index')}}";
    $(document).ready(function() {
        active_table = $('#paymentRequestTable').DataTable({
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
            "columns": [
                {
                    data: 'invoice.invoice_number',
                    name: 'invoice_number',
                    render: (data, type, row) => `<a href="${row.invoice.url_show}" target="_blank">${data}</a>`,
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'player.full_names',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'name',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'invoice.total_amount',
                    name: 'invoices.total_amount',
                    render: (data, type, row) => `$${formatMoney(data)}`,
                    searchable: false,
                    orderable: true,
                },
                {
                    data: 'created_at',
                    searchable: false,
                    render: (data, type, row) => moment(data).format('DD-MM-YYYY')
                },
                {
                    data: 'payment_method',
                    render: function(data, type, row) {
                        let badge = '<span class="badge badge-secondary">Otro</span>'

                        if (data === 'cash') {
                            badge = '<span class="badge badge-success">Efectivo</span>'
                        } else if (data === 'card') {
                            badge = '<span class="badge badge-primary">Tarjeta</span>'
                        } else if (data === 'transfer') {
                            badge = '<span class="badge badge-info">Transferencia</span>'
                        }
                        return badge
                    },
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'reference_number',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'amount',
                    name: 'amount',
                    render: (data, type, row) => `$${formatMoney(data)}`,
                    searchable: false,
                    orderable: false,
                },
                {
                    data: 'url_image',
                    render: (data, type, row) => {
                        return '<a href="javascript:void(0)" '
                        +'data-toggle="modal"'
                        +'data-target="#imageModal"'
                        +'data-image="'+ row.url_image +'"'
                        +'data-title="'+ row.reference_number +'"'
                        +'alt="'+ row.reference_number +'">Ver</a>'
                    },
                    searchable: false,
                    orderable: false,
                },
            ]
        });

        $('#imageModal').on('shown.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var imageUrl = button.data('image');
            var imageTitle = button.data('title');

            $(this).find('#modalImage').attr('src', imageUrl);
            $(this).find('#imageTitle').text('Referencia: ' + imageTitle);
        });
})
</script>
@endpush