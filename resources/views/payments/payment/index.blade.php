@extends('layouts.app')
@section('title', 'Pagos')
@section('content')
    @include('templates.bread_crumb', ['title' => 'Pagos', 'option' => 0])
    <div class="row">
        <div class="col-2"></div>
        <div class="col-8">
            <div class="card">
                <div class="card-body">

                    <h6 class="card-subtitle">Se puede buscar por cualquiera de los parametros, el año de busqueda será el actual.</h6>

                    @include('payments.payment.form')

                </div>
            </div>
        </div>
        <div class="col-2"></div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('payments.payment.table')
                </div>
            </div>
        </div>
    </div>
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
