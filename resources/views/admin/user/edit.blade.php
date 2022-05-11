@extends('layouts.app')
@section('title', 'Usuarios')
@section('content')
<x-bread-crumb title="Usuario" :option="0"/>
<x-row-card col-inside="6" col-outside="3" >
    {!! Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'patch', 'id'=>'form_user','class' => 'form-material m-t-0']) !!}
        <div class="form-body">
            @include('admin.user.fields')
        </div>
        <div class="form-actions m-t-0 text-center">
            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info">Guardar</button>
            <a href="{{ route('users.index') }}" class="btn waves-effect waves-light btn-rounded btn-outline-warning">Cancelar</a>
        </div>
    {!! Form::close() !!}
</x-row-card >
@endsection
@section('modals')
@endsection
@section('scripts')
<script>
    $('#permissions').select2({
        'placeholder':'Seleccionar...'
    });
$("#form_user").validate({
    rules:{
        name:{required:true},
        email:{required:true, emails:true},
        rol_id:{required:true},
    }
});
</script>
@endsection
