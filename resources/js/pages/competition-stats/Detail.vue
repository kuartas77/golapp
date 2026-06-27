<template>
    <panel>
        <template #header><div class="d-flex justify-content-between align-items-start gap-3"><div><h3 class="mb-1">{{ payload.group?.name || 'Detalle del grupo' }} <span v-if="payload.group?.is_retired" class="badge bg-secondary">Retirado</span></h3><p class="text-muted mb-0">{{ payload.group?.category }} · {{ payload.group?.instructor_name || 'Sin formador' }}</p></div><router-link :to="{ name: 'competition-stats.index', query: { year: filters.year } }" class="btn btn-secondary btn-sm">Volver al escalafón</router-link></div></template>
        <template #body><div class="position-relative stats-page"><Loader :is-loading="isLoading" loading-text="Cargando detalle..." />
            <div class="surface-card mb-4"><div class="surface-card-body"><div class="row g-3 align-items-end"><div class="col-md-4"><label class="form-label">Año</label><select v-model="filters.year" class="form-select form-select-sm"><option v-for="year in payload.options?.years || []" :key="year" :value="year">{{ year }}</option></select></div><div class="col-md-5"><label class="form-label">Torneo</label><select v-model="filters.tournament_id" class="form-select form-select-sm"><option :value="null">Todos</option><option v-for="item in payload.options?.tournaments || []" :key="item.value" :value="item.value">{{ item.label }}</option></select></div><div class="col-md-3 d-grid"><button class="btn btn-primary" type="button" @click="applyFilters">Filtrar</button></div></div></div></div>
            <div v-if="globalError" class="alert alert-danger">{{ globalError }}</div><div v-if="payload.data_quality?.excluded_invalid_scores" class="alert alert-warning">Se excluyeron {{ payload.data_quality.excluded_invalid_scores }} partidos con marcador inválido.</div>
            <div class="row g-3 mb-4"><div v-for="card in cards" :key="card.label" class="col-6 col-lg-3"><div class="surface-card stat-card"><span class="stat-label">{{ card.label }}</span><strong class="stat-value">{{ card.value }}</strong><span class="stat-help">{{ card.help }}</span></div></div></div>
            <div class="row g-3 mb-4"><div class="col-12 col-xl-5"><div class="surface-card h-100"><div class="surface-card-header"><h5 class="mb-0">Resultados</h5></div><div class="surface-card-body"><apexchart v-if="payload.summary?.played" height="280" type="donut" :options="resultOptions" :series="resultSeries" /><div v-else class="empty-state">Sin resultados.</div></div></div></div><div class="col-12 col-xl-7"><div class="surface-card h-100"><div class="surface-card-header"><h5 class="mb-0">Evolución de goles</h5></div><div class="surface-card-body"><apexchart v-if="payload.goal_trend?.length" height="280" type="line" :options="trendOptions" :series="trendSeries" /><div v-else class="empty-state">Sin partidos para graficar.</div></div></div></div></div>
            <div class="surface-card overflow-hidden"><div class="surface-card-header"><h5 class="mb-0">Últimos diez partidos</h5></div><div class="surface-card-body p-0"><div v-if="payload.recent_matches?.length" class="table-responsive"><table class="table table-hover table-sm align-middle mb-0"><thead><tr><th>Fecha</th><th>Torneo</th><th>Rival</th><th>Marcador</th><th>Resultado</th></tr></thead><tbody><tr v-for="match in payload.recent_matches" :key="match.id"><td>{{ match.date }}</td><td>{{ match.tournament_name }}</td><td>{{ match.rival_name }}</td><td>{{ match.goals_for }} - {{ match.goals_against }}</td><td><span class="badge" :class="resultClass(match.result)">{{ match.result_label }}</span></td></tr></tbody></table></div><div v-else-if="!isLoading" class="empty-state">No hay partidos jugados para este periodo.</div></div></div>
        </div></template>
    </panel>
    <breadcrumb parent="Plataforma" :current="payload.group?.name || 'Detalle de competencia'" />
</template>

<script setup>
import { computed } from 'vue'
import apexchart from 'vue3-apexcharts'
import Loader from '@/components/general/Loader'
import { formatDecimal, useCompetitionStatsDetail } from '@/composables/competition/competitionStats'

const { payload, filters, isLoading, globalError, applyFilters, resultSeries, resultOptions, trendSeries, trendOptions } = useCompetitionStatsDetail()
const signed = (value) => Number(value) > 0 ? `+${value}` : value
const resultClass = (result) => ({ 'bg-success': result === 'win', 'bg-warning': result === 'draw', 'bg-danger': result === 'loss' })
const cards = computed(() => [
    { label: 'Partidos', value: payload.value.summary?.played || 0, help: `${payload.value.summary?.wins || 0} V · ${payload.value.summary?.draws || 0} E · ${payload.value.summary?.losses || 0} D` },
    { label: 'Puntos', value: payload.value.summary?.points || 0, help: `${formatDecimal(payload.value.summary?.performance_percentage)}% rendimiento` },
    { label: 'Diferencia', value: signed(payload.value.summary?.goal_difference || 0), help: `${payload.value.summary?.goals_for || 0} GF · ${payload.value.summary?.goals_against || 0} GC` },
    { label: 'Vallas invictas', value: payload.value.summary?.clean_sheets || 0, help: `${formatDecimal(payload.value.summary?.goals_for_average)} GF · ${formatDecimal(payload.value.summary?.goals_against_average)} GC por partido` },
])
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
</style>
