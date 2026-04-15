<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <h3 class="mb-1">
                        {{ isEditMode ? `Editar evaluación #${route.params.id}` : 'Nueva evaluación' }}
                    </h3>
                    <p class="text-muted mb-0">
                        {{ isEditMode
                            ? 'Actualiza el seguimiento técnico del jugador sin perder trazabilidad.'
                            : 'Selecciona el contexto de trabajo y registra el desempeño por criterio.' }}
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <router-link :to="{ name: 'player-evaluations.index' }" class="btn btn-secondary btn-sm">
                        Volver al listado
                    </router-link>
                    <router-link
                        v-if="isEditMode"
                        :to="{ name: 'player-evaluations.show', params: { id: route.params.id } }"
                        class="btn btn-primary btn-sm"
                    >
                        Ver detalle
                    </router-link>
                </div>
            </div>
        </template>

        <template #body>
            <div class="position-relative evaluation-editor">
                <Loader :is-loading="isLoading" loading-text="Preparando formulario..." />

                <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between gap-3">
                    <span>{{ globalError }}</span>
                    <button type="button" class="btn btn-sm btn-danger align-self-start" @click="loadEditor">
                        Reintentar
                    </button>
                </div>

                <template v-if="!isEditMode && !formReady">
                    <div class="surface-card card selection-card mb-4">
                        <div class="surface-card-header card-header">
                            <div class="section-label mb-2">Configuración inicial</div>
                            <h5 class="mb-1">Selecciona el contexto de la evaluación</h5>
                            <p class="text-muted mb-0">
                                Esta elección define el jugador, el período y la plantilla de criterios que se van a diligenciar.
                            </p>
                        </div>

                        <div class="surface-card-body card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-lg-5">
                                    <label class="form-label">Inscripción</label>
                                    <select v-model="setup.inscription_id" class="form-select form-select-sm" @change="handleInscriptionChange">
                                        <option value="">Selecciona una inscripción</option>
                                        <option
                                            v-for="inscription in selectionOptions.inscriptions"
                                            :key="inscription.id"
                                            :value="String(inscription.id)"
                                        >
                                            {{ inscription.label }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6 col-lg-3">
                                    <label class="form-label">Período</label>
                                    <select v-model="setup.evaluation_period_id" class="form-select form-select-sm">
                                        <option value="">Selecciona un período</option>
                                        <option v-for="period in selectionOptions.periods" :key="period.id" :value="String(period.id)">
                                            {{ period.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-6 col-lg-4">
                                    <label class="form-label">Plantilla</label>
                                    <select v-model="setup.evaluation_template_id" class="form-select form-select-sm">
                                        <option value="">Selecciona una plantilla</option>
                                        <option
                                            v-for="template in selectionOptions.templates"
                                            :key="template.id"
                                            :value="String(template.id)"
                                        >
                                            {{ template.name }}
                                            {{ template.training_group_name ? `· ${template.training_group_name}` : '· General' }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-12 col-xl-4">
                                    <div class="selection-tip">
                                        <strong>Inscripción</strong>
                                        <span>Determina el jugador y el grupo de referencia.</span>
                                    </div>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <div class="selection-tip">
                                        <strong>Período</strong>
                                        <span>Marca el corte o momento del seguimiento que vas a registrar.</span>
                                    </div>
                                </div>
                                <div class="col-12 col-xl-4">
                                    <div class="selection-tip">
                                        <strong>Plantilla</strong>
                                        <span>Define criterios, pesos y tipos de respuesta del formulario.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-4">
                                <button type="button" class="btn btn-primary" @click="submitSetup">
                                    Continuar al formulario
                                </button>
                                <button type="button" class="btn btn-secondary" @click="resetSetup">
                                    Limpiar selección
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <template v-else-if="formReady">
                    <div class="editor-workspace">
                        <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card card stat-card">
                                <span class="stat-label">Jugador</span>
                                <strong class="stat-value stat-value-sm">{{ formContext.inscription?.player?.name || '—' }}</strong>
                                <small class="text-muted">{{ formContext.inscription?.training_group?.name || 'Sin grupo' }}</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card card stat-card">
                                <span class="stat-label">Período</span>
                                <strong class="stat-value stat-value-sm">{{ formContext.period?.name || '—' }}</strong>
                                <small class="text-muted">{{ formContext.period?.year || 'Sin año' }}</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card card stat-card">
                                <span class="stat-label">Plantilla</span>
                                <strong class="stat-value stat-value-sm">{{ formContext.template?.name || '—' }}</strong>
                                <small class="text-muted">
                                    {{ formContext.template?.training_group_name || 'Aplicación general' }}
                                </small>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="surface-card card stat-card">
                                <span class="stat-label">Avance</span>
                                <strong class="stat-value">{{ completionPercentage }}%</strong>
                                <small class="text-muted">
                                    {{ completedCriteriaCount }} / {{ totalCriteriaCount }} criterios diligenciados
                                </small>
                            </div>
                        </div>
                    </div>

                    <div v-if="isReadOnly" class="alert alert-warning">
                        Esta evaluación está cerrada. Puedes revisar el contenido, pero no modificarlo.
                    </div>

                    <div class="surface-card card mb-4">
                        <div class="surface-card-header card-header">
                            <div class="section-label mb-2">Configuración</div>
                            <h5 class="mb-1">Estado del registro</h5>
                            <p class="text-muted mb-0">
                                Ajusta el tipo de evaluación, el estado y la fecha del corte.
                            </p>
                        </div>

                        <div class="surface-card-body card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-md-4">
                                    <label class="form-label">Tipo de evaluación</label>
                                    <select v-model="form.evaluation_type" class="form-select form-select-sm" :disabled="isReadOnly">
                                        <option
                                            v-for="type in evaluationTypeOptions"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Estado</label>
                                    <select v-model="form.status" class="form-select form-select-sm" :disabled="isReadOnly">
                                        <option
                                            v-for="status in statusOptions"
                                            :key="status.value"
                                            :value="status.value"
                                        >
                                            {{ status.label }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4">
                                    <label class="form-label">Fecha de evaluación</label>
                                    <input
                                        v-model="form.evaluated_at"
                                        type="datetime-local"
                                        class="form-control form-control-sm"
                                        :disabled="isReadOnly"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="surface-card card mb-4">
                        <div class="surface-card-header card-header">
                            <div class="section-label mb-2">Preview</div>
                            <h5 class="mb-1">Lectura del progreso</h5>
                            <p class="text-muted mb-0">
                                El cálculo usa el mismo promedio ponderado por criterio que aplica el backend al guardar.
                            </p>
                        </div>

                        <div class="surface-card-body card-body">
                            <div class="row g-3 align-items-center">
                                <div class="col-12 col-lg-3">
                                    <div class="summary-pill">
                                        <span>Promedio general</span>
                                        <strong>{{ overallScorePreview }}</strong>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-3">
                                    <div class="summary-pill">
                                        <span>Criterios obligatorios pendientes</span>
                                        <strong>{{ missingRequiredCriteria.length }}</strong>
                                    </div>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <label class="form-label d-flex justify-content-between">
                                        <span>Avance ponderado</span>
                                        <span>{{ overallProgressPercentage }}%</span>
                                    </label>
                                    <div class="progress progress-lg">
                                        <div
                                            class="progress-bar"
                                            :class="progressBarClass"
                                            :style="{ width: `${overallProgressPercentage}%` }"
                                        >
                                            {{ overallProgressPercentage }}%
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="mustCompleteRequiredCriteria && missingRequiredCriteria.length" class="alert alert-warning mt-3 mb-0">
                                Para dejar la evaluación en estado
                                <strong>{{ currentStatusLabel.toLowerCase() }}</strong>
                                debes diligenciar todos los criterios obligatorios.
                                Faltan: {{ missingRequiredCriteria.join(', ') }}.
                            </div>
                        </div>
                    </div>

                    <div class="dimensions-toolbar mb-3">
                        <div>
                            <div class="section-label mb-2">Dimensiones</div>
                            <h5 class="mb-1">Evaluación por bloques</h5>
                            <p class="text-muted mb-0">
                                Abre solo la dimensión que estés diligenciando para mantener el formulario más compacto.
                            </p>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button
                                type="button"
                                class="btn btn-secondary btn-sm"
                                :disabled="areAllDimensionsExpanded"
                                @click="expandAllDimensions"
                            >
                                Expandir todas
                            </button>
                            <button
                                type="button"
                                class="btn btn-secondary btn-sm"
                                :disabled="!hasExpandedDimensions"
                                @click="collapseAllDimensions"
                            >
                                Contraer todas
                            </button>
                        </div>
                    </div>

                    <div
                        v-for="summary in dimensionSummaries"
                        :key="summary.dimension"
                        class="surface-card card dimension-card mb-3"
                    >
                        <div
                            class="surface-card-header card-header dimension-toggle"
                            role="button"
                            tabindex="0"
                            :aria-expanded="isDimensionExpanded(summary.dimension)"
                            @click="toggleDimension(summary.dimension)"
                            @keydown.enter.prevent="toggleDimension(summary.dimension)"
                            @keydown.space.prevent="toggleDimension(summary.dimension)"
                        >
                            <div class="dimension-toggle-main">
                                <div class="dimension-title-row">
                                    <h5 class="mb-0">{{ summary.dimension }}</h5>
                                    <span
                                        class="criterion-state-chip"
                                        :class="{ 'is-filled': summary.required_pending_count === 0 }"
                                    >
                                        {{ summary.required_pending_count ? `${summary.required_pending_count} obligatorios pendientes` : 'Al día' }}
                                    </span>
                                </div>

                                <div class="dimension-meta-row">
                                    <span>Promedio {{ formatScore(summary.average) }}</span>
                                    <span>{{ summary.completion.completed }} / {{ summary.completion.total }} diligenciados</span>
                                    <span>{{ summary.criteria.length }} criterios</span>
                                </div>
                            </div>

                            <div class="dimension-toggle-side">
                                <span class="theme-chip">
                                    {{ isDimensionExpanded(summary.dimension) ? 'Ocultar' : 'Abrir' }}
                                </span>
                            </div>
                        </div>

                        <div v-show="isDimensionExpanded(summary.dimension)" class="surface-card-body card-body">
                            <div class="criterion-list">
                                <div v-for="criterion in summary.criteria" :key="criterion.id" class="criterion-row">
                                    <div class="criterion-row-main">
                                        <div class="criterion-title-row">
                                            <div class="criterion-title">{{ criterion.name }}</div>
                                            <span
                                                class="criterion-state-chip"
                                                :class="{ 'is-filled': criterionHasValue(scoreForm[criterion.id]) }"
                                                :title="criterionDisplayValue(criterion)"
                                            >
                                                {{ criterionDisplayValue(criterion) }}
                                            </span>
                                        </div>

                                        <div class="criterion-meta">
                                            <!-- <span>{{ criterion.code || 'Sin código' }}</span>
                                            <span>Tipo {{ criterion.score_type }}</span> -->
                                            <span v-if="criterion.score_type === 'numeric'">
                                                Rango {{ formatScore(criterion.min_score) }} - {{ formatScore(criterion.max_score) }}
                                            </span>
                                            <span>Peso {{ formatScore(criterion.weight || 1) }}</span>
                                            <span v-if="criterion.is_required">Obligatorio</span>
                                        </div>

                                        <p v-if="criterion.description" class="criterion-description">
                                            {{ criterion.description }}
                                        </p>
                                    </div>

                                    <div class="criterion-row-input">
                                        <label class="form-label">
                                            {{ criterion.score_type === 'numeric' ? 'Puntaje' : 'Selección' }}
                                        </label>

                                        <input
                                            v-if="criterion.score_type === 'numeric'"
                                            v-model="scoreForm[criterion.id].score"
                                            type="number"
                                            step="0.01"
                                            class="form-control form-control-sm"
                                            :min="criterion.min_score ?? 0"
                                            :max="criterion.max_score ?? 5"
                                            :disabled="isReadOnly"
                                        >

                                        <select
                                            v-else
                                            v-model="scoreForm[criterion.id].scale_value"
                                            class="form-select form-select-sm"
                                            :disabled="isReadOnly"
                                        >
                                            <option value="">Selecciona una opción</option>
                                            <option
                                                v-for="(label, value) in criterion.scale_options || {}"
                                                :key="value"
                                                :value="value"
                                            >
                                                {{ label }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="criterion-row-comment">
                                        <label class="form-label">Comentario</label>
                                        <input
                                            v-model="scoreForm[criterion.id].comment"
                                            type="text"
                                            class="form-control form-control-sm"
                                            placeholder="Observación sobre este criterio"
                                            :disabled="isReadOnly"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-12 col-xl-6">
                            <div class="surface-card card h-100">
                                <div class="surface-card-header card-header">
                                    <div class="section-label mb-2">Conclusiones</div>
                                    <h5 class="mb-1">Lectura global</h5>
                                </div>
                                <div class="surface-card-body card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Comentario general</label>
                                        <textarea
                                            v-model="form.general_comment"
                                            class="form-control form-control-sm"
                                            rows="4"
                                            :disabled="isReadOnly"
                                        ></textarea>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label">Fortalezas</label>
                                        <textarea
                                            v-model="form.strengths"
                                            class="form-control form-control-sm"
                                            rows="4"
                                            :disabled="isReadOnly"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-6">
                            <div class="surface-card card h-100">
                                <div class="surface-card-header card-header">
                                    <div class="section-label mb-2">Plan de mejora</div>
                                    <h5 class="mb-1">Siguiente seguimiento</h5>
                                </div>
                                <div class="surface-card-body card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Oportunidades de mejora</label>
                                        <textarea
                                            v-model="form.improvement_opportunities"
                                            class="form-control form-control-sm"
                                            rows="4"
                                            :disabled="isReadOnly"
                                        ></textarea>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label">Recomendaciones</label>
                                        <textarea
                                            v-model="form.recommendations"
                                            class="form-control form-control-sm"
                                            rows="4"
                                            :disabled="isReadOnly"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between gap-3">
                        <div class="d-flex flex-wrap gap-2">
                            <button
                                v-if="!isEditMode"
                                type="button"
                                class="btn btn-secondary"
                                @click="changeSelection"
                            >
                                Cambiar selección
                            </button>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <router-link :to="{ name: 'player-evaluations.index' }" class="btn btn-secondary">
                                Cancelar
                            </router-link>
                            <button
                                type="button"
                                class="btn btn-primary"
                                :disabled="isSubmitting || isReadOnly"
                                @click="submitEvaluation"
                            >
                                {{ isSubmitting ? 'Guardando...' : (isEditMode ? 'Actualizar evaluación' : 'Guardar evaluación') }}
                            </button>
                        </div>
                    </div>
                    </div>
                </template>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="isEditMode ? 'Editar evaluación' : 'Nueva evaluación'" />
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Loader from '@/components/general/Loader.vue'
import api from '@/utils/axios'
import {
    criterionHasValue,
    datetimeLocalValue,
    defaultEvaluationTypeOptions,
    defaultStatusOptions,
    formatScore,
    getValidationMessage,
    labelFromOptions,
    numberOrNull,
    toQueryObject,
} from '@/pages/player-evaluations/utils'

const route = useRoute()
const router = useRouter()

const isLoading = ref(true)
const isSubmitting = ref(false)
const globalError = ref('')
const formReady = ref(false)
const loadedEvaluation = ref(null)

const selectionOptions = reactive({
    inscriptions: [],
    periods: [],
    templates: [],
})

const statusOptions = ref([...defaultStatusOptions])
const evaluationTypeOptions = ref([...defaultEvaluationTypeOptions])

const setup = reactive({
    inscription_id: '',
    evaluation_period_id: '',
    evaluation_template_id: '',
})

const formContext = reactive({
    inscription: null,
    period: null,
    template: null,
    criteria_by_dimension: {},
})

const form = reactive({
    evaluation_type: 'periodic',
    status: 'draft',
    evaluated_at: datetimeLocalValue(new Date().toISOString()),
    general_comment: '',
    strengths: '',
    improvement_opportunities: '',
    recommendations: '',
})

const scoreForm = reactive({})
const expandedDimensions = ref([])

const isEditMode = computed(() => Boolean(route.params.id))
const isReadOnly = computed(() => Boolean(loadedEvaluation.value?.is_closed))

const dimensionEntries = computed(() => Object.entries(formContext.criteria_by_dimension || {}))

const criteriaList = computed(() => dimensionEntries.value.flatMap(([, criteria]) => criteria))

const completedCriteriaCount = computed(() => criteriaList.value.filter((criterion) => criterionHasValue(scoreForm[criterion.id])).length)
const totalCriteriaCount = computed(() => criteriaList.value.length)
const completionPercentage = computed(() => {
    if (!totalCriteriaCount.value) return 0
    return Math.round((completedCriteriaCount.value / totalCriteriaCount.value) * 100)
})

const mustCompleteRequiredCriteria = computed(() => ['completed', 'closed'].includes(form.status))
const currentStatusLabel = computed(() => labelFromOptions(statusOptions.value, form.status, form.status))

const missingRequiredCriteria = computed(() => (
    criteriaList.value
        .filter((criterion) => criterion.is_required)
        .filter((criterion) => !criterionHasValue(scoreForm[criterion.id]))
        .map((criterion) => criterion.name)
))

const dimensionAverages = computed(() => {
    return dimensionEntries.value.reduce((accumulator, [dimension, criteria]) => {
        let weightedTotal = 0
        let weightTotal = 0

        criteria.forEach((criterion) => {
            if (criterion.score_type !== 'numeric') {
                return
            }

            const value = numberOrNull(scoreForm[criterion.id]?.score)
            const weight = Number(criterion.weight ?? 1)

            if (value === null) {
                return
            }

            weightedTotal += value * weight
            weightTotal += weight
        })

        accumulator[dimension] = weightTotal > 0 ? Number((weightedTotal / weightTotal).toFixed(2)) : null
        return accumulator
    }, {})
})

const dimensionCompletion = computed(() => {
    return dimensionEntries.value.reduce((accumulator, [dimension, criteria]) => {
        accumulator[dimension] = {
            completed: criteria.filter((criterion) => criterionHasValue(scoreForm[criterion.id])).length,
            total: criteria.length,
        }
        return accumulator
    }, {})
})

const dimensionSummaries = computed(() => (
    dimensionEntries.value.map(([dimension, criteria]) => ({
        dimension,
        criteria,
        average: dimensionAverages.value[dimension] ?? null,
        completion: dimensionCompletion.value[dimension] || {
            completed: 0,
            total: criteria.length,
        },
        required_pending_count: criteria.filter((criterion) => (
            criterion.is_required && !criterionHasValue(scoreForm[criterion.id])
        )).length,
    }))
))

const overallComputation = computed(() => {
    let weightedTotal = 0
    let weightTotal = 0
    let weightedMax = 0

    criteriaList.value.forEach((criterion) => {
        if (criterion.score_type !== 'numeric') {
            return
        }

        const value = numberOrNull(scoreForm[criterion.id]?.score)
        const weight = Number(criterion.weight ?? 1)
        const maxScore = Number(criterion.max_score ?? 5)

        weightedMax += maxScore * weight

        if (value === null) {
            return
        }

        weightedTotal += value * weight
        weightTotal += weight
    })

    const average = weightTotal > 0 ? weightedTotal / weightTotal : null
    const progress = weightedTotal > 0 && weightedMax > 0
        ? Math.round((weightedTotal / weightedMax) * 100)
        : 0

    return {
        average: average === null ? null : Number(average.toFixed(2)),
        progress,
    }
})

const overallScorePreview = computed(() => formatScore(overallComputation.value.average))
const overallProgressPercentage = computed(() => overallComputation.value.progress)
const progressBarClass = computed(() => {
    if (overallProgressPercentage.value < 40) return 'bg-danger'
    if (overallProgressPercentage.value < 70) return 'bg-warning'
    return 'bg-success'
})

const areAllDimensionsExpanded = computed(() => (
    dimensionEntries.value.length > 0 &&
    dimensionEntries.value.every(([dimension]) => expandedDimensions.value.includes(dimension))
))

const hasExpandedDimensions = computed(() => expandedDimensions.value.length > 0)

function resetFormState() {
    formReady.value = false
    loadedEvaluation.value = null
    formContext.inscription = null
    formContext.period = null
    formContext.template = null
    formContext.criteria_by_dimension = {}
    expandedDimensions.value = []

    form.evaluation_type = 'periodic'
    form.status = 'draft'
    form.evaluated_at = datetimeLocalValue(new Date().toISOString())
    form.general_comment = ''
    form.strengths = ''
    form.improvement_opportunities = ''
    form.recommendations = ''

    Object.keys(scoreForm).forEach((key) => {
        delete scoreForm[key]
    })
}

function syncSetupFromRoute() {
    setup.inscription_id = String(route.query.inscription_id || '')
    setup.evaluation_period_id = String(route.query.evaluation_period_id || '')
    setup.evaluation_template_id = String(route.query.evaluation_template_id || '')
}

async function loadSelectionOptions(inscriptionId = setup.inscription_id) {
    const { data } = await api.get('/api/v2/player-evaluations/options', {
        params: inscriptionId ? { inscription_id: inscriptionId } : {},
    })

    selectionOptions.inscriptions = data.selection?.inscriptions || []
    selectionOptions.periods = data.selection?.periods || []
    selectionOptions.templates = data.selection?.templates || []
}

function initializeScores(criteriaByDimension, existingScores = {}) {
    Object.keys(scoreForm).forEach((key) => {
        delete scoreForm[key]
    })

    Object.values(criteriaByDimension || {}).flat().forEach((criterion) => {
        const saved = existingScores[criterion.id] || {}
        scoreForm[criterion.id] = {
            template_criterion_id: criterion.id,
            score: saved.score ?? '',
            scale_value: saved.scale_value ?? '',
            comment: saved.comment ?? '',
        }
    })
}

function resetExpandedDimensions(criteriaByDimension = formContext.criteria_by_dimension) {
    const entries = Object.entries(criteriaByDimension || {})
    const firstPendingDimension = entries.find(([, criteria]) => (
        criteria.some((criterion) => criterion.is_required && !criterionHasValue(scoreForm[criterion.id]))
    ))

    if (firstPendingDimension) {
        expandedDimensions.value = [firstPendingDimension[0]]
        return
    }

    expandedDimensions.value = entries.length ? [entries[0][0]] : []
}

function isDimensionExpanded(dimension) {
    return expandedDimensions.value.includes(dimension)
}

function toggleDimension(dimension) {
    if (isDimensionExpanded(dimension)) {
        expandedDimensions.value = expandedDimensions.value.filter((item) => item !== dimension)
        return
    }

    expandedDimensions.value = [...expandedDimensions.value, dimension]
}

function expandAllDimensions() {
    expandedDimensions.value = dimensionEntries.value.map(([dimension]) => dimension)
}

function collapseAllDimensions() {
    expandedDimensions.value = []
}

function applyPayload(payload) {
    resetFormState()

    loadedEvaluation.value = payload.evaluation?.data || payload.evaluation || null
    formContext.inscription = payload.inscription || null
    formContext.period = payload.period || null
    formContext.template = payload.template || null
    formContext.criteria_by_dimension = payload.criteria_by_dimension || {}

    statusOptions.value = payload.status_options?.length ? payload.status_options : [...defaultStatusOptions]
    evaluationTypeOptions.value = payload.evaluation_type_options?.length
        ? payload.evaluation_type_options
        : [...defaultEvaluationTypeOptions]

    const sourceEvaluation = loadedEvaluation.value

    form.evaluation_type = sourceEvaluation?.evaluation_type || 'periodic'
    form.status = sourceEvaluation?.status || 'draft'
    form.evaluated_at = datetimeLocalValue(sourceEvaluation?.evaluated_at || new Date().toISOString())
    form.general_comment = sourceEvaluation?.general_comment || ''
    form.strengths = sourceEvaluation?.strengths || ''
    form.improvement_opportunities = sourceEvaluation?.improvement_opportunities || ''
    form.recommendations = sourceEvaluation?.recommendations || ''

    initializeScores(formContext.criteria_by_dimension, payload.existingScores || {})
    resetExpandedDimensions(formContext.criteria_by_dimension)
    formReady.value = true
}

async function loadCreateForm() {
    const { data } = await api.get('/api/v2/player-evaluations/create', {
        params: {
            inscription_id: setup.inscription_id,
            evaluation_period_id: setup.evaluation_period_id,
            evaluation_template_id: setup.evaluation_template_id,
        },
    })

    applyPayload(data)
}

async function loadEditForm() {
    const { data } = await api.get(`/api/v2/player-evaluations/${route.params.id}/edit`)
    applyPayload(data)
}

async function loadEditor() {
    isLoading.value = true
    globalError.value = ''

    try {
        syncSetupFromRoute()

        if (isEditMode.value) {
            await loadEditForm()
        } else {
            await loadSelectionOptions()

            if (setup.inscription_id && setup.evaluation_period_id && setup.evaluation_template_id) {
                await loadCreateForm()
            } else {
                resetFormState()
            }
        }
    } catch (error) {
        globalError.value = getValidationMessage(error, 'No fue posible preparar el formulario de evaluación.')
    } finally {
        isLoading.value = false
    }
}

async function handleInscriptionChange() {
    setup.evaluation_template_id = ''

    try {
        await loadSelectionOptions(setup.inscription_id)
    } catch (error) {
        showMessage(getValidationMessage(error, 'No fue posible cargar las plantillas disponibles.'), 'error')
    }
}

function submitSetup() {
    if (!setup.inscription_id || !setup.evaluation_period_id || !setup.evaluation_template_id) {
        showMessage('Selecciona inscripción, período y plantilla para continuar.', 'warning')
        return
    }

    router.replace({
        name: 'player-evaluations.create',
        query: toQueryObject(setup),
    })
}

function resetSetup() {
    resetFormState()

    router.replace({
        name: 'player-evaluations.create',
    })
}

function changeSelection() {
    resetSetup()
}

function criterionDisplayValue(criterion) {
    const item = scoreForm[criterion.id]

    if (!item) {
        return 'Sin registrar'
    }

    if (criterion.score_type === 'numeric') {
        return item.score !== '' ? formatScore(item.score) : 'Sin registrar'
    }

    if (!item.scale_value) {
        return 'Sin registrar'
    }

    return criterion.scale_options?.[item.scale_value] || item.scale_value
}

function buildPayload() {
    return {
        inscription_id: Number(formContext.inscription?.id),
        evaluation_period_id: Number(formContext.period?.id),
        evaluation_template_id: Number(formContext.template?.id),
        evaluation_type: form.evaluation_type,
        status: form.status,
        evaluated_at: form.evaluated_at || null,
        general_comment: form.general_comment || null,
        strengths: form.strengths || null,
        improvement_opportunities: form.improvement_opportunities || null,
        recommendations: form.recommendations || null,
        scores: criteriaList.value.map((criterion) => ({
            template_criterion_id: criterion.id,
            score: criterion.score_type === 'numeric' ? numberOrNull(scoreForm[criterion.id]?.score) : null,
            scale_value: criterion.score_type === 'numeric' ? null : (scoreForm[criterion.id]?.scale_value || null),
            comment: scoreForm[criterion.id]?.comment || null,
        })),
    }
}

async function submitEvaluation() {
    if (isReadOnly.value) {
        return
    }

    if (mustCompleteRequiredCriteria.value && missingRequiredCriteria.value.length) {
        showMessage(
            `Faltan criterios obligatorios: ${missingRequiredCriteria.value.join(', ')}.`,
            'warning'
        )
        return
    }

    isSubmitting.value = true

    try {
        const payload = buildPayload()
        let response

        if (isEditMode.value) {
            response = await api.put(`/api/v2/player-evaluations/${route.params.id}`, payload)
        } else {
            response = await api.post('/api/v2/player-evaluations', payload)
        }

        const evaluationId = response.data?.data?.id || route.params.id
        showMessage(isEditMode.value ? 'Evaluación actualizada correctamente.' : 'Evaluación creada correctamente.')
        router.push({ name: 'player-evaluations.show', params: { id: evaluationId } })
    } catch (error) {
        showMessage(getValidationMessage(error, 'No se pudo guardar la evaluación.'), 'error')
    } finally {
        isSubmitting.value = false
    }
}

watch(
    () => route.fullPath,
    () => {
        loadEditor()
    },
    { immediate: true }
)
</script>

<style scoped>
.editor-workspace {
    max-width: 1160px;
    margin: 0 auto;
}

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

.selection-card {
    overflow: hidden;
}

.selection-tip {
    border: 1px solid rgba(127, 127, 127, 0.18);
    border-radius: 18px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    height: 100%;
}

.selection-tip strong,
.stat-label {
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: inherit;
    opacity: 0.75;
}

.stat-card {
    padding: 1.2rem 1.3rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.stat-value {
    font-size: 1.9rem;
    line-height: 1;
}

.stat-value-sm {
    font-size: 1.25rem;
    line-height: 1.25;
}

.summary-pill {
    border: 1px solid rgba(127, 127, 127, 0.18);
    border-radius: 18px;
    padding: 1rem 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    height: 100%;
}

.summary-pill span {
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: inherit;
    opacity: 0.75;
}

.summary-pill strong {
    font-size: 1.75rem;
}

.progress-lg {
    height: 1.25rem;
    border-radius: 999px;
}

.dimensions-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 1rem;
}

.dimension-card .surface-card-header {
    padding: 1rem 1.25rem;
}

.dimension-card .surface-card-body {
    padding: 0.25rem 1.25rem 1rem;
}

.dimension-toggle {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    cursor: pointer;
}

.dimension-toggle-main,
.criterion-row-main,
.criterion-row-input,
.criterion-row-comment {
    min-width: 0;
}

.dimension-toggle-main {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.dimension-toggle-side {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.dimension-title-row,
.criterion-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
}

.dimension-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem 1rem;
    font-size: 0.82rem;
    opacity: 0.75;
}

.criterion-list {
    display: flex;
    flex-direction: column;
}

.criterion-row {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(180px, 0.75fr) minmax(240px, 1fr);
    gap: 1rem;
    padding: 0.95rem 0;
}

.criterion-row + .criterion-row {
    border-top: 1px solid rgba(127, 127, 127, 0.16);
}

.criterion-title-row {
    align-items: flex-start;
}

.criterion-title {
    font-weight: 700;
    margin-bottom: 0;
}

.criterion-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    font-size: 0.82rem;
    opacity: 0.75;
    margin-top: 0.4rem;
}

.criterion-description {
    margin-top: 0.45rem;
    margin-bottom: 0;
    opacity: 0.75;
}

.criterion-row-input .form-label,
.criterion-row-comment .form-label {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    opacity: 0.7;
    margin-bottom: 0.35rem;
}

.criterion-state-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    max-width: min(100%, 220px);
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    color: inherit;
    border: 1px solid rgba(127, 127, 127, 0.22);
    font-size: 0.72rem;
    font-weight: 600;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    opacity: 0.75;
}

.criterion-state-chip.is-filled {
    opacity: 1;
}

.criterion-row-input :deep(.form-control),
.criterion-row-input :deep(.form-select),
.criterion-row-comment :deep(.form-control) {
    width: 100%;
}

@media (max-width: 1199px) {
    .criterion-row {
        grid-template-columns: minmax(0, 1fr) minmax(220px, 280px);
    }

    .criterion-row-comment {
        grid-column: 1 / -1;
    }
}

@media (max-width: 767px) {
    .dimensions-toolbar,
    .dimension-toggle,
    .dimension-title-row,
    .criterion-title-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .dimension-toggle-side {
        width: 100%;
        justify-content: flex-start;
    }

    .criterion-row {
        grid-template-columns: 1fr;
    }
}
</style>
