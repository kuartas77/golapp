@extends('layouts.app')
@section('title', 'Notificaciones')
@section('content')
<x-bread-crumb title="Notificaciones" :option="0" />
<x-row-card col-inside="12">
    <p>Podrás encontrar todas las notificaciones generadas para la App GOLAPPLINK.</p>
    <span class="text-muted">
        Las notificaciones se mostrarán en GOLAPPLINK sólo hasta despues de 8 días de haber sido creadas.
    </span>

    @include('topic-notification.table')

</x-row-card>
@endsection
@section('modals')
    @include('modals.notification')
@endsection
@push('scripts')
<script>
    let active_table = $('#notificationTable');
    const urlCurrent = "{{route('notification.index')}}";
    $(document).ready(function() {
        active_table = $('#notificationTable').DataTable({
            "lengthMenu": [
                [10, 30, 50, 70, 100],
                [10, 30, 50, 70, 100]
            ],
            "order": [
                [4, "desc"]
            ],
            "processing": true,
            "serverSide": true,
            "deferRender": true,
            "fixedColumns": true,
            "ajax": $.fn.dataTable.pipeline({
                url: urlCurrent,
                pages: 5 // number of pages to cache
            }),
            "columnDefs": [
                {
                    targets: [0],
                    className: 'dt-body-center dt-head-center',
                }
            ],
            "columns": [
                {
                    data: 'id',
                    searchable: false,
                    orderable: true
                },
                {
                    data: 'topics',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'title',
                    searchable: true,
                    orderable: false
                },
                {
                    data: 'body',
                    searchable: false,
                    orderable: false
                },
                {
                    data: 'created_at',
                    searchable: false,
                    orderable: true,
                    render: (data, type, row) => moment(data).format('DD-MM-YYYY hh:mm:ss a')
                },
                {
                    data: 'id',
                    render: (data, type, row) => ``,
                    searchable: false,
                    orderable: false,
                },

            ]
        });
})
</script>
@endpush