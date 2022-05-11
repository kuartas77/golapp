@extends('layouts.app')
@section('title', 'Pagos')
@section('content')
    <x-bread-crumb title="Pagos" :option="0"/>
    <x-row-card col-inside="12" >
    @include('payments.historic.table')
    </x-row-card>
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}';
        $(document).ready(() => {
            $('#active_table').DataTable({
                "lengthMenu": [[5, 10, 30, 50], [5, 10, 30, 50]],
                "processing": true,
                "serverSide": true,
                "ajax": $.fn.dataTable.pipeline({
                    url: url_current,
                    pages: 5 // number of pages to cache
                }),
                "columns": [
                    {data: 'full_group'},
                    {data: 'id', render: (data, type, row, meta) => {
                            let links = '';
                            row.payments.map((assist) => {
                                links += '<a href="' + assist.url_historic + '" class="btn btn-info btn-xs" target="_blank">' + assist.year + '</a>&nbsp;';
                            });
                            return links;
                        }
                    }
                ],
                "order": [[ 0, "desc" ]]
            });
        });
    </script>
@endsection
