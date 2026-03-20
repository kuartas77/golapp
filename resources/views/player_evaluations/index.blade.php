@extends('layouts.app')

@section('title', 'Evaluaciones')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="mb-0">Evaluaciones</h2>
        <small class="text-muted">Listado de evaluaciones del jugador</small>
    </div>
    <div>
        <a href="{{ route('player-evaluations.comparison') }}" class="btn btn-outline-info">
            Comparativo
        </a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('player-evaluations.index') }}">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Jugador</label>
                    <select name="player_id" class="form-control">
                        <option value="">Todos</option>
                        @foreach($players as $player)
                            <option value="{{ $player->id }}" {{ request('player_id') == $player->id ? 'selected' : '' }}>
                                {{ $player->full_names ?? $player->full_name ?? $player->name ?? ('Jugador #' . $player->id) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label>Grupo</label>
                    <select name="training_group_id" class="form-control">
                        <option value="">Todos</option>
                        @foreach($trainingGroups as $group)
                            <option value="{{ $group->id }}" {{ request('training_group_id') == $group->id ? 'selected' : '' }}>
                                {{ $group->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label>Período</label>
                    <select name="evaluation_period_id" class="form-control">
                        <option value="">Todos</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}" {{ request('evaluation_period_id') == $period->id ? 'selected' : '' }}>
                                {{ $period->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label>Estado</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completada</option>
                        <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Cerrada</option>
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label>Tipo</label>
                    <select name="evaluation_type" class="form-control">
                        <option value="">Todos</option>
                        <option value="initial" {{ request('evaluation_type') === 'initial' ? 'selected' : '' }}>Inicial</option>
                        <option value="periodic" {{ request('evaluation_type') === 'periodic' ? 'selected' : '' }}>Periódica</option>
                        <option value="final" {{ request('evaluation_type') === 'final' ? 'selected' : '' }}>Final</option>
                        <option value="special" {{ request('evaluation_type') === 'special' ? 'selected' : '' }}>Especial</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <a href="{{ route('player-evaluations.index') }}" class="btn btn-light mr-2">Limpiar</a>
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Jugador</th>
                    <th>Grupo</th>
                    <th>Período</th>
                    <th>Plantilla</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Nota general</th>
                    <th>Fecha evaluación</th>
                    <th width="210">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($evaluations as $evaluation)
                    <tr>
                        <td>{{ $evaluation->id }}</td>
                        <td>
                            {{ optional(optional($evaluation->inscription)->player)->full_names
                                ?? optional(optional($evaluation->inscription)->player)->full_name
                                ?? optional(optional($evaluation->inscription)->player)->name
                                ?? '—' }}
                        </td>
                        <td>{{ optional(optional($evaluation->inscription)->trainingGroup)->name ?? '—' }}</td>
                        <td>{{ optional($evaluation->period)->name ?? '—' }}</td>
                        <td>{{ optional($evaluation->template)->name ?? '—' }}</td>
                        <td>{{ ucfirst($evaluation->evaluation_type ?? '—') }}</td>
                        <td>
                            @php
                                $statusClass = 'secondary';
                                if ($evaluation->status === 'completed') $statusClass = 'success';
                                if ($evaluation->status === 'closed') $statusClass = 'dark';
                            @endphp
                            <span class="badge badge-{{ $statusClass }}">
                                {{ $evaluation->status ?? '—' }}
                            </span>
                        </td>
                        <td>
                            {{ $evaluation->overall_score !== null ? number_format($evaluation->overall_score, 2) : '—' }}
                        </td>
                        <td>
                            {{ optional($evaluation->evaluated_at)->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td>
                            <a href="{{ route('player-evaluations.show', $evaluation->id) }}" class="btn btn-sm btn-info">
                                Ver
                            </a>
                            <a href="{{ route('player-evaluations.edit', $evaluation->id) }}" class="btn btn-sm btn-warning">
                                Editar
                            </a>
                            <a href="{{ route('player-evaluations.pdf', $evaluation->id) }}" class="btn btn-sm btn-dark" target="_blank">
                                PDF
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            No hay evaluaciones registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($evaluations, 'links'))
        <div class="card-body">
            {{ $evaluations->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection