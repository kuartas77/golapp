@extends('layouts.app')
@section('title', 'Informe De Deudores')
@section('content')
<x-bread-crumb title="Informe De Deudores" :option="0"/>
<x-row-card col-inside="8" col-outside="2" >
    @include('reports.debtors.form')
    <hr>
    <div class="col-md-12 text-center">
        <h6 class="help-block">Exporta en PDF el consolidado de deportistas con deudas de mensualidades y facturas.</h6>
    </div>
</x-row-card >
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}';

        $(function () { $(".preloader").fadeOut()})

        $('#year').select2({placeholder:'Seleccione...'});
        $('#training_group_id').select2({placeholder:'Todos los grupos', allowClear: true});

        $("#year").on('change', function (e) {
            let year = e.target.value;
            $.get(`${url_current}?year=${year}`, function (data) {
                $("#training_group_id").empty();
                $("#training_group_id").select2({data: data, placeholder:'Todos los grupos', allowClear: true});
            });
        });

        $("#form_report_debtors").validate({
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
