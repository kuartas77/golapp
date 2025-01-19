@extends('layouts.portal.public')
@section('title', $school->name)
@push('css')
@endpush
@section('content')
<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-1"></div>
            @include('portal.schools.show.card_school')
            @include('portal.schools.show.card_info')
            <div class="col-md-1"></div>
        </div>
    </div>
</div>
@endsection
@section('modals')
@include('modals.portal_inscription_register')
@endsection
@section('scripts')
<script>

</script>
@endsection