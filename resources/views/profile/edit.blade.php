@extends('layouts.app')
@section('title', "Perfil {$profile->user->name}")
@section('content')
<x-bread-crumb title="Perfil {{$profile->user->name}}" :option="0"/>
<x-row-card col-inside="8" col-outside="2" >
    <form action="{{$profile->url_update}}" id="form_create" class="form-material" method="POST">
        @method('PUT')
        @csrf
        <div class="form-body">
            @include('profile.fields')
        </div>

        <div class="form-actions text-center">
            <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info">Guardar
            </button>
        </div>

    </form>
</x-row-card>
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>
        let limitDate = moment().subtract(18,'years');
        optionsDateTimePicker.timePicker24Hour = false;
        optionsDateTimePicker.locale.format = 'YYYY-MM-DD';
        optionsDateTimePicker.timePicker = false;
        optionsDateTimePicker.autoUpdateInput = false;
        optionsDateTimePicker.startDate = limitDate.startOf('month').format('YYYY-MM-DD');
        optionsDateTimePicker.maxDate = limitDate.endOf('month').format('YYYY-MM-DD');

        $(document).ready(function () {
            $('.date').inputmask("yyyy-mm-dd");

            $('#date_birth').daterangepicker(optionsDateTimePicker).on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
        });
    </script>
@endsection
