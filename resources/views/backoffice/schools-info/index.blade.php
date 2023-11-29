@extends('layouts.app')
@section('content')
<x-bread-crumb title="Información De Escuelas" :option="0" />
<x-row-card col-inside="12">
    @include('backoffice.schools-info.table')
</x-row-card>
@endsection

@section('scripts')
<script>
    const url_current = "{{ URL::current() }}";
    const url = "{{route('config.datatables.schools_info')}}";
    const validateCheck = (value) => {
        return value ? '<span class="label label-success">SI</span>' : '<span class="label label-warning">NO</span>';
    }
    $(document).ready(function() {
        let table = $('#schools-table').DataTable({
            "lengthMenu": [
                [30, -1],
                [30, "Todos"]
            ],
            "processing": true,
            "serverSide": true,
            "ajax": $.fn.dataTable.pipeline({
                url: url,
                pages: 5
            }),
            "order": [ [2, 'desc'] ],
            "columns": [
                { data: 'name', name: 'name' },
                { data: 'users_count', name: 'users_count' },
                { data: 'inscriptions_count', name: 'inscriptions_count' },
                { data: 'players_count', name: 'players_count' },
                { data: 'payments_count', name: 'payments_count' },
                { data: 'tournament_payouts_count', name: 'tournament_payouts_count' },
                { data: 'assists_count', name: 'assists_count' },
                { data: 'skill_controls_count', name: 'skill_controls_count' },
                { data: 'matches_count', name: 'matches_count' },
                { data: 'tournaments_count', name: 'tournaments_count' },
                { data: 'training_groups_count', name: 'training_groups_count' },
                { data: 'competition_groups_count', name: 'competition_groups_count' },
                { data: 'incidents_count', name: 'incidents_count' },
            ]
        });
    });
</script>
@endsection