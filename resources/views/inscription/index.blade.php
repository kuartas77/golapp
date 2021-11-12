@extends('layouts.app')
@section('title', __('messages.inscriptions_title'))
@section('content')
    @include('templates.bread_crumb', ['title' => __('messages.inscriptions_title'), 'option' => 0])
    <div class="row">
        <div class="card col-lg-12">
            <div class="card-body">
                @include('inscription.table')
            </div>
        </div>
    </div>
    </div>
@endsection
@section('modals')
    @include('modals.create_inscription')
@endsection
@section('scripts')
    <script>
        const isAdmin = {{auth()->user()->hasRole('administrador') ? 1 : 0}};
        const url_inscriptions_enabled = '{{ route('inscriptions.enabled') }}';
    </script>
    <script src="{{ mix('js/inscriptions.js') }}" ></script>
@endsection
