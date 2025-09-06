@extends('layouts.app')
@section('content')
<x-bread-crumb title="Pagos por estado" :option="0" />
<x-row-card col-inside="12">
    <hr>
    <h6 class="card-subtitle text-themecolor m-b-0 m-t-0">Contiene los pagos de cualquier grupo dependiendo del estado seleccionado.</h6>
    @include('payments.status.table')
</x-row-card>
@endsection
@section('modals')
@endsection
@section('scripts')
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script>
    $(document).ready(() => {

        table = $('#active_table').DataTable({
            buttons: [
                {
                    extend: 'excel',
                    text: 'Exportar a Excel',
                },
            ],
            paging: false,
            dom: 'Bit', //lftip
            scrollX: true,
            scrollCollapse: true,
            columnDefs: [
                {
                    targets: [0, 1],
                    width: '5%'
                },
                {
                    targets: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
                    width: 'auto'
                }
            ],
            initComplete: function () {
                var btns = $('.dt-button');
                btns.addClass('btn waves-effect waves-light btn-rounded btn-info');
                btns.removeClass('dt-button');
            }
        })
        $('.payment_amount').inputmask("pesos");
    })
</script>

@endsection