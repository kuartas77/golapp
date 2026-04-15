<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div class="d-flex align-items-center gap-3">
                    <img
                        :src="evaluation?.inscription?.player?.photo_url || '/img/user.webp'"
                        :alt="evaluation?.inscription?.player?.name || 'Jugador'"
                        class="player-avatar-xl"
                    >
                    <div>
                        <h3 class="mb-1">
                            Evaluación #{{ route.params.id }}
                        </h3>
                        <p class="text-muted mb-0">
                            {{ evaluation?.inscription?.player?.name || 'Jugador sin nombre' }}
                            · {{ evaluation?.evaluation_period?.name || 'Sin período' }}
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <router-link :to="{ name: 'player-evaluations.index' }" class="btn btn-outline-secondary btn-sm">
                        Volver
                    </router-link>
                    <router-link
                        v-if="evaluation && !evaluation.is_closed"
                        :to="{ name: 'player-evaluations.edit', params: { id: evaluation.id } }"
                        class="btn btn-outline-warning btn-sm"
                    >
                        Editar
                    </router-link>
                    <a
                        v-if="evaluation?.urls?.pdf"
                        :href="evaluation.urls.pdf"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="btn btn-primary btn-sm"
                    >
                        Descargar PDF
                    </a>
                    <button
                        v-if="evaluation && !evaluation.is_closed"
                        type="button"
                        class="btn btn-outline-danger btn-sm"
                        @click="confirmDelete"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </template>

        <template #body>
            <div class="position-relative evaluation-show">
                <Loader :is-loading="isLoading" loading-text="Cargando detalle de la evaluación..." />

                <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between gap-3">
                    <span>{{ globalError }}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger align-self-start" @click="loadEvaluation">
                        Reintentar
                    </button>
                </div>

                <template v-if="evaluation">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card card stat-card">
                                <span class="stat-label">Jugador</span>
                                <strong class="stat-value stat-value-sm">{{ evaluation.inscription?.player?.name || '—' }}</strong>
                                <small class="text-muted">{{ evaluation.inscription?.training_group?.name || 'Sin grupo' }}</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card card stat-card">
                                <span class="stat-label">Período</span>
                                <strong class="stat-value stat-value-sm">{{ evaluation.evaluation_period?.name || '—' }}</strong>
                                <small class="text-muted">{{ evaluation.template?.name || 'Sin plantilla' }}</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card card stat-card">
                                <span class="stat-label">Estado</span>
                                <strong class="stat-value stat-value-sm">
                                    {{ labelFromOptions(statusOptions, evaluation.status, evaluation.status || '—') }}
                                </strong>
                                <small class="text-muted">
                                    {{ labelFromOptions(evaluationTypeOptions, evaluation.evaluation_type, evaluation.evaluation_type || '—') }}
                                </small>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card card stat-card">
                                <span class="stat-label">Nota general</span>
                                <strong class="stat-value">{{ formatScore(evaluation.overall_score) }}</strong>
                                <small class="text-muted">{{ formatDateTime(evaluation.evaluated_at) }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="surface-card card mb-4">
                        <div class="surface-card-header card-header">
                            <div class="section-label mb-2">Resumen general</div>
                            <h5 class="mb-1">Datos principales de la evaluación</h5>
                            <p class="text-muted mb-0">Información administrativa y trazabilidad del registro.</p>
                        </div>

                        <div class="surface-card-body card-body">
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <span>Evaluador</span>
                                    <strong>{{ evaluation.evaluator?.name || '—' }}</strong>
                                </div>
                                <div class="detail-item">
                                    <span>Fecha evaluación</span>
                                    <strong>{{ formatDateTime(evaluation.evaluated_at) }}</strong>
                                </div>
                                <div class="detail-item">
                                    <span>Código jugador</span>
                                    <strong>{{ evaluation.inscription?.player?.unique_code || '—' }}</strong>
                                </div>
                                <div class="detail-item">
                                    <span>Creado</span>
                                    <strong>{{ formatDateTime(evaluation.created_at) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="dimensionEntries.length" class="row g-3 mb-4">
                        <div v-for="([dimension, score]) in dimensionEntries" :key="dimension" class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card card metric-card">
                                <span class="stat-label">{{ dimension }}</span>
                                <strong>{{ formatScore(score) }}</strong>
                                <small class="text-muted">Promedio ponderado</small>
                            </div>
                        </div>
                    </div>

                    <div v-for="(scores, dimension) in scoresByDimension" :key="dimension" class="surface-card card overflow-hidden mb-4">
                        <div class="surface-card-header card-header d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div>
                                <div class="section-label mb-2">Dimensión</div>
                                <h5 class="mb-1">{{ dimension }}</h5>
                                <p class="text-muted mb-0">Criterios y observaciones registradas en este eje.</p>
                            </div>

                            <span class="theme-chip align-self-start">
                                Promedio: {{ formatScore(evaluation.dimension_scores?.[dimension]) }}
                            </span>
                        </div>

                        <div class="surface-card-body card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Criterio</th>
                                            <th class="text-center">Puntaje</th>
                                            <th class="text-center">Escala</th>
                                            <th>Comentario</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="score in scores" :key="score.id">
                                            <td>
                                                <div class="fw-semibold">{{ score.criterion?.name || '—' }}</div>
                                                <small class="text-muted">
                                                    {{ score.criterion?.code || 'Sin código' }}
                                                    · Peso {{ formatScore(score.criterion?.weight || 1) }}
                                                </small>
                                            </td>
                                            <td class="text-center">{{ formatScore(score.score) }}</td>
                                            <td class="text-center">{{ score.scale_value || '—' }}</td>
                                            <td>{{ score.comment || '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div v-if="!Object.keys(scoresByDimension).length" class="surface-card card empty-card mb-4">
                        <div class="surface-card-body card-body text-center py-5">
                            <h5 class="mb-2">No hay puntajes registrados</h5>
                            <p class="text-muted mb-0">
                                Esta evaluación aún no tiene criterios diligenciados.
                            </p>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12 col-xl-6">
                            <div class="surface-card card h-100">
                                <div class="surface-card-header card-header">
                                    <div class="section-label mb-2">Conclusiones</div>
                                    <h5 class="mb-1">Lectura general</h5>
                                </div>
                                <div class="surface-card-body card-body">
                                    <div class="comment-block">
                                        <span>Comentario general</span>
                                        <p>{{ evaluation.general_comment || '—' }}</p>
                                    </div>
                                    <div class="comment-block mb-0">
                                        <span>Fortalezas</span>
                                        <p>{{ evaluation.strengths || '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-6">
                            <div class="surface-card card h-100">
                                <div class="surface-card-header card-header">
                                    <div class="section-label mb-2">Plan de mejora</div>
                                    <h5 class="mb-1">Próximos pasos sugeridos</h5>
                                </div>
                                <div class="surface-card-body card-body">
                                    <div class="comment-block">
                                        <span>Oportunidades de mejora</span>
                                        <p>{{ evaluation.improvement_opportunities || '—' }}</p>
                                    </div>
                                    <div class="comment-block mb-0">
                                        <span>Recomendaciones</span>
                                        <p>{{ evaluation.recommendations || '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Detalle de evaluación'" />
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Loader from '@/components/general/Loader.vue'
import api from '@/utils/axios'
import {
    defaultEvaluationTypeOptions,
    defaultStatusOptions,
    formatDateTime,
    formatScore,
    getValidationMessage,
    groupScoresByDimension,
    labelFromOptions,
} from '@/pages/player-evaluations/utils'

const route = useRoute()
const router = useRouter()

const isLoading = ref(true)
const globalError = ref('')
const evaluation = ref(null)

const statusOptions = defaultStatusOptions
const evaluationTypeOptions = defaultEvaluationTypeOptions

const scoresByDimension = computed(() => groupScoresByDimension(evaluation.value?.scores || []))
const dimensionEntries = computed(() => Object.entries(evaluation.value?.dimension_scores || {}))

async function loadEvaluation() {
    isLoading.value = true
    globalError.value = ''

    try {
        const { data } = await api.get(`/api/v2/player-evaluations/${route.params.id}`)
        evaluation.value = data.data || data
    } catch (error) {
        globalError.value = getValidationMessage(error, 'No fue posible cargar la evaluación.')
    } finally {
        isLoading.value = false
    }
}

async function confirmDelete() {
    if (!evaluation.value || evaluation.value.is_closed) {
        return
    }

    const result = await Swal.fire({
        title: `Eliminar evaluación #${evaluation.value.id}`,
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
    })

    if (!result.isConfirmed) {
        return
    }

    try {
        await api.delete(`/api/v2/player-evaluations/${evaluation.value.id}`)
        showMessage('Evaluación eliminada correctamente.')
        router.push({ name: 'player-evaluations.index' })
    } catch (error) {
        showMessage(getValidationMessage(error, 'No se pudo eliminar la evaluación.'), 'error')
    }
}

watch(
    () => route.params.id,
    () => {
        loadEvaluation()
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

.section-label,
.theme-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    color: inherit;
    border: 1px solid currentColor;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    opacity: 0.8;
}

.stat-card,
.metric-card {
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
    font-size: 1.3rem;
    line-height: 1.2;
}

.player-avatar-xl {
    width: 64px;
    height: 64px;
    object-fit: cover;
    border-radius: 18px;
    border: 1px solid rgba(127, 127, 127, 0.22);
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 1rem;
}

.detail-item {
    border: 1px solid rgba(127, 127, 127, 0.18);
    border-radius: 18px;
    padding: 1rem;
}

.detail-item span,
.comment-block span {
    display: block;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: inherit;
    opacity: 0.75;
    margin-bottom: 0.35rem;
}

.comment-block {
    margin-bottom: 1.25rem;
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

@media (max-width: 991px) {
    .detail-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 575px) {
    .detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>
