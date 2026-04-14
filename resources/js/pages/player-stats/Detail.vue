<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div class="d-flex align-items-center gap-3">
                    <img
                        :src="player?.photo || '/img/user.png'"
                        :alt="player?.player_name || 'Jugador'"
                        class="player-avatar-xl"
                    />
                    <div>
                        <h3 class="mb-1">{{ player?.player_name || 'Detalle del jugador' }}</h3>
                        <p class="text-muted mb-0" v-if="player?.date_birth">
                            {{ age }} años · nacido el {{ formatDate(player.date_birth) }}
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="goBack">
                        Volver
                    </button>
                    <router-link :to="{ name: 'player-stats.top' }" class="btn btn-outline-primary btn-sm">
                        Ver destacados
                    </router-link>
                </div>
            </div>
        </template>

        <template #body>
            <div class="position-relative detail-page">
                <Loader :is-loading="isLoading" loading-text="Cargando detalle del jugador..." />

                <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between gap-3">
                    <span>{{ globalError }}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger align-self-start" @click="loadPlayerDetail()">
                        Reintentar
                    </button>
                </div>

                <template v-if="player">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card metric-card">
                                <span class="metric-label">Puntuación total</span>
                                <strong>{{ formatDecimal(player.puntaje_escalafon, 1) }}</strong>
                                <small>Escalafón acumulado</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card metric-card">
                                <span class="metric-label">Goles</span>
                                <strong>{{ formatNumber(player.total_goles) }}</strong>
                                <small>{{ formatDecimal(goalsPerMatch, 2) }} por partido</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card metric-card">
                                <span class="metric-label">Asistencias</span>
                                <strong>{{ formatNumber(player.total_asistencias_gol) }}</strong>
                                <small>{{ formatDecimal(assistsPerMatch, 2) }} por partido</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card metric-card">
                                <span class="metric-label">Calificación promedio</span>
                                <strong>{{ formatDecimal(player.promedio_calificacion, 1) }}</strong>
                                <small>{{ formatNumber(player.asistencias_partidos) }} partidos evaluados</small>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-12 col-xl-6">
                            <div class="surface-card h-100">
                                <div class="surface-card-header">
                                    <div class="section-label mb-2">Resumen del jugador</div>
                                    <h5 class="mb-1">Resumen general</h5>
                                    <p class="text-muted mb-0">Participación, disciplina y producción por juego.</p>
                                </div>

                                <div class="surface-card-body">
                                    <div class="summary-grid">
                                        <div class="summary-item">
                                            <span class="summary-label">Partidos jugados</span>
                                            <strong>{{ formatNumber(player.total_partidos) }}</strong>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">Partidos asistidos</span>
                                            <strong>{{ formatNumber(player.asistencias_partidos) }}</strong>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">Veces titular</span>
                                            <strong>{{ formatNumber(player.veces_titular) }}</strong>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">% titularidad</span>
                                            <strong>{{ formatDecimal(starterPercentage, 1) }}%</strong>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">Minutos jugados</span>
                                            <strong>{{ formatNumber(player.minutos_jugados) }}'</strong>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">Promedio minutos</span>
                                            <strong>{{ formatDecimal(player.promedio_minutos_partido, 2) }}'</strong>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">Tarjetas amarillas</span>
                                            <strong class="text-warning">{{ formatNumber(player.total_amarillas) }}</strong>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">Tarjetas rojas</span>
                                            <strong class="text-danger">{{ formatNumber(player.total_rojas) }}</strong>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">Atajadas</span>
                                            <strong>{{ formatNumber(player.total_atajadas) }}</strong>
                                        </div>
                                        <div class="summary-item">
                                            <span class="summary-label">Atajadas por partido</span>
                                            <strong>{{ formatDecimal(savesPerMatch, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-6">
                            <div class="surface-card h-100">
                                <div class="surface-card-header">
                                    <div class="section-label mb-2">Rendimiento</div>
                                    <h5 class="mb-1">Evolución de calificaciones</h5>
                                    <p class="text-muted mb-0">Últimos partidos ordenados de más antiguo a más reciente.</p>
                                </div>

                                <div class="surface-card-body">
                                    <apexchart
                                        v-if="orderedMatches.length"
                                        height="280"
                                        type="line"
                                        :options="ratingsChartOptions"
                                        :series="ratingsChartSeries"
                                    />

                                    <div v-else class="empty-state">
                                        <p class="text-muted mb-0">No hay suficientes partidos para graficar la evolución.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-12 col-xl-7">
                            <div class="surface-card h-100 overflow-hidden">
                                <div class="surface-card-header">
                                    <div class="section-label mb-2">Historial reciente</div>
                                    <h5 class="mb-1">Últimos 10 partidos</h5>
                                    <p class="text-muted mb-0">Rendimiento reciente con producción y observaciones.</p>
                                </div>

                                <div class="surface-card-body p-0">
                                    <div v-if="recentMatches.length" class="table-responsive">
                                        <table class="table table-hover table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Fecha</th>
                                                    <th class="text-center">Posición</th>
                                                    <th class="text-center">Min.</th>
                                                    <th class="text-center">G</th>
                                                    <th class="text-center">A</th>
                                                    <th class="text-center">ATA</th>
                                                    <th class="text-center">TA</th>
                                                    <th class="text-center">TR</th>
                                                    <th class="text-center">Calif.</th>
                                                    <th>Observación</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="match in recentMatches" :key="`${match.fecha_partido}_${match.position}`">
                                                    <td>{{ formatDateTime(match.fecha_partido) }}</td>
                                                    <td class="text-center">
                                                        <span class="theme-chip">{{ match.position || '-' }}</span>
                                                    </td>
                                                    <td class="text-center">{{ formatNumber(match.minutos) }}</td>
                                                    <td class="text-center">{{ formatNumber(match.goals) }}</td>
                                                    <td class="text-center">{{ formatNumber(match.goal_assists) }}</td>
                                                    <td class="text-center">{{ formatNumber(match.goal_saves) }}</td>
                                                    <td class="text-center text-warning">{{ formatNumber(match.yellow_cards) }}</td>
                                                    <td class="text-center text-danger">{{ formatNumber(match.red_cards) }}</td>
                                                    <td class="text-center">
                                                        <span class="badge" :class="`bg-${ratingVariant(match.qualification)}`">
                                                            {{ formatDecimal(match.qualification, 1) }}
                                                        </span>
                                                    </td>
                                                    <td class="match-observation">
                                                        {{ match.observation || '-' }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div v-else class="empty-state">
                                        <p class="text-muted mb-0">No hay partidos recientes para este jugador.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-5">
                            <div class="surface-card h-100">
                                <div class="surface-card-header">
                                    <div class="section-label mb-2">Mapa de posiciones</div>
                                    <h5 class="mb-1">Posiciones jugadas</h5>
                                    <p class="text-muted mb-0">Distribución histórica de roles dentro del campo.</p>
                                </div>

                                <div class="surface-card-body">
                                    <apexchart
                                        v-if="positionsHistory.length"
                                        height="260"
                                        type="donut"
                                        :options="positionsChartOptions"
                                        :series="positionsChartSeries"
                                    />

                                    <div v-if="positionsHistory.length" class="table-responsive mt-4">
                                        <table class="table table-sm align-middle mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Posición</th>
                                                    <th class="text-center">Veces</th>
                                                    <th class="text-center">Porcentaje</th>
                                                    <th>Última vez</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="position in positionsHistory" :key="position.position">
                                                    <td>{{ position.position }}</td>
                                                    <td class="text-center">{{ formatNumber(position.veces_jugada) }}</td>
                                                    <td class="text-center">{{ formatDecimal(position.porcentaje, 2) }}%</td>
                                                    <td>{{ formatDate(getLastMatchForPosition(position.position)) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div v-else class="empty-state">
                                        <p class="text-muted mb-0">No hay historial de posiciones para mostrar.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="player?.player_name || 'Detalle del jugador'" />
</template>

<script setup>
import apexchart from 'vue3-apexcharts'
import Loader from '@/components/general/Loader.vue'
import { usePlayerStatsDetail } from '@/composables/player/playerStats'

const {
    age,
    assistsPerMatch,
    formatDate,
    formatDateTime,
    formatDecimal,
    formatNumber,
    getLastMatchForPosition,
    globalError,
    goBack,
    goalsPerMatch,
    isLoading,
    loadPlayerDetail,
    orderedMatches,
    player,
    positionsChartOptions,
    positionsChartSeries,
    positionsHistory,
    ratingVariant,
    ratingsChartOptions,
    ratingsChartSeries,
    recentMatches,
    savesPerMatch,
    starterPercentage,
} = usePlayerStatsDetail()
</script>

<style scoped>
.detail-page {
    display: grid;
    gap: 1rem;
}

.surface-card {
    border: 1px solid rgba(59, 63, 92, 0.14);
    border-radius: 0.85rem;
    box-shadow: 0 12px 28px rgba(94, 92, 154, 0.05);
}

.surface-card-header,
.surface-card-body {
    padding: 1rem 1.15rem;
}

.surface-card-body.p-0 {
    padding: 0;
}

.surface-card-header {
    border-bottom: 1px solid rgba(59, 63, 92, 0.08);
}

.section-label {
    color: #888ea8;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.player-avatar-xl {
    width: 76px;
    height: 76px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid rgba(13, 110, 253, 0.16);
}

.metric-card {
    height: 100%;
    padding: 1rem 1.15rem;
}

.metric-card strong,
.metric-card small,
.metric-label {
    display: block;
}

.metric-label {
    color: #6c757d;
    margin-bottom: 0.45rem;
}

.metric-card strong {
    font-size: 2rem;
    line-height: 1.1;
}

.metric-card small {
    color: #6c757d;
    margin-top: 0.35rem;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 1rem;
}

.summary-item {
    border-radius: 0.85rem;
    padding: 0.9rem 1rem;
    background: rgba(59, 63, 92, 0.04);
}

.summary-label {
    display: block;
    color: #6c757d;
    margin-bottom: 0.25rem;
    font-size: 0.88rem;
}

.theme-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    padding: 0.35rem 0.7rem;
    border: 1px solid rgba(0, 150, 136, 0.2);
    background: rgba(0, 150, 136, 0.08);
    color: #009688;
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1;
}

.match-observation {
    min-width: 180px;
    white-space: normal;
}

.empty-state {
    padding: 2rem 1rem;
    text-align: center;
}

:global(.dark) .surface-card {
    background: rgba(27, 46, 75, 0.4);
    border-color: #1b2e4b;
    box-shadow: none;
}

:global(.dark) .surface-card-header {
    border-bottom-color: rgba(136, 142, 168, 0.18);
}

:global(.dark) .summary-item {
    background: rgba(255, 255, 255, 0.05);
}

:global(.dark) .theme-chip {
    background: rgba(0, 150, 136, 0.16);
    border-color: rgba(37, 213, 228, 0.2);
    color: #25d5e4;
}
</style>
