@extends('layouts.app')
@section('title', 'Contratos')
@section('content')
<x-bread-crumb title="Contratos" :option="0"/>
<x-row-card col-inside="8" col-outside="2">
    @include('backoffice.contracts.table')
</x-row-card >

@endsection
@section('scripts')
<script>
    $(document).ready(function() {
    userTable = $('#user-table').DataTable({
                "lengthMenu": [[10,20,50, -1], [10,20,50, "Todos"]]
            });
    userTrash = $('#userTrash-table').DataTable({
                "lengthMenu": [[10,20,50, -1], [10,20,50, "Todos"]]
            });
});

</script>
@endsection
