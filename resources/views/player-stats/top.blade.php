@extends('layouts.app')

@section('title', 'Jugadores Destacados')

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Encabezado -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="fas fa-crown text-warning"></i> Jugadores Destacados
            </h2>
            <p class="text-muted mb-0">
                Reconocimiento a los jugadores con mejores estadísticas en cada categoría
            </p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <a href="{{ route('player.stats') }}" class="btn btn-outline-primary">
                    <i class="fas fa-list-ol"></i> Ver Ranking
                </a>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="fas fa-print"></i> Imprimir
                </button>
            </div>
        </div>
    </div>

    <!-- Fecha de actualización -->
    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-calendar-alt me-2"></i>
            <div>
                <strong>Actualizado al:</strong> {{ now()->format('d/m/Y') }}
                <span class="mx-2">•</span>
                <strong>Temporada:</strong> {{ now()->year }}
            </div>
        </div>
    </div>

    <!-- Sección: Máximos Goleadores -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-futbol me-2"></i> Máximos Goleadores
                        </h4>
                        <span class="badge bg-light text-success fs-6">
                            {{ $topScorers->count() }} jugadores
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($topScorers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-sm table-bordered mb-0">
                                <thead class="">
                                    <tr>
                                        <th width="60" class="text-center">Pos.</th>
                                        <th>Jugador</th>
                                        <th class="text-center" width="100">Partidos</th>
                                        <th class="text-center" width="120">Total Goles</th>
                                        <th class="text-center" width="120">Promedio por Partido</th>
                                        <th class="text-center" width="120">Goles cada 90 min</th>
                                        <th class="text-center" width="100" title="Calificación promedio">⭐ Cal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topScorers as $index => $player)
                                    @php
                                        $efficiency = $player->minutos_jugados > 0
                                            ? ($player->total_goles / $player->minutos_jugados) * 90
                                            : 0;
                                    @endphp
                                    <tr class="{{ $index < 3 ? 'table-success' : '' }}">
                                        <td class="text-center fw-bold">
                                            #{{ $index + 1 }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar me-3">
                                                    @if($player->photo)
                                                    <img src="{{$player->photo}}"
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
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold">
                                            {{ $player->partidos ?? 0 }}
                                        </td>
                                        <td class="text-center">
                                            <div class="goals-badge">
                                                <span class="text-success fs-5 px-3 py-2">
                                                    {{ $player->total_goles }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="average-display">
                                                <span class="fw-bold">{{ $player->promedio_goles ?? 0 }}</span>
                                                <div class="small text-muted">goles/partido</div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="efficiency-display">
                                                <span class="text-info bg-opacity-10 text-info ">
                                                    {{ number_format($efficiency, 2) }}
                                                </span>
                                                <div class="small text-muted">cada 90'</div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="rating-small">
                                                <span class="text-warning bg-opacity-10 text-warning ">
                                                    {{ $player->promedio_calificacion ?? 0 }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-futbol fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay datos de goleadores</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Máximas Asistencias y Porteros Destacados lado a lado -->
    <div class="row mb-5">
        <!-- Máximas Asistencias -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white py-3">
                    <h4 class="mb-0">
                        <i class="fas fa-handshake me-2"></i> Máximas Asistencias
                    </h4>
                </div>
                <div class="card-body">
                    @if($topAssists->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topAssists as $index => $player)
                            <div class="list-group-item border-0 py-3 px-0 {{ $index < 3 ? 'bg-primary bg-opacity-5' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative me-3">
                                            <div class="avatar">
                                                @if($player->photo)
                                                <img src="{{ $player->photo }}"
                                                     class="rounded-circle border"
                                                     width="50" height="50"
                                                     alt="{{ $player->player_name }}">
                                                @else
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                                @endif
                                            </div>
                                            @if($index < 3)
                                            <div class="position-absolute top-0 start-100 translate-middle badge-circle">
                                                @if($index == 0)
                                                <span class="badge bg-warning">1</span>
                                                @elseif($index == 1)
                                                <span class="badge bg-secondary">2</span>
                                                @elseif($index == 2)
                                                <span class="badge bg-danger">3</span>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $player->player_name }}</h6>
                                            <small class="text-muted">
                                                {{ $player->partidos }} partidos •
                                                {{ $player->promedio_asistencias ?? 0 }} asis/partido
                                            </small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <h3 class="mb-0 text-primary">
                                            {{ $player->total_asistencias }}
                                        </h3>
                                        <small class="text-muted">asistencias</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay datos de asistencias</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Porteros Destacados -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-info text-white py-3">
                    <h4 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i> Porteros Destacados
                    </h4>
                </div>
                <div class="card-body">
                    @if($topGoalSaves->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topGoalSaves as $index => $player)
                            <div class="list-group-item border-0 py-3 px-0 {{ $index < 3 ? 'bg-info bg-opacity-5' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative me-3">
                                            <div class="avatar">
                                                @if($player->photo)
                                                <img src="{{$player->photo}}"
                                                     class="rounded-circle border"
                                                     width="50" height="50"
                                                     alt="{{ $player->player_name }}">
                                                @else
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center"
                                                     style="width: 50px; height: 50px;">
                                                    <i class="fas fa-user text-muted"></i>
                                                </div>
                                                @endif
                                            </div>
                                            @if($index < 3)
                                            <div class="position-absolute top-0 start-100 translate-middle">
                                                <span class="badge bg-info">POR</span>
                                            </div>
                                            @endif
                                        </div>
                                        <div>
                                            <h6 class="mb-1">{{ $player->player_name }}</h6>
                                            <small class="text-muted">
                                                {{ $player->partidos }} partidos •
                                                {{ $player->promedio_calificacion ?? 0 }}/5 calificación
                                            </small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <h3 class="mb-0 text-info">
                                            {{ $player->total_atajadas }}
                                        </h3>
                                        <small class="text-muted">atajadas</small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay datos de porteros</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sección: Jugadores Mejor Calificados -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-star me-2"></i> Jugadores Más Regulares
                        </h4>
                        <span class="badge bg-light text-warning">
                            Mejor calificación promedio (mín. 3 partidos)
                        </span>
                    </div>
                </div>
                <div class="card-body">
                    @if($topRated->count() > 0)
                        <div class="row">
                            @foreach($topRated as $index => $player)
                            <div class="col-md-4">
                                <div class="card border-warning shadow">
                                    <div class="card-body text-center">
                                        @if($index < 3)
                                        <div class="position-absolute top-0 start-50 translate-middle badge-circle">
                                            @if($index == 0)
                                            <span class="badge bg-warning fs-5">🥇</span>
                                            @elseif($index == 1)
                                            <span class="badge bg-secondary fs-5">🥈</span>
                                            @elseif($index == 2)
                                            <span class="badge bg-danger fs-5">🥉</span>
                                            @endif
                                        </div>
                                        @endif

                                        <div class="avatar mx-auto mb-2">
                                            @if($player->photo)
                                            <img src="{{$player->photo}}"
                                                 class="rounded-circle border-warning border-3"
                                                 width="80" height="80"
                                                 alt="{{ $player->player_name }}">
                                            @else
                                            <div class="rounded-circle bg-light d-flex align-items-center justify-content-center border-warning border-3"
                                                 style="width: 80px; height: 80px;">
                                                <i class="fas fa-user fa-2x text-muted"></i>
                                            </div>
                                            @endif
                                        </div>

                                        <h5 class="card-title mb-2">{{ $player->player_name }}</h5>

                                        <div class="rating-display mb-3">
                                            <div class="stars">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= floor($player->promedio_calificacion))
                                                        <i class="fas fa-star text-warning"></i>
                                                    @elseif($i - 0.5 <= $player->promedio_calificacion)
                                                        <i class="fas fa-star-half-alt text-warning"></i>
                                                    @else
                                                        <i class="far fa-star text-warning"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <h2 class="display-5 fw-bold text-warning my-2">
                                                {{ number_format($player->promedio_calificacion, 1) }}
                                            </h2>
                                            <small class="text-muted">/ 5.0</small>
                                        </div>

                                        <div class="stats">
                                            <div class="row">
                                                <div class="col-6">
                                                    <div class="stat-item">
                                                        <div class="stat-value">{{ $player->partidos }}</div>
                                                        <div class="stat-label small">Partidos</div>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    @if($player->goals)
                                                    <div class="stat-item">
                                                        <div class="stat-value">{{ $player->goals ?? 0 }}</div>
                                                        <div class="stat-label small">Goles</div>
                                                    </div>
                                                    @elseif($player->assists)
                                                    <div class="stat-item">
                                                        <div class="stat-value">{{ $player->assists ?? 0 }}</div>
                                                        <div class="stat-label small">Asistencias</div>
                                                    </div>
                                                    @else
                                                    <div class="stat-item">
                                                        <div class="stat-value">-</div>
                                                        <div class="stat-label small">Acciones</div>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <a href="{{ route('player.detail', $player->player_id) }}"
                                               class="btn btn-sm btn-outline-warning">
                                                <i class="fas fa-chart-line"></i> Ver estadísticas
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay datos de calificaciones</h5>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas Generales -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i> Resumen Estadístico
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <div class="stat-card">
                                <div class="stat-icon text-success">
                                    <i class="fas fa-futbol fa-2x"></i>
                                </div>
                                <h3 class="stat-value mt-2">
                                    @if($topScorers->count() > 0)
                                        {{ $topScorers->sum('total_goles') }}
                                    @else
                                        0
                                    @endif
                                </h3>
                                <p class="stat-label text-muted mb-0">Goles totales (Top 10)</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="stat-card">
                                <div class="stat-icon text-primary">
                                    <i class="fas fa-handshake fa-2x"></i>
                                </div>
                                <h3 class="stat-value mt-2">
                                    @if($topAssists->count() > 0)
                                        {{ $topAssists->sum('total_asistencias') }}
                                    @else
                                        0
                                    @endif
                                </h3>
                                <p class="stat-label text-muted mb-0">Asistencias totales (Top 10)</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="stat-card">
                                <div class="stat-icon text-info">
                                    <i class="fas fa-shield-alt fa-2x"></i>
                                </div>
                                <h3 class="stat-value mt-2">
                                    @if($topGoalSaves->count() > 0)
                                        {{ $topGoalSaves->sum('total_atajadas') }}
                                    @else
                                        0
                                    @endif
                                </h3>
                                <p class="stat-label text-muted mb-0">Atajadas totales (Top 10)</p>
                            </div>
                        </div>
                        <div class="col-md-3 text-center">
                            <div class="stat-card">
                                <div class="stat-icon text-warning">
                                    <i class="fas fa-star fa-2x"></i>
                                </div>
                                <h3 class="stat-value mt-2">
                                    @if($topRated->count() > 0)
                                        {{ number_format($topRated->avg('promedio_calificacion'), 2) }}
                                    @else
                                        0.00
                                    @endif
                                </h3>
                                <p class="stat-label text-muted mb-0">Calif. promedio (Top 10)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leyenda del Sistema -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="alert alert-light border">
                <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i> Criterios de Selección</h6>
                <div class="row small">
                    <div class="col-md-3">
                        <strong>⚽ Goleadores:</strong> Jugadores con más goles totales
                    </div>
                    <div class="col-md-3">
                        <strong>🎯 Asistentes:</strong> Jugadores con más asistencias
                    </div>
                    <div class="col-md-3">
                        <strong>🧤 Porteros:</strong> Porteros con más atajadas
                    </div>
                    <div class="col-md-3">
                        <strong>⭐ Regulares:</strong> Mejor calificación promedio (mín. 3 partidos)
                    </div>
                </div>
                <div class="mt-2 small text-muted">
                    <i class="fas fa-clock me-1"></i>
                    Los rankings se actualizan automáticamente después de cada partido registrado.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .avatar img {
        object-fit: cover;
    }

    .badge-circle {
        transform: translate(-50%, -50%);
    }

    .goals-badge .badge {
        font-size: 1.25rem;
        padding: 0.5rem 1rem;
    }

    .average-display, .efficiency-display {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .rating-small .badge {
        min-width: 50px;
    }

    .rating-display .stars {
        font-size: 1.2rem;
        letter-spacing: 2px;
    }

    .rating-display .display-5 {
        line-height: 1;
    }

    .stat-card {
        padding: 1rem;
    }

    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
    }

    .stat-label {
        font-size: 0.9rem;
    }

    .stat-item {
        padding: 0.5rem;
    }

    .stat-item .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
    }

    .stat-item .stat-label {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .card {
        border-radius: 10px;
        overflow: hidden;
    }

    .card-header {
        border-radius: 10px 10px 0 0 !important;
    }

    .list-group-item:last-child {
        border-bottom: none;
    }

    @media (max-width: 768px) {
        .stat-value {
            font-size: 1.5rem;
        }

        .display-5 {
            font-size: 2.5rem;
        }
    }

    /* Efecto hover para las tarjetas */
    .card.border-warning:hover {
        transform: translateY(-5px);
        transition: transform 0.3s ease;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animación para las tarjetas de jugadores mejor calificados
    const ratingCards = document.querySelectorAll('.card.border-warning');
    ratingCards.forEach((card, index) => {
        // Animación de entrada escalonada
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';

        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });

    // Tooltips para las estrellas de calificación
    const stars = document.querySelectorAll('.stars i');
    stars.forEach(star => {
        star.addEventListener('mouseover', function() {
            const rating = this.closest('.rating-display').querySelector('.display-5').textContent;
            this.setAttribute('title', `Calificación: ${rating}/5.0`);
        });
    });

    // Cargar más datos si es necesario (para futuras implementaciones)
    const loadMoreBtn = document.getElementById('load-more');
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';
            // Aquí iría la lógica para cargar más jugadores
        });
    }
});
</script>
@endpush