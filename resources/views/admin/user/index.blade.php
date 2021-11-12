@extends('layouts.app')
@section('title', 'Usuarios')
@section('content')
    @include('templates.bread_crumb', ['title' => 'Usuarios', 'option' => 0])
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Usuarios</h4>
                    @include('admin.user.table')
                </div>
            </div>
        </div>
    </div>

</div>
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
