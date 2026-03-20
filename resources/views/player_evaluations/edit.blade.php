@extends('layouts.app')

@section('title', 'Editar evaluación')

@section('content')
<div class="mb-3">
    <h2 class="mb-0">Editar evaluación #{{ $playerEvaluation->id }}</h2>
    <small class="text-muted">Actualización de evaluación</small>
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

<form action="{{ route('player-evaluations.update', $playerEvaluation->id) }}" method="POST">
    @include('player_evaluations.partials._form', ['playerEvaluation' => $playerEvaluation])
</form>
@endsection