@extends('layouts.app')

@section('title', 'Comparativo de evaluaciones')

@section('content')
<div class="mb-3">
    <h2 class="mb-0">Comparativo entre períodos</h2>
    <small class="text-muted">Evolución del jugador entre dos evaluaciones</small>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('player-evaluations.comparison') }}">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Inscripción</label>
                    <select name="inscription_id" class="form-control" required>
                        <option value="">Seleccione</option>
                        @foreach($inscriptions as $inscription)
                            <option value="{{ $inscription->id }}" {{ request('inscription_id') == $inscription->id ? 'selected' : '' }}>
                                #{{ $inscription->id }} -
                                {{ $inscription->player?->full_names ?? $inscription->player?->full_name ?? $inscription->player?->name ?? 'Jugador' }}
                                @if($inscription->trainingGroup?->name)
                                    - {{ $inscription->trainingGroup->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Período A</label>
                    <select name="period_a_id" class="form-control" required>
                        <option value="">Seleccione</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ request('period_a_id') == $period->id ? 'selected' : '' }}>
                                {{ $period->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Período B</label>
                    <select name="period_b_id" class="form-control" required>
                        <option value="">Seleccione</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ request('period_b_id') == $period->id ? 'selected' : '' }}>
                                {{ $period->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                @if(request()->filled(['inscription_id', 'period_a_id', 'period_b_id']))
                    <a href="{{ route('player-evaluations.comparison.pdf', request()->query()) }}" target="_blank" class="btn btn-dark mr-2">
                        PDF
                    </a>
                @endif

                <button type="submit" class="btn btn-primary">Comparar</button>
            </div>
        </form>
    </div>
</div>

@if(!empty($comparison))
    @php
        $overallA = data_get($comparison, 'overall.period_a_score');
        $overallB = data_get($comparison, 'overall.period_b_score');
        $delta = data_get($comparison, 'overall.delta');
        $trend = data_get($comparison, 'overall.trend', 'neutral');
        $badge = $trend === 'up' ? 'success' : ($trend === 'down' ? 'danger' : 'secondary');
    @endphp

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Jugador</h6>
                    <h5 class="mb-0">{{ data_get($comparison, 'player.name', '—') }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-left-info shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Período A</h6>
                    <h5 class="mb-0">{{ data_get($comparison, 'period_a.period_name', '—') }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-left-success shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-muted">Período B</h6>
                    <h5 class="mb-0">{{ data_get($comparison, 'period_b.period_name', '—') }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Resultado general</h5>
            <span class="badge badge-{{ $badge }} p-2">
                Variación:
                @if($delta !== null)
                    {{ $delta > 0 ? '+' : '' }}{{ number_format((float) $delta, 2) }}
                @else
                    —
                @endif
            </span>
        </div>

        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <div class="text-muted">Período A</div>
                        <h3>{{ $overallA !== null ? number_format((float) $overallA, 2) : '—' }}</h3>
                    </div>
                </div>

                <div class="col-md-4 d-flex align-items-center justify-content-center">
                    <h2 class="mb-0">→</h2>
                </div>

                <div class="col-md-4">
                    <div class="border rounded p-3">
                        <div class="text-muted">Período B</div>
                        <h3>{{ $overallB !== null ? number_format((float) $overallB, 2) : '—' }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Comparativo por dimensión</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Dimensión</th>
                        <th>Período A</th>
                        <th>Período B</th>
                        <th>Delta</th>
                        <th>Tendencia</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(data_get($comparison, 'dimensions', []) as $item)
                        @php
                            $trendItem = data_get($item, 'trend', 'neutral');
                            $trendClass = $trendItem === 'up' ? 'success' : ($trendItem === 'down' ? 'danger' : 'secondary');
                        @endphp
                        <tr>
                            <td>{{ data_get($item, 'dimension', '—') }}</td>
                            <td>{{ data_get($item, 'period_a_score') !== null ? number_format((float) data_get($item, 'period_a_score'), 2) : '—' }}</td>
                            <td>{{ data_get($item, 'period_b_score') !== null ? number_format((float) data_get($item, 'period_b_score'), 2) : '—' }}</td>
                            <td>
                                {{ data_get($item, 'delta') !== null ? number_format((float) data_get($item, 'delta'), 2) : '—' }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $trendClass }}">
                                    {{ $trendItem }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">
                                No hay información por dimensión.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Comparativo por criterio</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Dimensión</th>
                        <th>Criterio</th>
                        <th>Período A</th>
                        <th>Período B</th>
                        <th>Delta</th>
                        <th>Comentario A</th>
                        <th>Comentario B</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(data_get($comparison, 'criteria', []) as $item)
                        <tr>
                            <td>{{ data_get($item, 'dimension', '—') }}</td>
                            <td>{{ data_get($item, 'criterion', '—') }}</td>
                            <td>{{ data_get($item, 'period_a_score') !== null ? number_format((float) data_get($item, 'period_a_score'), 2) : '—' }}</td>
                            <td>{{ data_get($item, 'period_b_score') !== null ? number_format((float) data_get($item, 'period_b_score'), 2) : '—' }}</td>
                            <td>{{ data_get($item, 'delta') !== null ? number_format((float) data_get($item, 'delta'), 2) : '—' }}</td>
                            <td>{{ data_get($item, 'period_a_comment') ?: '—' }}</td>
                            <td>{{ data_get($item, 'period_b_comment') ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                No hay información por criterio.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Comentarios período A</h5>
                </div>
                <div class="card-body">
                    <p><strong>Comentario general:</strong><br>{{ data_get($comparison, 'comments.period_a.general_comment') ?: '—' }}</p>
                    <p><strong>Fortalezas:</strong><br>{{ data_get($comparison, 'comments.period_a.strengths') ?: '—' }}</p>
                    <p><strong>Oportunidades de mejora:</strong><br>{{ data_get($comparison, 'comments.period_a.improvement_opportunities') ?: '—' }}</p>
                    <p class="mb-0"><strong>Recomendaciones:</strong><br>{{ data_get($comparison, 'comments.period_a.recommendations') ?: '—' }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Comentarios período B</h5>
                </div>
                <div class="card-body">
                    <p><strong>Comentario general:</strong><br>{{ data_get($comparison, 'comments.period_b.general_comment') ?: '—' }}</p>
                    <p><strong>Fortalezas:</strong><br>{{ data_get($comparison, 'comments.period_b.strengths') ?: '—' }}</p>
                    <p><strong>Oportunidades de mejora:</strong><br>{{ data_get($comparison, 'comments.period_b.improvement_opportunities') ?: '—' }}</p>
                    <p class="mb-0"><strong>Recomendaciones:</strong><br>{{ data_get($comparison, 'comments.period_b.recommendations') ?: '—' }}</p>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection