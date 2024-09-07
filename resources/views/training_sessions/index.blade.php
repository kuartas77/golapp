@extends('layouts.app')
@section('title', 'Sessiones De Entrenamiento')
@section('content')
<x-bread-crumb title="Sessiones De Entrenamiento" :option="0" />
<x-row-card col-inside="12">
@include('training_sessions.table')
</x-row-card>
@endsection
@section('scripts')
<script>
    const url_current = "{{ URL::current() }}";
    const url = "{{route('training_sessions.enabled')}}";
    $(document).ready(function() {
        let table = $('#active_table').DataTable({
            "lengthMenu": [
                [30, -1],
                [30, "Todos"]
            ],
            "processing": true,
            "serverSide": true,
            "ajax": $.fn.dataTable.pipeline({
                url: url,
                pages: 5 // number of pages to cache
            }),
            "columns": [
                { data: 'creator', name: 'creator' },
                { data: 'group', name: 'group'},
                { data: 'training_ground', name: 'training_ground' },
                { data: 'period', name: 'period' },
                { data: 'session', name: 'session' },
                { data: 'date', name: 'date' },
                { data: 'hour', name: 'hour' },
                { data: 'tasks', name: 'tasks' },
                { data: 'created_at', name: 'created_at' },
                { data: 'buttons', name: 'buttons' },
            ]
        });
    });
</script>
@endsection