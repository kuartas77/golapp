@extends('layouts.app')
@section('title', __('messages.inscriptions_title'))
@section('content')
    <x-bread-crumb :title="__('messages.inscriptions_title')" :option="0"/>
    <x-row-card col-inside="12" >
        @include('inscription.table')
    </x-row-card >
@endsection
@section('modals')
    @include('modals.create_inscription')
@endsection
@section('scripts')
    <script>
        const currentYear = {{$year}}
        let yearSelected = currentYear
        const firstGroup = {{$provisional_group_id}}
        const isAdmin = {{auth()->user()->hasAnyRole(['super-admin','school']) ? 1 : 0}};
        const groups = @json($training_groups_arr);
        const categories = @json($categories);
        const url_inscriptions_enabled = "{{ route('inscriptions.enabled') }}";
        const url_inscriptions_disabled = "{{ route('inscriptions.disabled') }}";
    </script>
    <script src="{{ mix('js/inscriptions.js') }}" ></script>
@endsection
