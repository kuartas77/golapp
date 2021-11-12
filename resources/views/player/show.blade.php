@extends('layouts.app')
@section('title', __('messages.player_title', ['unique_code'=> $player->unique_code]))
@section('content')
    @include('templates.bread_crumb', ['title' => __('messages.player_title', ['unique_code'=> $player->unique_code]), 'option' => 0])
    <div class="row">

        @include('player.show.card_person')
        @include('player.show.card_info')

    </div>

@endsection
@section('scripts')
    <script>
        $(function () {
           $(".preloader").fadeOut()
        })
    </script>
@endsection
