@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Pagos" :option="0"/>
    <x-row-card col-outside="3" col-inside="6" >
        <h6 class="card-subtitle">El año de busqueda será el actual.</h6>
        @include('payments.payment.form')
    </x-row-card >
    <x-row-card col-inside="12" >
        <h6 class="card-subtitle text-themecolor m-b-0 m-t-0" id="group_name"></h6>
        @include('payments.payment.table')
    </x-row-card >
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}';
        $(function () {
            $(".preloader").fadeOut()
        })
    </script>
    <script src="{{mix('js/payments.js')}}"></script>
@endsection
