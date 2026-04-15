<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <h3 class="mb-1">Comparativo de evaluaciones</h3>
                    <p class="text-muted mb-0">
                        Contrasta dos períodos para entender la evolución técnica del jugador.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <router-link :to="{ name: 'player-evaluations.index' }" class="btn btn-outline-secondary btn-sm">
                        Volver al listado
                    </router-link>
                    <a
                        v-if="canExportPdf"
                        :href="pdfUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-primary btn-sm"
                    >
                        Exportar PDF
                    </a>
                </div>
            </div>
        </template>

        <template #body>
            <div class="position-relative comparison-page">
                <Loader :is-loading="isLoading" loading-text="Cargando comparativo..." />

                <div class="surface-card card mb-4">
                    <div class="surface-card-body card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-lg-5">
                                <label class="form-label">Inscripción</label>
                                <select v-model="filters.inscription_id" class="form-select">
                                    <option value="">Selecciona una inscripción</option>
                                    <option
                                        v-for="inscription in filterOptions.inscriptions"
                                        :key="inscription.id"
                                        :value="String(inscription.id)"
                                    >
                                        {{ inscription.label }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label">Período A</label>
                                <select v-model="filters.period_a_id" class="form-select">
                                    <option value="">Selecciona un período</option>
                                    <option v-for="period in filterOptions.periods" :key="period.id" :value="String(period.id)">
                                        {{ period.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <label class="form-label">Período B</label>
                                <select v-model="filters.period_b_id" class="form-select">
                                    <option value="">Selecciona un período</option>
                                    <option v-for="period in filterOptions.periods" :key="period.id" :value="String(period.id)">
                                        {{ period.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-lg-1 d-grid">
                                <button type="button" class="btn btn-primary" @click="submitComparison">
                                    Ver
                                </button>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="resetComparison">
                                Limpiar
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" @click="reload">
                                Recargar
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between gap-3">
                    <span>{{ globalError }}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger align-self-start" @click="reload">
                        Reintentar
                    </button>
                </div>

                <template v-if="comparison">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <div class="surface-card card stat-card">
                                <span class="stat-label">Jugador</span>
                                <strong class="stat-value stat-value-sm">{{ comparison.player?.name || '—' }}</strong>
                                <small class="text-muted">{{ comparison.inscription?.training_group_name || 'Sin grupo' }}</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="surface-card card stat-card">
                                <span class="stat-label">Período A</span>
                                <strong class="stat-value stat-value-sm">{{ comparison.period_a?.period_name || '—' }}</strong>
                                <small class="text-muted">{{ formatScore(comparison.overall?.period_a_score) }} pts</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="surface-card card stat-card">
                                <span class="stat-label">Período B</span>
                                <strong class="stat-value stat-value-sm">{{ comparison.period_b?.period_name || '—' }}</strong>
                                <small class="text-muted">{{ formatScore(comparison.overall?.period_b_score) }} pts</small>
                            </div>
                        </div>
                    </div>

                    <div class="surface-card card mb-4">
                        <div class="surface-card-header card-header d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div>
                                <div class="section-label mb-2">Resultado general</div>
                                <h5 class="mb-1">Variación entre períodos</h5>
                                <p class="text-muted mb-0">
                                    Lectura rápida del movimiento global del puntaje.
                                </p>
                            </div>
                            <span class="badge align-self-start text-uppercase" :class="`badge-${overallTrendVariant}`">
                                {{ overallTrendLabel }}
                                · {{ signedDelta(comparison.overall?.delta) }}
                            </span>
                        </div>

                        <div class="surface-card-body card-body">
                            <div class="comparison-hero">
                                <div class="score-box">
                                    <span>Período A</span>
                                    <strong>{{ formatScore(comparison.overall?.period_a_score) }}</strong>
                                </div>
                                <div class="comparison-arrow">→</div>
                                <div class="score-box">
                                    <span>Período B</span>
                                    <strong>{{ formatScore(comparison.overall?.period_b_score) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="surface-card card overflow-hidden mb-4">
                        <div class="surface-card-header card-header">
                            <div class="section-label mb-2">Dimensiones</div>
                            <h5 class="mb-1">Comparativo por dimensión</h5>
                            <p class="text-muted mb-0">Permite ver dónde hubo mayor avance o retroceso.</p>
                        </div>

                        <div class="surface-card-body card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Dimensión</th>
                                            <th class="text-center">Período A</th>
                                            <th class="text-center">Período B</th>
                                            <th class="text-center">Delta</th>
                                            <th class="text-center">Tendencia</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in comparison.dimensions || []" :key="item.dimension">
                                            <td class="fw-semibold">{{ item.dimension || '—' }}</td>
                                            <td class="text-center">{{ formatScore(item.period_a_score) }}</td>
                                            <td class="text-center">{{ formatScore(item.period_b_score) }}</td>
                                            <td class="text-center">{{ signedDelta(item.delta) }}</td>
                                            <td class="text-center">
                                                <span class="badge text-uppercase" :class="`badge-${trendVariant(item.trend)}`">
                                                    {{ trendLabel(item.trend) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="surface-card card overflow-hidden mb-4">
                        <div class="surface-card-header card-header">
                            <div class="section-label mb-2">Criterios</div>
                            <h5 class="mb-1">Detalle por criterio</h5>
                            <p class="text-muted mb-0">Incluye comentarios asociados a cada período.</p>
                        </div>

                        <div class="surface-card-body card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Dimensión</th>
                                            <th>Criterio</th>
                                            <th class="text-center">Período A</th>
                                            <th class="text-center">Período B</th>
                                            <th class="text-center">Delta</th>
                                            <th>Comentario A</th>
                                            <th>Comentario B</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in comparison.criteria || []" :key="`${item.dimension}-${item.criterion}`">
                                            <td>{{ item.dimension || '—' }}</td>
                                            <td class="fw-semibold">{{ item.criterion || '—' }}</td>
                                            <td class="text-center">{{ formatScore(item.period_a_score) }}</td>
                                            <td class="text-center">{{ formatScore(item.period_b_score) }}</td>
                                            <td class="text-center">{{ signedDelta(item.delta) }}</td>
                                            <td>{{ item.period_a_comment || '—' }}</td>
                                            <td>{{ item.period_b_comment || '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12 col-xl-6">
                            <div class="surface-card card h-100">
                                <div class="surface-card-header card-header">
                                    <div class="section-label mb-2">Período A</div>
                                    <h5 class="mb-1">Comentarios del período A</h5>
                                </div>
                                <div class="surface-card-body card-body">
                                    <div class="comment-block">
                                        <span>Comentario general</span>
                                        <p>{{ comparison.comments?.period_a?.general_comment || '—' }}</p>
                                    </div>
                                    <div class="comment-block">
                                        <span>Fortalezas</span>
                                        <p>{{ comparison.comments?.period_a?.strengths || '—' }}</p>
                                    </div>
                                    <div class="comment-block">
                                        <span>Oportunidades de mejora</span>
                                        <p>{{ comparison.comments?.period_a?.improvement_opportunities || '—' }}</p>
                                    </div>
                                    <div class="comment-block mb-0">
                                        <span>Recomendaciones</span>
                                        <p>{{ comparison.comments?.period_a?.recommendations || '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-6">
                            <div class="surface-card card h-100">
                                <div class="surface-card-header card-header">
                                    <div class="section-label mb-2">Período B</div>
                                    <h5 class="mb-1">Comentarios del período B</h5>
                                </div>
                                <div class="surface-card-body card-body">
                                    <div class="comment-block">
                                        <span>Comentario general</span>
                                        <p>{{ comparison.comments?.period_b?.general_comment || '—' }}</p>
                                    </div>
                                    <div class="comment-block">
                                        <span>Fortalezas</span>
                                        <p>{{ comparison.comments?.period_b?.strengths || '—' }}</p>
                                    </div>
                                    <div class="comment-block">
                                        <span>Oportunidades de mejora</span>
                                        <p>{{ comparison.comments?.period_b?.improvement_opportunities || '—' }}</p>
                                    </div>
                                    <div class="comment-block mb-0">
                                        <span>Recomendaciones</span>
                                        <p>{{ comparison.comments?.period_b?.recommendations || '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div v-else-if="!isLoading" class="surface-card card empty-card">
                    <div class="surface-card-body card-body text-center py-5">
                        <h5 class="mb-2">Selecciona una inscripción y dos períodos</h5>
                        <p class="text-muted mb-0">
                            El comparativo se generará apenas elijas ambos cortes del mismo jugador.
                        </p>
                    </div>
                </div>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Comparativo de evaluaciones'" />
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Loader from '@/components/general/Loader.vue'
import api from '@/utils/axios'
import {
    buildPdfUrl,
    formatScore,
    getValidationMessage,
    toQueryObject,
    trendLabelMap,
    trendVariantMap,
} from '@/pages/player-evaluations/utils'

const route = useRoute()
const router = useRouter()

const isLoading = ref(true)
const comparison = ref(null)
const globalError = ref('')

const filterOptions = reactive({
    inscriptions: [],
    periods: [],
})

const filters = reactive({
    inscription_id: '',
    period_a_id: '',
    period_b_id: '',
})

const canExportPdf = computed(() => (
    Boolean(filters.inscription_id) &&
    Boolean(filters.period_a_id) &&
    Boolean(filters.period_b_id) &&
    filters.period_a_id !== filters.period_b_id
))

const pdfUrl = computed(() => buildPdfUrl('/player-evaluations/comparison/pdf', filters))

const overallTrendVariant = computed(() => trendVariant(comparison.value?.overall?.trend))
const overallTrendLabel = computed(() => trendLabel(comparison.value?.overall?.trend))

function trendVariant(trend) {
    return trendVariantMap[trend] || 'secondary'
}

function trendLabel(trend) {
    return trendLabelMap[trend] || 'Sin datos'
}

function signedDelta(value) {
    if (value === null || value === undefined || value === '') {
        return '—'
    }

    const parsed = Number(value)

    if (Number.isNaN(parsed)) {
        return '—'
    }

    return `${parsed > 0 ? '+' : ''}${parsed.toFixed(2)}`
}

function syncFiltersFromRoute() {
    filters.inscription_id = String(route.query.inscription_id || '')
    filters.period_a_id = String(route.query.period_a_id || '')
    filters.period_b_id = String(route.query.period_b_id || '')
}

async function loadComparison() {
    const { data } = await api.get('/api/v2/player-evaluations/comparison', {
        params: toQueryObject(filters),
    })

    comparison.value = data.comparison || null
    filterOptions.inscriptions = data.filters?.inscriptions || []
    filterOptions.periods = data.filters?.periods || []
}

async function reload() {
    isLoading.value = true
    globalError.value = ''
    syncFiltersFromRoute()

    try {
        await loadComparison()
    } catch (error) {
        globalError.value = getValidationMessage(error, 'No fue posible cargar el comparativo.')
    } finally {
        isLoading.value = false
    }
}

function submitComparison() {
    if (!filters.inscription_id || !filters.period_a_id || !filters.period_b_id) {
        showMessage('Selecciona la inscripción y ambos períodos.', 'warning')
        return
    }

    if (filters.period_a_id === filters.period_b_id) {
        showMessage('El período A debe ser distinto al período B.', 'warning')
        return
    }

    router.replace({
        name: 'player-evaluations.comparison',
        query: toQueryObject(filters),
    })
}

function resetComparison() {
    router.replace({
        name: 'player-evaluations.comparison',
    })
}

watch(
    () => route.fullPath,
    () => {
        reload()
    },
    { immediate: true }
)
</script>

<style scoped>
.surface-card {
    border-radius: 16px;
    overflow: hidden;
}

.surface-card-body,
.surface-card-header {
    padding: 1.5rem;
}

.surface-card-body.p-0,
.surface-card-header.p-0 {
    padding: 0 !important;
}

.section-label {
    display: inline-flex;
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
    color: inherit;
    border: 1px solid currentColor;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    opacity: 0.8;
}

.stat-card {
    padding: 1.2rem 1.3rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.stat-label {
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: inherit;
    opacity: 0.75;
}

.stat-value {
    font-size: 1.9rem;
    line-height: 1;
}

.stat-value-sm {
    font-size: 1.35rem;
    line-height: 1.2;
}

.comparison-hero {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
    gap: 1rem;
    align-items: center;
}

.score-box {
    border: 1px solid rgba(127, 127, 127, 0.18);
    border-radius: 18px;
    padding: 1.5rem 1rem;
    text-align: center;
}

.score-box span {
    display: block;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: inherit;
    opacity: 0.75;
    margin-bottom: 0.5rem;
}

.score-box strong {
    font-size: 2rem;
}

.comparison-arrow {
    font-size: 2rem;
    font-weight: 700;
    color: inherit;
    opacity: 0.75;
}

.comment-block {
    margin-bottom: 1.25rem;
}

.comment-block span {
    display: block;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: inherit;
    opacity: 0.75;
    margin-bottom: 0.35rem;
}

.comment-block p {
    margin-bottom: 0;
    white-space: pre-line;
}

.empty-card {
    border-style: dashed;
}

.table > :not(caption) > * > * {
    padding: 1rem 0.85rem;
}

@media (max-width: 767px) {
    .comparison-hero {
        grid-template-columns: 1fr;
    }

    .comparison-arrow {
        transform: rotate(90deg);
        justify-self: center;
    }
}
</style>
