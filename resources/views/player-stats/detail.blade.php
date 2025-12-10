@extends('layouts.app')

@section('title', 'Estadísticas Detalladas - ' . ($playerStats->player_name ?? 'Jugador'))

@section('content')
<div class="container-fluid px-3 py-4">
    <!-- Encabezado con información del jugador -->
    <div class="row mb-4">
        <div class="col-md-10">
            <div class="d-flex align-items-center">
                <div class="avatar me-4">
                    @if($playerStats->photo)
                    <img src="{{$playerStats->photo}}"
                         class="rounded-circle border shadow"
                         width="100" height="100"
                         alt="{{ $playerStats->player_name }}">
                    @else
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow"
                         style="width: 100px; height: 100px;">
                        <i class="fas fa-user fa-3x text-muted"></i>
                    </div>
                    @endif
                </div>
                <div>
                    <h1 class="h2 mb-2">{{ $playerStats->player_name ?? 'Jugador' }}</h1>
                    @if($playerStats->date_birth)
                    <p class="text-muted mb-2 ml-2">
                        <i class="fas fa-birthday-cake me-1"></i>
                        {{ \Carbon\Carbon::parse($playerStats->date_birth)->age }} años
                        ({{ \Carbon\Carbon::parse($playerStats->date_birth)->format('d/m/Y') }})
                    </p>
                    @endif
                    <div class="d-flex gap-2">
                        <a href="{{ route('player.stats') }}" class="btn btn-sm btn-outline-secondary no-print">
                            <i class="fas fa-arrow-left"></i> Volver al listado
                        </a>
                        <button onclick="window.print()" class="btn btn-sm btn-outline-primary no-print">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- Estadísticas principales -->
    <div class="row ">

        <div class="col-md-2">
            <div class="card shadow-sm">
                <div class="card-body text-center py-3">
                    <h6 class="card-title mb-1">🏆Puntuación Total</h6>
                    <h2 class="display-6 fw-bold mb-0">
                        {{ number_format($playerStats->puntaje_escalafon ?? 0, 1) }}
                    </h2>
                    <small class="opacity-75">Puntos en el escalafón</small>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card  shadow-sm">
                <div class="card-body text-center py-3">
                    <h6 class="card-title mb-1">⚽Goles totales</h6>
                    <h2 class="display-8 fw-bold mb-0">
                        {{ number_format($playerStats->total_goles ?? 0, 1) }}
                    </h2>
                    <small class="opacity-75">{{ number_format($playerStats->total_goles / $playerStats->asistencias_partidos, 2) }} por partido</small>
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card  shadow-sm">
                <div class="card-body text-center py-3">
                    <h6 class="card-title mb-1">🤝Asistencias</h6>
                    <h2 class="display-8 fw-bold mb-0">
                        {{ $playerStats->total_asistencias_gol ?? 0 }}
                    </h2>
                    @if($playerStats->asistencias_partidos > 0)
                    <small class="opacity-75">{{ number_format($playerStats->total_asistencias_gol / $playerStats->asistencias_partidos, 2) }} por partido</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card  shadow-sm">
                <div class="card-body text-center py-3">
                    <h6 class="card-title mb-1">🧤Atajadas</h6>
                    <h2 class="display-8 fw-bold mb-0">
                        {{ $playerStats->total_atajadas ?? 0 }}
                    </h2>
                    @if($playerStats->asistencias_partidos > 0)
                    <small class="opacity-75">{{ number_format($playerStats->total_atajadas / $playerStats->asistencias_partidos, 2) }} por partido</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-2">
            <div class="card  shadow-sm">
                <div class="card-body text-center py-3">
                    <h6 class="card-title mb-1">⭐Calificación promedio</h6>
                    <h2 class="display-8 fw-bold mb-0">
                        {{ $playerStats->promedio_calificacion ?? 0 }}
                    </h2>
                    <small class="opacity-75">{{ $playerStats->asistencias_partidos ?? 0 }} partidos evaluados</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas detalladas -->
    <div class="row">
        <!-- Columna izquierda - Estadísticas generales -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Estadísticas Generales
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted">Partidos jugados</span>
                                <span class="fw-bold">{{ $playerStats->total_partidos ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted">Partidos asistidos</span>
                                <span class="fw-bold">{{ $playerStats->asistencias_partidos ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted">Veces titular</span>
                                <span class="fw-bold">{{ $playerStats->veces_titular ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted">% de titularidad</span>
                                <span class="fw-bold">
                                    @if($playerStats->asistencias_partidos > 0)
                                    {{ number_format(($playerStats->veces_titular / $playerStats->asistencias_partidos) * 100, 1) }}%
                                    @else
                                    0%
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted">Minutos jugados</span>
                                <span class="fw-bold">{{ number_format($playerStats->minutos_jugados ?? 0) }}'</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted">Promedio minutos/partido</span>
                                <span class="fw-bold">{{ $playerStats->promedio_minutos_partido ?? 0 }}'</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted">Tarjetas amarillas</span>
                                <span class="fw-bold text-warning">{{ $playerStats->total_amarillas ?? 0 }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-muted">Tarjetas rojas</span>
                                <span class="fw-bold text-danger">{{ $playerStats->total_rojas ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Eficiencia -->
                    @if($playerStats->minutos_jugados > 0)
                    <div class="mt-2">
                        <h6 class="mb-3">Eficiencia (acciones por 90 minutos)</h6>
                        <div class="row">
                            <div class="col-md-4 text-center">
                                <div class="bg-light rounded p-3">
                                    <div class="text-success fw-bold fs-5">
                                        {{ number_format(($playerStats->total_goles / $playerStats->minutos_jugados) * 90, 2) }}
                                    </div>
                                    <small class="text-muted">Goles cada 90'</small>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="bg-light rounded p-3">
                                    <div class="text-primary fw-bold fs-5">
                                        {{ number_format(($playerStats->total_asistencias_gol / $playerStats->minutos_jugados) * 90, 2) }}
                                    </div>
                                    <small class="text-muted">Asistencias cada 90'</small>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <div class="bg-light rounded p-3">
                                    <div class="text-info fw-bold fs-5">
                                        {{ number_format(($playerStats->total_atajadas / $playerStats->minutos_jugados) * 90, 2) }}
                                    </div>
                                    <small class="text-muted">Atajadas cada 90'</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Evolución del rendimiento -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Evolución de Calificaciones
                    </h5>
                </div>
                <div class="card-body">
                    <div class="col-md-12">
                    @if($recentMatches && count($recentMatches) > 0)
                        <canvas id="ratingsChart" height="100"></canvas>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">No hay datos suficientes para mostrar la evolución</p>
                        </div>
                    @endif
                    </div>
                </div>
            </div>
        </div>


    </div>

    <div class="row">
        <!-- Historial de partidos recientes -->
        <div class="col-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>Últimos 10 Partidos
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($recentMatches && count($recentMatches) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 table-sm table-bordered">
                                <thead class="">
                                    <tr>
                                        <th>📅 Fecha</th>
                                        <th class="text-center">Posición</th>
                                        <th class="text-center" title="Minutos Jugados">⏱️ Minutos</th>
                                        <th class="text-center" title="Goles">⚽ G</th>
                                        <th class="text-center" title="Asistencias Gol">🎯 A</th>
                                        <th class="text-center" title="Atajadas">🧤 ATA</th>
                                        <th class="text-center" title="Tarjetas Amarillas">🟨 TA</th>
                                        <th class="text-center" title="Tarjetas Rojas">🟥 TR</th>
                                        <th class="text-center" title="Calificación">⭐ Calificación</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentMatches as $match)
                                    <tr>
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($match->fecha_partido)->format('d/m/Y') }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($match->fecha_partido)->format('H:i') }}
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="">{{ $match->position ?? '-' }}</span>
                                        </td>
                                        <td class="text-center fw-bold">{{ $match->minutos ?? 0 }}'</td>
                                        <td class="text-center">
                                            @if($match->goals > 0)
                                            <span class="">{{ $match->goals }}</span>
                                            @else
                                            <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($match->goal_assists > 0)
                                            <span class="">{{ $match->goal_assists }}</span>
                                            @else
                                            <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($match->goal_saves > 0)
                                            <span class="text-success">{{ $match->goal_saves }}</span>
                                            @else
                                            <span class="text-muted">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($match->yellow_cards > 0)
                                            <span class="text-warning">{{ $match->yellow_cards }}</span>
                                            @else
                                            <span class="text-success">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($match->red_cards > 0)
                                            <span class="text-danger">{{ $match->red_cards }}</span>
                                            @else
                                            <span class="text-success">0</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $rating = $match->qualification ?? 0;
                                                if($rating >= 4) {
                                                    $ratingClass = 'bg-success';
                                                    $ratingIcon = 'fas fa-star';
                                                } elseif($rating >= 3) {
                                                    $ratingClass = 'bg-warning';
                                                    $ratingIcon = 'fas fa-star-half-alt';
                                                } else {
                                                    $ratingClass = 'bg-danger';
                                                    $ratingIcon = 'far fa-star';
                                                }
                                            @endphp
                                            <span class="badge {{ $ratingClass }} p-2"
                                                  title="Calificación: {{ $rating }}/5">
                                                <i class="{{ $ratingIcon }} me-1"></i>{{ $rating }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($match->observation)
                                            <small class="text-muted" data-bs-toggle="tooltip"
                                                   title="{{ $match->observation }}">
                                                {{ Str::limit($match->observation, 50) }}
                                            </small>
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No hay partidos registrados recientemente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

                <!-- Columna derecha - Posiciones jugadas -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-map-marker-alt me-2"></i>Posiciones Jugadas
                    </h5>
                </div>
                <div class="card-body">
                    @if($positionsHistory && count($positionsHistory) > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Posición</th>
                                        <th class="text-center">Veces jugada</th>
                                        <th class="text-center">Porcentaje</th>
                                        <th>Última vez</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($positionsHistory as $position)
                                    <tr>
                                        <td>
                                            <span class="">
                                                {{ $position->position }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold">{{ $position->veces_jugada }}</td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-info"
                                                     style="width: {{ $position->porcentaje }}%"
                                                     role="progressbar">
                                                    {{ $position->porcentaje }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $lastMatch = $recentMatches->firstWhere('position', $position->position);
                                            @endphp
                                            @if($lastMatch)
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($lastMatch->fecha_partido)->format('d/m/Y') }}
                                            </small>
                                            @else
                                            <small class="text-muted">-</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Gráfico de posiciones -->
                        <div class="mt-4">
                            <h6 class="mb-3">Distribución de posiciones</h6>
                            <div class="col-md-3">
                            <canvas id="positionsChart"></canvas>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No hay registro de posiciones jugadas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>

</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Gráfico de posiciones
    @if($positionsHistory && count($positionsHistory) > 0)
    var positionsCtx = document.getElementById('positionsChart').getContext('2d');
    var positionsChart = new Chart(positionsCtx, {
        type: 'doughnut',
        data: {
            labels: [
                @foreach($positionsHistory as $position)
                    "{{ $position->position }}",
                @endforeach
            ],
            datasets: [{
                data: [
                    @foreach($positionsHistory as $position)
                        {{ $position->veces_jugada }},
                    @endforeach
                ],
                backgroundColor: [
                    '#36a2eb', '#ff6384', '#4bc0c0', '#ff9f40',
                    '#9966ff', '#ffcd56', '#c9cbcf', '#4dc9f6'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            donutWidth: 60,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
    @endif

    // Gráfico de evolución de calificaciones
    @if($recentMatches && count($recentMatches) > 0)
    var ratingsCtx = document.getElementById('ratingsChart').getContext('2d');

    // Ordenar partidos por fecha (más antiguo a más reciente)
    var matches = @json($recentMatches);
    matches.sort((a, b) => new Date(a.fecha_partido) - new Date(b.fecha_partido));

    var dates = matches.map(match => {
        var date = new Date(match.fecha_partido);
        return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'short' });
    });

    var ratings = matches.map(match => match.qualification || 0);

    var ratingsChart = new Chart(ratingsCtx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Calificación',
                data: ratings,
                borderColor: '#36a2eb',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#36a2eb',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5,
                    title: {
                        display: true,
                        text: 'Calificación (0-5)'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Fecha del partido'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Calificación: ' + context.parsed.y + '/5';
                        }
                    }
                }
            }
        }
    });
    @endif

    // Función para imprimir
    // window.print = function() {
    //     window.print();
    // };
});
</script>
@endpush