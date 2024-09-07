@extends('layouts.app')
@section('title', 'Horarios')
@section('content')
<x-bread-crumb title="Informe De Pagos" :option="0"/>
<x-row-card col-inside="8" col-outside="2" >
    @include('reports.payments.form')
    <hr>
    <div class="col-md-12 text-center">
        <h6 class="help-block">Puedes exportar un informe por año ó por año y grupo. Este será enviado al correo que tienes registrado.</h6>
    </div>
</x-row-card >
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}';

        $(function () { $(".preloader").fadeOut()})

        $('#year').select2({placeholder:'Seleccione...'});
        $('#training_group_id').select2({placeholder:'Seleccione...',allowClear: true});

        $("#year").on('change', function (e) {
            let training_group_id = e.target.value;
            $.get(`${url_current}?year=${training_group_id}`, function (data) {
                $("#training_group_id").empty();
                $("#training_group_id").select2({data: data});
            });
        });

        $("#form_report_assists").validate({
            rules: {
                year: {required: true},
            },
            messages: {
                'year': {required: "Seleccione un año."},
            }, submitHandler(form) {
                form.submit();
            }
        });
    </script>
@endsection
