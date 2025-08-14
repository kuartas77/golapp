@extends('layouts.app')
@section('title', 'Usuarios')
@section('content')
<x-bread-crumb title="Usuarios" :option="0"/>
<x-row-card col-inside="8" col-outside="2" >
    <h4 class="card-title">Usuarios</h4>
    @include('admin.user.table')
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
