@extends('layouts.app')
@section('title', 'Escuela')
@section('content')
<x-bread-crumb title="Escuela" :option="0"/>
<x-row-card col-inside="12 col-sm-12 col-md-12 col-lg-10 col-xl-10" col-outside="1 col-lg-1 col-xl-1">
    {!! Form::open(['route' => ['school.update', 'school' => $school], 'id'=>'form_player', 'files'=>true, 'class'=>'form-material m-t-0']) !!}
    @method('PUT')
    <div class="form-body">
    @include('admin.school.form')
    </div>
    <div class="form-actions m-t-0 text-center">
        <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info">Actualizar</button>
    </div>
    {!! Form::close() !!}
</x-row-card>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
    $('.money').inputmask("pesos");
    $('.notify_day').inputmask('numeric',{min:1, max:31});
});

function readFile(input) {
    let label = $(input).next('label.custom-file-label')
    if (input.files && input.files[0]) {
        let reader = new FileReader();                
        reader.onload = function (e) {
            $('#player-img').attr('src', e.target.result);
        }                
        reader.readAsDataURL(input.files[0]);
        // label.empty().html(input.files[0].name)
        label.empty().html('Seleccionada.')
    }else{
        label.empty().html("Seleccionar...")
        $('#player-img').attr('src', 'https://golapp.softdreamc.com/img/ballon.png');                
    }
}
$('#file-upload').on('change', function(){
    readFile(this);
});
</script>
@endsection