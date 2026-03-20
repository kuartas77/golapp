@extends('layouts.app')

@section('title', 'Nueva evaluación')

@section('content')
<div class="mb-3">
    <h2 class="mb-0">Nueva evaluación</h2>
    <small class="text-muted">Registro de evaluación del jugador</small>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Revisa los siguientes errores:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('player-evaluations.store') }}" method="POST">
    @include('player_evaluations.partials._form')
</form>
@endsection