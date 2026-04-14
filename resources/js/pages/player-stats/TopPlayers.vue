<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <h3 class="mb-1">Jugadores destacados</h3>
                    <p class="text-muted mb-0">
                        Reconocimiento a quienes lideran las métricas ofensivas, defensivas y de rendimiento.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <router-link :to="{ name: 'player-stats.index' }" class="btn btn-outline-primary btn-sm">
                        Ver ranking
                    </router-link>
                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="loadTopPlayers">
                        Recargar
                    </button>
                </div>
            </div>
        </template>

        <template #body>
            <div class="position-relative stats-page">
                <Loader :is-loading="isLoading" loading-text="Cargando destacados..." />

                <div class="row g-3 mb-4">
                    <div class="col-12 col-xl-4">
                        <div class="surface-card accent-card h-100">
                            <div class="surface-card-body">
                                <div class="section-label mb-2">Jugador foco</div>
                                <p class="text-muted mb-0">
                                    Referencia rápida del módulo según rendimiento reciente y evaluación promedio.
                                </p>

                                <div v-if="spotlightPlayer" class="d-flex align-items-center gap-3 mt-3">
                                    <img :src="spotlightPlayer.photo" :alt="spotlightPlayer.player_name" class="spotlight-avatar" />
                                    <div>
                                        <router-link
                                            :to="{ name: 'player-stats.detail', params: { id: spotlightPlayer.player_id } }"
                                            class="fw-semibold text-decoration-none"
                                        >
                                            {{ spotlightPlayer.player_name }}
                                        </router-link>
                                        <div class="small text-muted mt-1">
                                            {{ formatDecimal(spotlightPlayer.promedio_calificacion || 0, 1) }}/5 de calificación promedio
                                        </div>
                                    </div>
                                </div>

                                <p v-else class="text-muted mb-0 mt-3">Aún no hay jugadores para destacar.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="surface-card mini-stat h-100">
                            <div class="surface-card-body">
                                <span class="mini-stat-label">Actualizado</span>
                                <strong>{{ updatedAt ? formatDate(updatedAt, 'DD/MM/YYYY') : '-' }}</strong>
                                <small>Fecha de corte actual</small>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="surface-card mini-stat h-100">
                            <div class="surface-card-body">
                                <span class="mini-stat-label">Temporada</span>
                                <strong>{{ season || '-' }}</strong>
                                <small>Resumen de la campaña vigente</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between gap-3">
                    <span>{{ globalError }}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger align-self-start" @click="loadTopPlayers">
                        Reintentar
                    </button>
                </div>

                <div class="surface-card overflow-hidden mb-4">
                    <div class="surface-card-header">
                        <div class="section-label mb-2">Tabla principal</div>
                        <h5 class="mb-1">Máximos goleadores</h5>
                        <p class="text-muted mb-0">Incluye productividad por partido y eficiencia cada 90 minutos.</p>
                    </div>

                    <div class="surface-card-body p-0">
                        <div v-if="scorersWithEfficiency.length" class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Pos.</th>
                                        <th>Jugador</th>
                                        <th class="text-center">Partidos</th>
                                        <th class="text-center">Goles</th>
                                        <th class="text-center">Promedio</th>
                                        <th class="text-center">Cada 90'</th>
                                        <th class="text-center">Calif.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(player, index) in scorersWithEfficiency"
                                        :key="player.player_id"
                                        :class="{ 'podium-row': index < 3 }"
                                    >
                                        <td class="text-center fw-semibold">#{{ index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img :src="player.photo" :alt="player.player_name" class="player-avatar" />
                                                <router-link
                                                    :to="{ name: 'player-stats.detail', params: { id: player.player_id } }"
                                                    class="fw-semibold text-decoration-none"
                                                >
                                                    {{ player.player_name }}
                                                </router-link>
                                            </div>
                                        </td>
                                        <td class="text-center">{{ formatNumber(player.partidos) }}</td>
                                        <td class="text-center text-success fw-semibold">{{ formatNumber(player.total_goles) }}</td>
                                        <td class="text-center">{{ formatDecimal(player.promedio_goles, 2) }}</td>
                                        <td class="text-center text-info">{{ formatDecimal(player.goals_per_90, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge" :class="`bg-${ratingVariant(player.promedio_calificacion)}`">
                                                {{ formatDecimal(player.promedio_calificacion, 1) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else-if="!isLoading" class="empty-state">
                            <h5 class="mb-2">No hay datos de goleadores</h5>
                            <p class="text-muted mb-0">Cuando existan registros aparecerán aquí.</p>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-xl-6">
                        <div class="surface-card h-100">
                            <div class="surface-card-header">
                                <div class="section-label mb-2">Creación ofensiva</div>
                                <h5 class="mb-1">Máximas asistencias</h5>
                                <p class="text-muted mb-0">Jugadores con mejor producción de pases gol.</p>
                            </div>

                            <div class="surface-card-body">
                                <div v-if="topAssists.length" class="list-group list-group-flush">
                                    <div
                                        v-for="(player, index) in topAssists"
                                        :key="player.player_id"
                                        class="list-group-item px-0 bg-transparent"
                                    >
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="rank-chip">{{ index + 1 }}</span>
                                                <img :src="player.photo" :alt="player.player_name" class="player-avatar" />
                                                <div>
                                                    <router-link
                                                        :to="{ name: 'player-stats.detail', params: { id: player.player_id } }"
                                                        class="fw-semibold text-decoration-none"
                                                    >
                                                        {{ player.player_name }}
                                                    </router-link>
                                                    <div class="small text-muted">
                                                        {{ formatNumber(player.partidos) }} partidos ·
                                                        {{ formatDecimal(player.promedio_asistencias, 2) }} por juego
                                                    </div>
                                                </div>
                                            </div>
                                            <strong class="text-primary">{{ formatNumber(player.total_asistencias) }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="!isLoading" class="empty-state">
                                    <p class="text-muted mb-0">No hay asistencias registradas.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-6">
                        <div class="surface-card h-100">
                            <div class="surface-card-header">
                                <div class="section-label mb-2">Bloque defensivo</div>
                                <h5 class="mb-1">Porteros destacados</h5>
                                <p class="text-muted mb-0">Ranking por atajadas con apoyo de la calificación técnica.</p>
                            </div>

                            <div class="surface-card-body">
                                <div v-if="topGoalSaves.length" class="list-group list-group-flush">
                                    <div
                                        v-for="(player, index) in topGoalSaves"
                                        :key="player.player_id"
                                        class="list-group-item px-0 bg-transparent"
                                    >
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="rank-chip rank-chip-info">{{ index + 1 }}</span>
                                                <img :src="player.photo" :alt="player.player_name" class="player-avatar" />
                                                <div>
                                                    <router-link
                                                        :to="{ name: 'player-stats.detail', params: { id: player.player_id } }"
                                                        class="fw-semibold text-decoration-none"
                                                    >
                                                        {{ player.player_name }}
                                                    </router-link>
                                                    <div class="small text-muted">
                                                        {{ formatNumber(player.partidos) }} partidos
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <strong class="d-block text-info">{{ formatNumber(player.total_atajadas) }}</strong>
                                                <small class="text-muted">
                                                    {{ formatDecimal(player.promedio_calificacion, 1) }}/5
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-else-if="!isLoading" class="empty-state">
                                    <p class="text-muted mb-0">No hay atajadas registradas.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="surface-card">
                    <div class="surface-card-header">
                        <div class="section-label mb-2">Evaluación técnica</div>
                        <h5 class="mb-1">Mejor calificados</h5>
                        <p class="text-muted mb-0">Mínimo 3 partidos con evaluación registrada.</p>
                    </div>

                    <div class="surface-card-body">
                        <div v-if="topRated.length" class="row g-3">
                            <div v-for="player in topRated" :key="player.player_id" class="col-12 col-md-6 col-xl-4">
                                <div class="rated-card h-100">
                                    <div class="d-flex align-items-center gap-3">
                                        <img :src="player.photo" :alt="player.player_name" class="player-avatar player-avatar-lg" />
                                        <div>
                                            <router-link
                                                :to="{ name: 'player-stats.detail', params: { id: player.player_id } }"
                                                class="fw-semibold text-decoration-none"
                                            >
                                                {{ player.player_name }}
                                            </router-link>
                                            <div class="small text-muted mt-1">
                                                {{ formatNumber(player.partidos) }} partidos
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-end mt-3">
                                        <div>
                                            <span class="small text-muted d-block">Calificación promedio</span>
                                            <strong class="fs-4">{{ formatDecimal(player.promedio_calificacion, 1) }}</strong>
                                        </div>
                                        <span class="badge" :class="`bg-${ratingVariant(player.promedio_calificacion)}`">
                                            {{ formatNumber(player.goals) }} G · {{ formatNumber(player.assists) }} A
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else-if="!isLoading" class="empty-state">
                            <p class="text-muted mb-0">Todavía no hay evaluaciones suficientes para este listado.</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Jugadores destacados'" />
</template>

<script setup>
import Loader from '@/components/general/Loader.vue'
import { useTopPlayers } from '@/composables/player/playerStats'

const {
    formatDate,
    formatDecimal,
    formatNumber,
    globalError,
    isLoading,
    scorersWithEfficiency,
    season,
    spotlightPlayer,
    topAssists,
    topGoalSaves,
    topRated,
    updatedAt,
    loadTopPlayers,
    ratingVariant,
} = useTopPlayers()
</script>

<style scoped>
.stats-page {
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

.section-label,
.mini-stat-label {
    color: #888ea8;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.75rem;
    font-weight: 700;
}

.accent-card {
    border-top: 3px solid #0d6efd;
}

.spotlight-avatar,
.player-avatar {
    width: 52px;
    height: 52px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid rgba(13, 110, 253, 0.14);
}

.player-avatar-lg {
    width: 60px;
    height: 60px;
}

.mini-stat strong,
.mini-stat small {
    display: block;
}

.mini-stat strong {
    font-size: 1.8rem;
    margin-top: 0.4rem;
}

.mini-stat small {
    color: #6c757d;
    margin-top: 0.35rem;
}

.podium-row {
    background: rgba(25, 135, 84, 0.06);
}

.rank-chip {
    width: 28px;
    height: 28px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(13, 110, 253, 0.12);
    color: #0d6efd;
    font-weight: 700;
}

.rank-chip-info {
    background: rgba(13, 202, 240, 0.12);
    color: #0dcaf0;
}

.rated-card {
    border-radius: 0.85rem;
    border: 1px solid rgba(59, 63, 92, 0.12);
    background: rgba(13, 110, 253, 0.03);
    padding: 1rem;
}

.empty-state {
    padding: 2rem 1rem;
    text-align: center;
}

:global(.dark) .surface-card,
:global(.dark) .rated-card {
    background: rgba(27, 46, 75, 0.4);
    border-color: #1b2e4b;
    box-shadow: none;
}

:global(.dark) .surface-card-header {
    border-bottom-color: rgba(136, 142, 168, 0.18);
}

:global(.dark) .podium-row {
    background: rgba(25, 135, 84, 0.14);
}

:global(.dark) .rank-chip {
    background: rgba(13, 110, 253, 0.18);
}

:global(.dark) .rank-chip-info {
    background: rgba(13, 202, 240, 0.2);
}
</style>
