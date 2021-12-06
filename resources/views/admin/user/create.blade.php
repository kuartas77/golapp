@extends('layouts.app')
@section('title', 'Usuarios')
@section('content')
    @include('templates.bread_crumb', ['title' => 'Usuario', 'option' => 0])
<div class="row">
    <div class="col-2"></div>
    <div class="col-8">
        <div class="card">
            <div class="card-body">
                {!! Form::open(['route' => 'users.store', 'id'=>'form_user', 'class' => 'form-material m-t-0']) !!}
                    <div class="form-body">
                        @include('admin.user.fields')
                    </div>
                    <div class="form-actions m-t-0 text-center">
                        <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info">Guardar</button>
                        <a href="{!! URL::previous() !!}" class="btn waves-effect waves-light btn-rounded btn-outline-warning">Cancelar</a>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    <div class="col-2"></div>
</div>
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
        password:{required:true},
        rol_id:{required:true},
    }
});
</script>
@endsection
