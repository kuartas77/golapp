@extends('layouts.app')
@section('title', "Perfil {$profile->user->name}")
@section('content')
<x-bread-crumb title="Perfil {{$profile->user->name}}" :option="0"/>
<x-row-card col-inside="12">
        {{html()->modelForm($profile, 'put', $profile->url_update)->attributes(['id'=>'form_create', 'accept-charset' => 'UTF-8', 'enctype' => "multipart/form-data", 'class'=>''])->open()}}
        <div class="form-body">
            @include('profile.fields')
        </div>

        <div class="form-actions text-center">
            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info">Guardar
            </button>
        </div>

    {{ html()->closeModelForm() }}
</x-row-card>
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>


        $(document).ready(function () {
            $('.date').inputmask("yyyy-mm-dd");

            $("#date_birth").bootstrapMaterialDatePicker({
                time: false,
                clearButton: false,
                lang: 'es',
                cancelText: 'Cancelar',
                okText: 'Aceptar',
                minDate: moment().subtract(60, 'year'),//TODO: settings
                maxDate: moment().subtract(17, 'year')// TODO: settings
            });

            $('#file-upload').on('change', function(){
                readFile(this);
            });
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
                $('#player-img').attr('src', 'http://golapp.local/img/user.png');
            }
        }
    </script>
@endsection
