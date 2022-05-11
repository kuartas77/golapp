@extends('layouts.app')
@section('title', 'Días de Entrenamiento')
@section('content')
    <x-bread-crumb title="Días de Entrenamiento" :option="0"/>
    <x-row-card col-inside="12" >
        @include('day.table')
    </x-row-card >
@endsection
@section('modals')
    @include('modals.create_day')
@endsection
@section('scripts')
    <script>
        let url_current = "{{URL::current()}}/";
        let url_enabled = "{{route('days.enabled')}}";

        function forceKeyPressUppercase(e)
        {
            var charInput = e.keyCode;
            if((charInput >= 97) && (charInput <= 122)) { // lowercase
            if(!e.ctrlKey && !e.metaKey && !e.altKey) { // no modifier key
                var newChar = charInput - 32;
                var start = e.target.selectionStart;
                var end = e.target.selectionEnd;
                e.target.value = e.target.value.substring(0, start) + String.fromCharCode(newChar) + e.target.value.substring(end);
                e.target.setSelectionRange(start+1, start+1);
                e.preventDefault();
            }
            }
        }
    </script>
    <script src="{{mix('js/day_schedules.js')}}"></script>
@endsection

