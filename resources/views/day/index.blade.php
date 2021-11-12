@extends('layouts.app')
@section('title', 'Días de Entrenamiento')
@section('content')
    @include('templates.bread_crumb', ['title' => 'Días de Entrenamiento', 'option' => 0])
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('day.table')
                </div>
            </div>
        </div>
    </div>
@endsection
@section('modals')
    @include('modals.create_day')
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}/';
        let url_enabled = '{{route('days.enabled')}}';
    </script>
    <script src="{{mix('js/day_schedules.js')}}"></script>
@endsection

