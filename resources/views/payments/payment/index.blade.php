@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Mensualidades" :option="0"/>
    <x-row-card col-inside="12" >
        @include('payments.payment.form')
        <hr>
        <h6 class="card-subtitle text-themecolor m-b-0 m-t-0" id="group_name"></h6>
        @include('payments.payment.table')
    </x-row-card >
@endsection
@section('modals')
    @include('modals.modify_payments')
@endsection
@section('scripts')
    <script>
        const url_current = '{{URL::current()}}';
        const inscription_amount = '{{$inscription_amount}}'
        const monthly_payment = '{{$monthly_payment}}'
        const annuity = '{{$annuity}}'
        $(function () {
            $(".preloader").fadeOut()
        })
    </script>
    @if($enabledPaymentOld)
    <script src="{{asset('js/payments.js')}}"></script>
    @else
    <script src="{{asset('js/payments_modal.js')}}"></script>
    @endif
@endsection
