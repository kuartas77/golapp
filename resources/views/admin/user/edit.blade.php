@extends('layouts.app')
@section('content')
<x-bread-crumb title="Usuario" :option="0"/>
<x-row-card col-inside="4" col-outside="4" >
    {{html()->modelForm($user, 'patch', route('users.update', [$user->id]))->attributes(['id'=>'form_user','class' => 'form-material m-t-0'])->open()}}
        <div class="form-body">
            @include('admin.user.fields')
        </div>
        <div class="form-actions m-t-0 text-center">
            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info">Guardar</button>
            <a href="{{ route('users.index') }}" class="btn waves-effect waves-light btn-rounded btn-outline-warning">Cancelar</a>
        </div>
    {{ html()->closeModelForm() }}
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
