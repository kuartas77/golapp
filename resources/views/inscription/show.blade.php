@extends('layouts.app')
@section('title', __('messages.inscription_title', ['unique_code'=> $inscription->unique_code]))
@section('content')
    @include('templates.bread_crumb', ['title' => __('messages.inscription_title', ['unique_code'=> $inscription->unique_code]), 'option' => 0])
    <div class="row">
        @include('inscription.profile.card_person')
        @include('inscription.profile.card_info')
    </div>
@endsection
@section('modals')
@endsection
@section('scripts')
    <script></script>
@endsection
