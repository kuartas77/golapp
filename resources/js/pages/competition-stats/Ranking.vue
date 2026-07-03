<template>
    <panel>
        <template #header>
            <div class="d-flex justify-content-between align-items-start gap-3 w-100" data-tour="competition-stats-actions">
                <h3 class="mb-1">Estadísticas de competencias</h3>
                <p class="text-muted mb-0">Rendimiento de los grupos en partidos marcados como jugados.</p>
                <button type="button" class="btn btn-info btn-sm mt-2" @click="tutorial.start()"><i class="fa-regular fa-circle-question me-2"></i>Guía</button>
            </div>
        </template>
        <template #body>
            <div class="position-relative stats-page">
                <Loader :is-loading="isLoading" loading-text="Calculando estadísticas..." />

                <div class="surface-card mb-4" data-tour="competition-stats-filters"><div class="surface-card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3"><label class="form-label">Año</label><select v-model="filters.year" class="form-select form-select-sm"><option v-for="year in payload.options?.years || []" :key="year" :value="year">{{ year }}</option></select></div>
                        <div class="col-md-3"><label class="form-label">Torneo</label><select v-model="filters.tournament_id" class="form-select form-select-sm"><option :value="null">Todos</option><option v-for="item in payload.options?.tournaments || []" :key="item.value" :value="item.value">{{ item.label }}</option></select></div>
                        <div class="col-md-3"><label class="form-label">Categoría</label><select v-model="filters.category" class="form-select form-select-sm"><option :value="null">Todas</option><option v-for="category in payload.options?.categories || []" :key="category" :value="category">{{ category }}</option></select></div>
                        <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-fill" type="button" @click="applyFilters">Filtrar</button><button class="btn btn-secondary" type="button" @click="resetFilters">Limpiar</button></div>
                    </div>
                </div></div>

                <div v-if="globalError" class="alert alert-danger">{{ globalError }}</div>
                <div v-if="payload.data_quality?.excluded_invalid_scores" class="alert alert-warning">Se excluyeron {{ payload.data_quality.excluded_invalid_scores }} partidos con marcador inválido.</div>

                <div class="row g-3 mb-4" data-tour="competition-stats-summary">
                    <div v-for="card in summaryCards" :key="card.label" class="col-6 col-lg-3"><div class="surface-card stat-card"><span class="stat-label">{{ card.label }}</span><strong class="stat-value">{{ card.value }}</strong><span class="stat-help">{{ card.help }}</span></div></div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-xl-5"><div class="surface-card h-100"><div class="surface-card-header"><h5 class="mb-0">Distribución de resultados</h5></div><div class="surface-card-body"><apexchart v-if="payload.summary?.played" height="290" type="donut" :options="resultOptions" :series="resultSeries" /><div v-else class="empty-state">Sin partidos jugados.</div></div></div></div>
                    <div class="col-12 col-xl-7"><div class="surface-card h-100"><div class="surface-card-header"><h5 class="mb-0">Goles por grupo</h5></div><div class="surface-card-body"><apexchart v-if="payload.groups?.length" height="290" type="bar" :options="goalsOptions" :series="goalsSeries" /><div v-else class="empty-state">Sin grupos para comparar.</div></div></div></div>
                </div>

                <div class="surface-card overflow-hidden" data-tour="competition-stats-ranking"><div class="surface-card-header"><h5 class="mb-1">Escalafón de grupos</h5><p class="text-muted mb-0">Orden: puntos, diferencia de gol, goles a favor y victorias.</p></div><div class="surface-card-body p-0">
                    <div v-if="payload.groups?.length" class="table-responsive"><table class="table table-hover table-sm align-middle mb-0"><thead><tr><th>#</th><th>Grupo</th><th>PJ</th><th>PG</th><th>PE</th><th>PP</th><th>GF</th><th>GC</th><th>DG</th><th>Pts</th><th>Rend.</th></tr></thead><tbody><tr v-for="(group, index) in payload.groups" :key="group.id"><td>{{ index + 1 }}</td><td><router-link :to="{ name: 'competition-stats.detail', params: { id: group.id }, query: { year: filters.year } }" class="fw-semibold">{{ group.name }}</router-link><div class="small text-muted">{{ group.category }} · {{ group.instructor_name || 'Sin formador' }} <span v-if="group.is_retired" class="badge bg-secondary">Retirado</span></div></td><td>{{ group.played }}</td><td>{{ group.wins }}</td><td>{{ group.draws }}</td><td>{{ group.losses }}</td><td>{{ group.goals_for }}</td><td>{{ group.goals_against }}</td><td>{{ signed(group.goal_difference) }}</td><td><strong>{{ group.points }}</strong></td><td>{{ formatDecimal(group.performance_percentage) }}%</td></tr></tbody></table></div>
                    <div v-else-if="!isLoading" class="empty-state">No hay partidos jugados para los filtros seleccionados.</div>
                </div></div>
            </div>
        </template>
    </panel>
    <PageTutorialOverlay :tutorial="tutorial" />
    <breadcrumb parent="Plataforma" current="Estadísticas de competencias" />
</template>

<script setup>
import { computed } from 'vue'
import apexchart from 'vue3-apexcharts'
import Loader from '@/components/general/Loader'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { competitionStatsTutorial } from '@/tutorials/competition'
import { formatDecimal, useCompetitionStatsRanking } from '@/composables/competition/competitionStats'

const { payload, filters, isLoading, globalError, applyFilters, resetFilters, resultSeries, resultOptions, goalsSeries, goalsOptions } = useCompetitionStatsRanking()
const tutorial = usePageTutorial(competitionStatsTutorial)
const signed = (value) => Number(value) > 0 ? `+${value}` : value
const summaryCards = computed(() => [
    { label: 'Grupos', value: payload.value.summary?.groups_count || 0, help: 'Con partidos jugados' },
    { label: 'Partidos', value: payload.value.summary?.played || 0, help: `${payload.value.summary?.wins || 0} V · ${payload.value.summary?.draws || 0} E · ${payload.value.summary?.losses || 0} D` },
    { label: 'Balance de gol', value: signed(payload.value.summary?.goal_difference || 0), help: `${payload.value.summary?.goals_for || 0} GF · ${payload.value.summary?.goals_against || 0} GC` },
    { label: 'Rendimiento', value: `${formatDecimal(payload.value.summary?.performance_percentage)}%`, help: `${payload.value.summary?.points || 0} puntos` },
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
