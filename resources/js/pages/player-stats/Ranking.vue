<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <h3 class="mb-1">Estadísticas de jugadores</h3>
                    <p class="text-muted mb-0">
                        Escalafón general por rendimiento, participación y aporte deportivo.
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <router-link :to="{ name: 'player-stats.top' }" class="btn btn-primary btn-sm">
                        Ver destacados
                    </router-link>
                </div>
            </div>
        </template>

        <template #body>
            <div class="position-relative stats-page">
                <Loader :is-loading="isLoading" loading-text="Cargando escalafón..." />

                <div class="row g-3 mb-4">
                    <div class="col-12 col-xl-7">
                        <div class="surface-card h-100">
                            <div class="surface-card-body">
                                <div class="section-label mb-2">Filtros del ranking</div>
                                <p class="text-muted mb-3">
                                    Ajusta la vista por posición o categoría sin salir del módulo.
                                </p>

                                <div class="row g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label">Posición</label>
                                        <select v-model="filters.position" class="form-select form-select-sm">
                                            <option :value="null">Todas las posiciones</option>
                                            <option
                                                v-for="position in positions"
                                                :key="position.value"
                                                :value="position.value"
                                            >
                                                {{ position.label }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-5">
                                        <label class="form-label">Categoría</label>
                                        <select v-model="filters.category" class="form-select form-select-sm">
                                            <option :value="null">Todas las categorías</option>
                                            <option v-for="category in categories" :key="category" :value="category">
                                                {{ category }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 d-grid">
                                        <button type="button" class="btn btn-primary" @click="applyFilters">
                                            Filtrar
                                        </button>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <button
                                        type="button"
                                        class="btn btn-secondary btn-sm"
                                        :disabled="!hasActiveFilters"
                                        @click="resetFilters"
                                    >
                                        Limpiar filtros
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" @click="reload">
                                        Recargar
                                    </button>
                                    <span v-if="school?.name" class="theme-chip align-self-center">
                                        {{ school.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-5">
                        <div class="surface-card h-100">
                            <div class="surface-card-body">
                                <div class="section-label mb-2">Sistema de puntuación</div>
                                <p class="text-muted mb-3">
                                    El puntaje mezcla impacto ofensivo, minutos, titularidad, disciplina y evaluación técnica.
                                </p>

                                <div class="rule-grid">
                                    <div v-for="rule in scoringRules" :key="rule.label" class="rule-pill">
                                        <strong>{{ rule.label }}</strong>
                                        <span>{{ rule.points }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between gap-3">
                    <span>{{ globalError }}</span>
                    <button type="button" class="btn btn-sm btn-danger align-self-start" @click="reload">
                        Reintentar
                    </button>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="surface-card stat-card">
                            <span class="stat-label">Jugadores en ranking</span>
                            <strong class="stat-value">{{ formatNumber(players.length) }}</strong>
                            <span class="stat-help">Top del escalafón actual</span>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="surface-card stat-card">
                            <span class="stat-label">Líder actual</span>
                            <strong class="stat-value stat-value-sm">
                                {{ leader?.player_name || 'Sin registros' }}
                            </strong>
                            <span class="stat-help">
                                {{ leader ? `${formatDecimal(leader.puntaje_escalafon, 1)} pts` : 'Aún no hay datos suficientes' }}
                            </span>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="surface-card stat-card">
                            <span class="stat-label">Filtros activos</span>
                            <strong class="stat-value">{{ hasActiveFilters ? 'Sí' : 'No' }}</strong>
                            <span class="stat-help">Posición y categoría opcionales</span>
                        </div>
                    </div>
                </div>

                <div class="surface-card overflow-hidden">
                    <div class="surface-card-header">
                        <div class="section-label mb-2">Tabla principal</div>
                        <h5 class="mb-1">Escalafón de jugadores</h5>
                        <p class="text-muted mb-0">Ordenado por puntaje total calculado con el sistema deportivo.</p>
                    </div>

                    <div class="surface-card-body p-0">
                        <div v-if="players.length" class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Jugador</th>
                                        <th class="text-center">Posición</th>
                                        <th class="text-center">PJ</th>
                                        <th class="text-center">Goles</th>
                                        <th class="text-center">Asist.</th>
                                        <th class="text-center">Ataj.</th>
                                        <th class="text-center">Amarillas</th>
                                        <th class="text-center">Rojas</th>
                                        <th class="text-center">Minutos</th>
                                        <th class="text-center">Calif.</th>
                                        <th class="text-center">Puntaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(player, index) in players" :key="player.player_id">
                                        <td class="text-center fw-semibold">#{{ index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img :src="player.photo" :alt="player.player_name" class="player-avatar" />
                                                <div>
                                                    <router-link
                                                        :to="{ name: 'player-stats.detail', params: { id: player.player_id } }"
                                                        class="fw-semibold text-decoration-none"
                                                    >
                                                        {{ player.player_name }}
                                                    </router-link>
                                                    <div class="small text-muted">
                                                        {{ formatNumber(player.asistencias_partidos) }} asistencias
                                                        · {{ formatNumber(player.veces_titular) }} veces titular
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="theme-chip">
                                                {{ player.posicion_principal || 'Variada' }}
                                            </span>
                                        </td>
                                        <td class="text-center">{{ formatNumber(player.total_partidos) }}</td>
                                        <td class="text-center">
                                            <div class="fw-semibold text-success">{{ formatNumber(player.total_goles) }}</div>
                                            <small class="text-muted">
                                                {{ formatDecimal(safeDivide(player.total_goles, player.asistencias_partidos), 2) }}/partido
                                            </small>
                                        </td>
                                        <td class="text-center">{{ formatNumber(player.total_asistencias_gol) }}</td>
                                        <td class="text-center">{{ formatNumber(player.total_atajadas) }}</td>
                                        <td class="text-center text-warning">{{ formatNumber(player.total_amarillas) }}</td>
                                        <td class="text-center text-danger">{{ formatNumber(player.total_rojas) }}</td>
                                        <td class="text-center">
                                            <div>{{ formatNumber(player.minutos_jugados) }}'</div>
                                            <small class="text-muted">
                                                {{ formatDecimal(player.promedio_minutos_partido, 2) }}/partido
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge" :class="`bg-${ratingVariant(player.promedio_calificacion)}`">
                                                {{ formatDecimal(player.promedio_calificacion, 1) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ formatDecimal(player.puntaje_escalafon, 1) }}</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else-if="!isLoading" class="empty-state">
                            <h5 class="mb-2">No hay jugadores para mostrar</h5>
                            <p class="text-muted mb-0">Prueba cambiando los filtros o vuelve a recargar la información.</p>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Estadísticas de jugadores'" />
</template>

<script setup>
import Loader from '@/components/general/Loader.vue'
import { usePlayerStatsRanking } from '@/composables/player/playerStats'

const {
    categories,
    filters,
    formatDecimal,
    formatNumber,
    globalError,
    hasActiveFilters,
    isLoading,
    leader,
    players,
    positions,
    ratingVariant,
    reload,
    resetFilters,
    safeDivide,
    school,
    scoringRules,
    applyFilters,
} = usePlayerStatsRanking()
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

.section-label {
    color: #888ea8;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.rule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 0.75rem;
}

.rule-pill {
    border: 1px solid rgba(13, 110, 253, 0.12);
    border-radius: 0.75rem;
    padding: 0.8rem 0.9rem;
    background: rgba(13, 110, 253, 0.04);
}

.rule-pill strong,
.rule-pill span {
    display: block;
}

.rule-pill span {
    color: #6c757d;
    font-size: 0.88rem;
    margin-top: 0.2rem;
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

.stat-card {
    height: 100%;
    padding: 1rem 1.15rem;
}

.stat-label,
.stat-help {
    display: block;
}

.stat-label {
    color: #6c757d;
    font-size: 0.88rem;
    margin-bottom: 0.35rem;
}

.stat-value {
    display: block;
    font-size: 2rem;
    line-height: 1.1;
}

.stat-value-sm {
    font-size: 1.25rem;
}

.stat-help {
    color: #6c757d;
    margin-top: 0.45rem;
}

.empty-state {
    padding: 3rem 1.5rem;
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

:global(.dark) .rule-pill {
    background: rgba(13, 110, 253, 0.12);
    border-color: rgba(37, 213, 228, 0.18);
}

:global(.dark) .theme-chip {
    background: rgba(0, 150, 136, 0.16);
    border-color: rgba(37, 213, 228, 0.2);
    color: #25d5e4;
}
</style>
