@extends('layouts.app')
@section('title', 'Grupos Entrenamiento')
@section('content')
    @include('templates.bread_crumb', ['title' => 'Grupos Entrenamiento', 'option' => 0])
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('groups.training.table')
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modals')
    @include('modals.create_training_groups')
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}/';
        let url_days = '{{route('days.index')}}/';
        let url_enabled = '{{route('training_groups.enabled')}}';
        let url_disabled = '{{route('training_groups.retired')}}';
    </script>
    <script src="{{asset('js/app/trainingGroupIndex.js')}}" defer></script>
@endsection

