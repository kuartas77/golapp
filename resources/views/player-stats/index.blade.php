@extends('layouts.app')

@section('title', 'Estadísticas de Jugadores')

@section('content')
<x-bread-crumb title="📊 Estadísticas de Jugadores y escalafón" :option="0" />

<x-row-card col-inside="12">
    <div class="row">

        <div class=" col-md-6">
            <form method="GET" class="row  g-3">
                <div class="col-md-3">
                    <label for="position" class="form-label">Posición</label>
                    <select name="position" id="position" class="form-control form-control-sm">
                        <option value="">Todas las posiciones</option>
                        @foreach($positions as $key => $label)
                        <option value="{{ $key }}" {{ request('position') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 d-flex align-items-end text-end">
                    <div class="btn-group w-100">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('player.stats') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-redo"></i> Limpiar
                        </a>
                         <a href="{{ route('players.top') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list-ol"></i> Ver destacados
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <!-- Información del sistema de puntuación -->
        <div class="col-md-6">

            <div class="alert alert-info mb-4">
                <div class="d-flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle fa-2x"></i>
                    </div>
                    <div class="ms-3">
                        <h6 class="alert-heading mb-2">📈 Sistema de Puntuación del Escalafón</h6>
                        <p class="mb-1 small">
                            <strong>Gol:</strong> 10 puntos •
                            <strong>Asistencia de gol:</strong> 7 puntos •
                            <strong>Atajada:</strong> 5 puntos •
                            <strong>Calificación (0-5):</strong> ×3 •
                            <strong>Minuto jugado:</strong> 0.1 •
                            <strong>Titular:</strong> 3 puntos •
                            <strong>Amarilla:</strong> -2 punto •
                            <strong>Roja:</strong> -5 puntos
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-row-card>
<x-row-card col-inside="12">



    <!-- Tabla principal de estadísticas -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-trophy"></i> Escalafón de Jugadores
                </h5>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-striped table-bordered table-sm mb-0">
                <thead class="">
                    <tr>
                        <th>#</th>
                        <th>Jugador</th>
                        <th class="text-center">Posición</th>
                        <th class="text-center" title="Partidos Jugados">PJ</th>
                        <th class="text-center" title="Goles">⚽ G</th>
                        <th class="text-center" title="Asistencias Gol">🎯 A</th>
                        <th class="text-center" title="Atajadas">🧤 ATA</th>
                        <th class="text-center" title="Tarjetas Amarillas">🟨 AM</th>
                        <th class="text-center" title="Tarjetas Rojas">🟥 RO</th>
                        <th class="text-center" title="Minutos Jugados">⏱️ MIN</th>
                        <th class="text-center" title="Calificación Promedio">⭐ CAL</th>
                        <th class="text-center" title="Puntaje Total">🏆 PTS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($players as $index => $player)
                    <tr class="align-middle">
                        <td class="text-center">
                            <strong class="text-muted">#{{ $loop->iteration }}</strong>
                        </td>

                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar me-3">
                                    @if($player->photo)
                                    <img src="{{ $player->photo }}"
                                        class="rounded-circle border"
                                        width="45" height="45"
                                        alt="{{ $player->player_name }}">
                                    @else
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                        style="width: 45px; height: 45px;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                    @endif
                                </div>
                                <div class="ml-2">
                                    <a href="{{ route('player.detail', $player->player_id) }}"
                                        class="text-decoration-none fw-bold">
                                        {{ $player->player_name }}
                                    </a>
                                    <div class="small text-muted">
                                        {{ $player->asistencias_partidos }} asist. / {{ $player->veces_titular }} tit.
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="text-center">
                            @if($player->posicion_principal)
                            <span class="position-badge"
                                data-bs-toggle="tooltip"
                                title="Posición más frecuente">
                                {{ $player->posicion_principal }}
                            </span>
                            @else
                            <span class="">Variada</span>
                            @endif
                        </td>

                        <td class="text-center fw-bold">{{ $player->total_partidos }}</td>

                        <td class="text-center">
                            <span class=" text-success">
                                <strong>{{ $player->total_goles }}</strong>
                                @if($player->total_partidos > 0)
                                <br>
                                <small class="text-muted">({{ $player->promedio_goles_partido }}/partido)</small>
                                @endif
                            </span>
                        </td>

                        <td class="text-center">
                            @if($player->total_asistencias_gol > 0)
                            <span class="text-info">
                                {{ $player->total_asistencias_gol }}
                            </span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($player->total_atajadas > 0)
                            <span class="text-info">
                                {{ $player->total_atajadas }}
                            </span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($player->total_amarillas > 0)
                            <span class="text-warning">
                                {{ $player->total_amarillas }}
                            </span>
                            @else
                            <span class="text-success">0</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if($player->total_rojas > 0)
                            <span class="text-danger">
                                {{ $player->total_rojas }}
                            </span>
                            @else
                            <span class="text-success">0</span>
                            @endif
                        </td>

                        <td class="text-center">
                            <div class="small">
                                <div>{{ number_format($player->minutos_jugados) }}'</div>
                                <div class="text-muted">({{ $player->promedio_minutos_partido }}/partido)</div>
                            </div>
                        </td>

                        <td class="text-center">
                            @php
                            $rating = $player->promedio_calificacion;
                            $ratingClass = $rating >= 4 ? 'bg-success' : ($rating >= 3 ? 'bg-warning' : 'bg-danger');
                            $ratingIcon = $rating >= 4 ? 'fas fa-star' : ($rating >= 3 ? 'fas fa-star-half-alt' : 'far fa-star');
                            @endphp
                            <div class=" mx-auto {{ $ratingClass }}"
                                data-bs-toggle="tooltip"
                                title="Calificación promedio">
                                <i class="{{ $ratingIcon }}"></i>
                                <span class="rating-number">{{ $rating }}</span>
                            </div>
                        </td>

                        <td class="text-center">
                            <div class="score-display">
                                <span class="fs-6">
                                    {{ number_format($player->puntaje_escalafon, 1) }}
                                </span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-users fa-2x mb-3"></i>
                                <h5>No hay jugadores con estadísticas</h5>
                                <p>Comienza a registrar partidos para ver las estadísticas aquí.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


    </div>

    <!-- Estadísticas rápidas -->
    @if($players->count() > 0)
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="card-title text-success">
                        <i class="fas fa-futbol"></i> Máximo Goleador
                    </h6>
                    @php $topScorer = $players->sortByDesc('total_goles')->first(); @endphp
                    <h4 class="mb-1">{{ $topScorer->player_name ?? '-' }}</h4>
                    <div class="text-muted small">
                        {{ $topScorer->total_goles ?? 0 }} goles
                        ({{ $topScorer->promedio_goles_partido ?? 0 }}/partido)
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="card-title text-primary">
                        <i class="fas fa-handshake"></i> Mejor Asistente
                    </h6>
                    @php $topAssister = $players->sortByDesc('total_asistencias_gol')->where('total_asistencias_gol', '>', 0)->first(); @endphp
                    <h4 class="mb-1">{{ $topAssister->player_name ?? '-' }}</h4>
                    <div class="text-muted small">
                        {{ $topAssister->total_asistencias_gol ?? 0 }} asistencias
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h6 class="card-title text-warning">
                        <i class="fas fa-star"></i> Mejor Calificación
                    </h6>
                    @php $topRated = $players->sortByDesc('promedio_calificacion')->first(); @endphp
                    <h4 class="mb-1">{{ $topRated->player_name ?? '-' }}</h4>
                    <div class="text-muted small">
                        Calificación: {{ $topRated->promedio_calificacion ?? 0 }}/10
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h6 class="card-title text-info">
                        <i class="fas fa-chart-line"></i> Mejor Puntuación
                    </h6>
                    <h4 class="mb-1">{{ $players->first()->player_name ?? '-' }}</h4>
                    <div class="text-muted small">
                        {{ number_format($players->first()->puntaje_escalafon ?? 0, 1) }} puntos
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</x-row-card>
@endsection

@push('styles')
<style>
    .position-badge {
        font-size: 0.75rem;
        max-width: 120px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .rating-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        position: relative;
    }

    .rating-circle .rating-number {
        /* font-size: 0.9rem; */
        font-weight: bold;
    }

    .rating-circle i {
        font-size: 0.8rem;
        margin-bottom: 2px;
    }

    .score-display .badge {
        min-width: 70px;
        padding: 0.5rem 0.75rem;
    }

    /* .table th {
        font-size: 0.85rem;
        white-space: nowrap;
    }

    .table td {
        vertical-align: middle;
    }

    .card-header h5 {
        font-size: 1.1rem;
    } */
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Inicializar tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Exportar a Excel
        $('#export-excel').click(function() {
            // Implementar exportación a Excel
            alert('Funcionalidad de exportación en desarrollo');
        });

        // Buscador rápido
        $('#search-player').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Ordenar por columnas
        $('.sortable').click(function() {
            var column = $(this).data('column');
            var direction = $(this).data('direction') === 'asc' ? 'desc' : 'asc';

            // Actualizar iconos
            $('.sortable i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
            $(this).find('i')
                .removeClass('fa-sort')
                .addClass(direction === 'asc' ? 'fa-sort-up' : 'fa-sort-down');

            $(this).data('direction', direction);

            // Redirigir con parámetros de ordenación
            var url = new URL(window.location.href);
            url.searchParams.set('sort', column);
            url.searchParams.set('order', direction);
            window.location.href = url.toString();
        });
    });
</script>
@endpush