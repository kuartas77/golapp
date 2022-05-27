@extends('layouts.app')
@section('title', 'Perfil')
@section('content')
<x-bread-crumb title="Perfil {{$profile->user->name}}" :option="0" />
<div class="row">
    @include('profile.fields.personal_info')
    @include('profile.fields.additional')
</div>
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>
        $(function () {
            $(".preloader").fadeOut()
            // $('[data-toggle="tooltip"]').tooltip('show')
        })
    </script>
@endsection
