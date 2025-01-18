@extends('layouts.portal.public')
@section('title', $school->name)
@push('css')
@endpush
@section('content')
    {{$school->name}}
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>

    </script>
@endsection