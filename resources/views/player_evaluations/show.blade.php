@extends('layouts.app')

@section('title', 'Detalle de evaluación')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">Evaluación #{{ $playerEvaluation->id }}</h2>
        <small class="text-muted">Detalle completo de la evaluación</small>
    </div>
    <div class="d-flex">
        <a href="{{ route('player-evaluations.edit', $playerEvaluation->id) }}" class="btn btn-warning mr-2">
            Editar
        </a>
        <a href="{{ route('player-evaluations.pdf', $playerEvaluation->id) }}" target="_blank" class="btn btn-dark mr-2">
            PDF
        </a>
        <a href="{{ route('player-evaluations.index') }}" class="btn btn-light">
            Volver
        </a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="font-weight-bold">Jugador</label>
                <div>
                    {{ $playerEvaluation->inscription?->player?->full_names
                        ?? $playerEvaluation->inscription?->player?->full_name
                        ?? $playerEvaluation->inscription?->player?->name
                        ?? '—' }}
                </div>
            </div>

            <div class="col-md-3">
                <label class="font-weight-bold">Grupo</label>
                <div>{{ $playerEvaluation->inscription?->trainingGroup?->name ?? '—' }}</div>
            </div>

            <div class="col-md-2">
                <label class="font-weight-bold">Período</label>
                <div>{{ $playerEvaluation->period?->name ?? '—' }}</div>
            </div>

            <div class="col-md-2">
                <label class="font-weight-bold">Plantilla</label>
                <div>{{ $playerEvaluation->template?->name ?? '—' }}</div>
            </div>

            <div class="col-md-2">
                <label class="font-weight-bold">Nota general</label>
                <div>
                    @if($playerEvaluation->overall_score !== null)
                        <span class="badge badge-success p-2">{{ number_format($playerEvaluation->overall_score, 2) }}</span>
                    @else
                        —
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <label class="font-weight-bold">Tipo</label>
                <div>{{ ucfirst($playerEvaluation->evaluation_type ?? '—') }}</div>
            </div>

            <div class="col-md-3">
                <label class="font-weight-bold">Estado</label>
                <div>{{ $playerEvaluation->status ?? '—' }}</div>
            </div>

            <div class="col-md-3">
                <label class="font-weight-bold">Fecha evaluación</label>
                <div>{{ optional($playerEvaluation->evaluated_at)->format('d/m/Y H:i') ?? '—' }}</div>
            </div>

            <div class="col-md-3">
                <label class="font-weight-bold">Evaluador</label>
                <div>{{ $playerEvaluation->evaluator?->name ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

@forelse($scoresByDimension as $dimension => $scores)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">{{ $dimension }}</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Criterio</th>
                        <th width="120">Puntaje</th>
                        <th width="140">Escala</th>
                        <th>Comentario</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($scores as $score)
                        <tr>
                            <td>{{ $score->criterion?->name ?? '—' }}</td>
                            <td>{{ $score->score !== null ? number_format($score->score, 2) : '—' }}</td>
                            <td>{{ $score->scale_value ?: '—' }}</td>
                            <td>{{ $score->comment ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="alert alert-light border">
        No hay puntajes registrados para esta evaluación.
    </div>
@endforelse

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Conclusiones</h5>
    </div>
    <div class="card-body">
        <div class="mb-3">
            <label class="font-weight-bold">Comentario general</label>
            <p class="mb-0">{{ $playerEvaluation->general_comment ?: '—' }}</p>
        </div>

        <div class="mb-3">
            <label class="font-weight-bold">Fortalezas</label>
            <p class="mb-0">{{ $playerEvaluation->strengths ?: '—' }}</p>
        </div>

        <div class="mb-3">
            <label class="font-weight-bold">Oportunidades de mejora</label>
            <p class="mb-0">{{ $playerEvaluation->improvement_opportunities ?: '—' }}</p>
        </div>

        <div class="mb-0">
            <label class="font-weight-bold">Recomendaciones</label>
            <p class="mb-0">{{ $playerEvaluation->recommendations ?: '—' }}</p>
        </div>
    </div>
</div>

@if($playerEvaluation->status !== 'closed')
    <div class="card shadow-sm">
        <div class="card-body d-flex justify-content-end">
            <form action="{{ route('player-evaluations.destroy', $playerEvaluation->id) }}" method="POST" onsubmit="return confirm('¿Deseas eliminar esta evaluación?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    Eliminar evaluación
                </button>
            </form>
        </div>
    </div>
@endif
@endsection