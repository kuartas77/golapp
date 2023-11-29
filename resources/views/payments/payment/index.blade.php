@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Pagos" :option="0"/>
    <x-row-card col-inside="12" >
        @include('payments.payment.form')
        <hr>
        <h6 class="card-subtitle text-themecolor m-b-0 m-t-0" id="group_name"></h6>
        @include('payments.payment.table')
    </x-row-card >
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}';
        let inscription_amount = '{{$inscription_amount}}'
        let monthly_payment = '{{$monthly_payment}}'
        let annuity = '{{$annuity}}'
        $(function () {
            $(".preloader").fadeOut()
        })
    </script>
    <script src="{{mix('js/payments.js')}}"></script>
@endsection
