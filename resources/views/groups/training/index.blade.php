@extends('layouts.app')
@section('title', 'Grupos Entrenamiento')
@section('content')
    <x-bread-crumb title="Grupos Entrenamiento" :option="0"/>
    <x-row-card col-inside="12" >
        @include('groups.training.table')
    </x-row-card >
@endsection
@section('modals')
    @include('modals.create_training_groups')
@endsection
@section('scripts')
    <script>
        let url_current = "{{URL::current()}}/";
        let url_enabled = "{{route('training_groups.enabled')}}";
        let url_disabled = "{{route('training_groups.retired')}}";
    </script>
    <script src="{{asset('js/trainingGroupIndex.js')}}" defer></script>
@endsection

