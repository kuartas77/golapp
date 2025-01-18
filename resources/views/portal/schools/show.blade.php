@extends('layouts.portal.public')
@section('title', $school->name)
@push('css')
@endpush
@section('content')
<div class="container-fluid">
    <div class="row">
        @include('portal.schools.show.card_school')
    </div>
</div>
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>

    </script>
@endsection