<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <h3 class="mb-1">
                        {{ isEditMode ? `Plantilla #${route.params.id}` : 'Nueva plantilla de evaluación' }}
                    </h3>
                    <p class="text-muted mb-0">
                        Define el alcance de la plantilla, el estado y la estructura de criterios que usarán las evaluaciones.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <router-link :to="{ name: 'evaluation-templates.index' }" class="btn btn-secondary btn-sm">
                        Volver al listado
                    </router-link>
                    <button
                        v-if="isEditMode"
                        type="button"
                        class="btn btn-primary btn-sm"
                        :disabled="isDuplicating"
                        @click="duplicateCurrentTemplate"
                    >
                        {{ isDuplicating ? 'Duplicando...' : 'Duplicar versión' }}
                    </button>
                </div>
            </div>
        </template>

        <template #body>
            <div class="position-relative evaluation-template-editor">
                <Loader :is-loading="isLoading" loading-text="Cargando plantilla..." />

                <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between gap-3">
                    <span>{{ globalError }}</span>
                    <button type="button" class="btn btn-sm btn-danger align-self-start" @click="loadPage">
                        Reintentar
                    </button>
                </div>

                <template v-if="formReady">
                    <div v-if="templateState.is_in_use" class="alert alert-warning">
                        Esta plantilla ya tiene evaluaciones asociadas. Puedes revisarla, activar o inactivar desde el listado,
                        pero para cambiar criterios debes duplicar una nueva versión.
                    </div>

                    <div class="surface-card card mb-4">
                        <div class="surface-card-header card-header">
                            <div class="section-label mb-2">Configuración</div>
                            <h5 class="mb-1">Configuración general</h5>
                            <p class="text-muted mb-0">
                                Define el contexto de aplicación de la plantilla dentro de la escuela seleccionada.
                            </p>
                        </div>

                        <div class="surface-card-body card-body">
                            <div class="row g-3">
                                <div class="col-12 col-lg-5">
                                    <label class="form-label">Nombre</label>
                                    <input
                                        v-model.trim="form.name"
                                        type="text"
                                        class="form-control"
                                        :disabled="isReadOnly"
                                        placeholder="Ej. Plantilla base jugadores de campo"
                                    >
                                </div>

                                <div class="col-12 col-md-4 col-lg-2">
                                    <label class="form-label">Año</label>
                                    <input
                                        v-model="form.year"
                                        type="number"
                                        min="2000"
                                        max="2100"
                                        class="form-control"
                                        :disabled="isReadOnly"
                                    >
                                </div>

                                <div class="col-12 col-md-4 col-lg-2">
                                    <label class="form-label">Estado</label>
                                    <select v-model="form.status" class="form-select" :disabled="isReadOnly">
                                        <option v-for="status in editorOptions.statuses" :key="status.value" :value="status.value">
                                            {{ status.label }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12 col-md-4 col-lg-3">
                                    <label class="form-label">Grupo de entrenamiento</label>
                                    <select v-model="form.training_group_id" class="form-select" :disabled="isReadOnly">
                                        <option value="">General</option>
                                        <option
                                            v-for="group in editorOptions.training_groups"
                                            :key="group.id"
                                            :value="String(group.id)"
                                        >
                                            {{ group.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Descripción</label>
                                    <textarea
                                        v-model.trim="form.description"
                                        class="form-control"
                                        rows="3"
                                        :disabled="isReadOnly"
                                        placeholder="Describe el propósito de la plantilla y el tipo de seguimiento que soporta."
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="surface-card card mb-4">
                        <div class="surface-card-header card-header d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div>
                                <div class="section-label mb-2">Criterios</div>
                                <h5 class="mb-1">Criterios de evaluación</h5>
                                <p class="text-muted mb-0">
                                    Crea la estructura que verá el evaluador. Solo se permiten criterios numéricos o de escala.
                                </p>
                            </div>

                            <div class="d-flex flex-wrap gap-2 align-self-start">
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    :disabled="isReadOnly"
                                    @click="addCriterion"
                                >
                                    Agregar criterio
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-secondary btn-sm"
                                    :disabled="isReadOnly"
                                    @click="normalizeSortOrders"
                                >
                                    Reordenar
                                </button>
                            </div>
                        </div>

                        <div class="surface-card-body card-body">
                            <div class="scale-options-box mb-4">
                                <div class="fw-semibold mb-2">Escala disponible para criterios tipo `scale`</div>
                                <div class="d-flex flex-wrap gap-2">
                                    <span
                                        v-for="(label, value) in editorOptions.scale_options"
                                        :key="value"
                                        class="theme-chip scale-option-chip"
                                    >
                                        {{ value }}: {{ label }}
                                    </span>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 90px;">Orden</th>
                                            <th style="min-width: 150px;">Dimensión</th>
                                            <th style="min-width: 170px;">Criterio</th>
                                            <th style="width: 140px;">Tipo</th>
                                            <th style="width: 110px;">Mín.</th>
                                            <th style="width: 110px;">Máx.</th>
                                            <th style="width: 110px;">Peso</th>
                                            <th style="width: 110px;">Oblig.</th>
                                            <th style="min-width: 220px;">Descripción</th>
                                            <th class="text-end" style="width: 110px;">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(criterion, index) in form.criteria" :key="`criterion-${index}`">
                                            <td>
                                                <input
                                                    v-model="criterion.sort_order"
                                                    type="number"
                                                    min="1"
                                                    class="form-control form-control-sm"
                                                    :disabled="isReadOnly"
                                                >
                                            </td>
                                            <td>
                                                <input
                                                    v-model.trim="criterion.dimension"
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    :disabled="isReadOnly"
                                                    placeholder="Técnica"
                                                >
                                            </td>
                                            <td>
                                                <input
                                                    v-model.trim="criterion.name"
                                                    type="text"
                                                    class="form-control form-control-sm"
                                                    :disabled="isReadOnly"
                                                    placeholder="Pase"
                                                >
                                            </td>
                                            <td>
                                                <select
                                                    v-model="criterion.score_type"
                                                    class="form-select form-select-sm"
                                                    :disabled="isReadOnly"
                                                    @change="handleScoreTypeChange(criterion)"
                                                >
                                                    <option
                                                        v-for="scoreType in editorOptions.score_types"
                                                        :key="scoreType.value"
                                                        :value="scoreType.value"
                                                    >
                                                        {{ scoreType.label }}
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    v-model="criterion.min_score"
                                                    type="number"
                                                    step="0.01"
                                                    class="form-control form-control-sm"
                                                    :disabled="isReadOnly || criterion.score_type === 'scale'"
                                                >
                                            </td>
                                            <td>
                                                <input
                                                    v-model="criterion.max_score"
                                                    type="number"
                                                    step="0.01"
                                                    class="form-control form-control-sm"
                                                    :disabled="isReadOnly || criterion.score_type === 'scale'"
                                                >
                                            </td>
                                            <td>
                                                <input
                                                    v-model="criterion.weight"
                                                    type="number"
                                                    min="0.01"
                                                    step="0.01"
                                                    class="form-control form-control-sm"
                                                    :disabled="isReadOnly"
                                                >
                                            </td>
                                            <td>
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input
                                                        v-model="criterion.is_required"
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        :disabled="isReadOnly"
                                                    >
                                                </div>
                                            </td>
                                            <td>
                                                <textarea
                                                    v-model.trim="criterion.description"
                                                    rows="2"
                                                    class="form-control form-control-sm"
                                                    :disabled="isReadOnly"
                                                    placeholder="Observación o guía del criterio"
                                                ></textarea>
                                            </td>
                                            <td class="text-end">
                                                <button
                                                    type="button"
                                                    class="btn btn-danger btn-sm"
                                                    :disabled="isReadOnly || form.criteria.length === 1"
                                                    @click="removeCriterion(index)"
                                                >
                                                    Quitar
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between gap-3">
                        <div class="text-muted small">
                            {{ templateState.is_in_use ? 'Plantilla bloqueada por histórico de uso.' : 'Puedes guardar cambios sobre esta plantilla.' }}
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <router-link :to="{ name: 'evaluation-templates.index' }" class="btn btn-secondary">
                                Cancelar
                            </router-link>
                            <button
                                type="button"
                                class="btn btn-primary"
                                :disabled="isSubmitting || isReadOnly"
                                @click="submitForm"
                            >
                                {{ isSubmitting ? 'Guardando...' : (isEditMode ? 'Guardar cambios' : 'Crear plantilla') }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Administración'" :current="isEditMode ? 'Editar plantilla' : 'Nueva plantilla'" />
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Loader from '@/components/general/Loader.vue'
import { usePageTitle } from '@/composables/use-meta'
import api from '@/utils/axios'
import {
    createEmptyCriterion,
    defaultScoreTypeOptions,
    defaultStatusOptions,
    getValidationMessage,
    normalizeCriterionPayload,
} from '@/pages/admin/evaluation-templates/utils'

const route = useRoute()
const router = useRouter()

const isLoading = ref(false)
const isSubmitting = ref(false)
const isDuplicating = ref(false)
const globalError = ref('')
const formReady = ref(false)

const editorOptions = reactive({
    statuses: [...defaultStatusOptions],
    score_types: [...defaultScoreTypeOptions],
    training_groups: [],
    scale_options: {},
})

const templateState = reactive({
    id: null,
    is_in_use: false,
})

const form = reactive({
    name: '',
    description: '',
    year: String(new Date().getFullYear()),
    training_group_id: '',
    status: 'draft',
    criteria: [createEmptyCriterion(1)],
})

const isEditMode = computed(() => Boolean(route.params.id))
const isReadOnly = computed(() => isEditMode.value && templateState.is_in_use)

usePageTitle(computed(() => isEditMode.value ? 'Editar plantilla de evaluación' : 'Nueva plantilla de evaluación'))

function resetForm() {
    form.name = ''
    form.description = ''
    form.year = String(new Date().getFullYear())
    form.training_group_id = ''
    form.status = 'draft'
    form.criteria = [createEmptyCriterion(1)]

    templateState.id = null
    templateState.is_in_use = false
}

function hydrateForm(template) {
    templateState.id = template.id
    templateState.is_in_use = Boolean(template.is_in_use)

    form.name = template.name || ''
    form.description = template.description || ''
    form.year = String(template.year || new Date().getFullYear())
    form.training_group_id = template.training_group_id ? String(template.training_group_id) : ''
    form.status = template.status || 'draft'
    form.criteria = (template.criteria || []).length
        ? template.criteria
            .slice()
            .sort((left, right) => (left.sort_order || 999) - (right.sort_order || 999))
            .map((criterion, index) => ({
                dimension: criterion.dimension || '',
                name: criterion.name || '',
                description: criterion.description || '',
                score_type: criterion.score_type || 'numeric',
                min_score: criterion.score_type === 'scale' ? '' : criterion.min_score,
                max_score: criterion.score_type === 'scale' ? '' : criterion.max_score,
                weight: criterion.weight ?? 1,
                sort_order: criterion.sort_order || (index + 1),
                is_required: Boolean(criterion.is_required),
            }))
        : [createEmptyCriterion(1)]
}

async function loadOptions() {
    const { data } = await api.get('/api/v2/admin/evaluation-templates/options')

    editorOptions.statuses = data.editor?.statuses?.length ? data.editor.statuses : [...defaultStatusOptions]
    editorOptions.score_types = data.editor?.score_types?.length ? data.editor.score_types : [...defaultScoreTypeOptions]
    editorOptions.training_groups = data.editor?.training_groups || []
    editorOptions.scale_options = data.editor?.scale_options || {}
}

async function loadTemplate() {
    const { data } = await api.get(`/api/v2/admin/evaluation-templates/${route.params.id}`)
    hydrateForm(data.data)
}

async function loadPage() {
    isLoading.value = true
    globalError.value = ''
    formReady.value = false

    try {
        resetForm()

        if (isEditMode.value) {
            await Promise.all([loadOptions(), loadTemplate()])
        } else {
            await loadOptions()
        }

        formReady.value = true
    } catch (error) {
        globalError.value = getValidationMessage(error, 'No fue posible preparar el formulario de la plantilla.')
    } finally {
        isLoading.value = false
    }
}

function addCriterion() {
    form.criteria.push(createEmptyCriterion(form.criteria.length + 1))
}

function removeCriterion(index) {
    if (form.criteria.length === 1) {
        return
    }

    form.criteria.splice(index, 1)
    normalizeSortOrders()
}

function normalizeSortOrders() {
    form.criteria = form.criteria
        .slice()
        .sort((left, right) => Number(left.sort_order || 999) - Number(right.sort_order || 999))
        .map((criterion, index) => ({
            ...criterion,
            sort_order: index + 1,
        }))
}

function handleScoreTypeChange(criterion) {
    if (criterion.score_type === 'scale') {
        criterion.min_score = ''
        criterion.max_score = ''
    } else {
        criterion.min_score = criterion.min_score || 1
        criterion.max_score = criterion.max_score || 5
    }
}

function buildPayload() {
    normalizeSortOrders()

    return {
        name: form.name,
        description: form.description || null,
        year: Number(form.year),
        training_group_id: form.training_group_id ? Number(form.training_group_id) : null,
        status: form.status,
        criteria: form.criteria.map((criterion, index) => normalizeCriterionPayload(criterion, index)),
    }
}

async function submitForm() {
    isSubmitting.value = true

    try {
        const payload = buildPayload()
        let response

        if (isEditMode.value) {
            response = await api.put(`/api/v2/admin/evaluation-templates/${route.params.id}`, payload)
            hydrateForm(response.data.data)
            showMessage(response.data.message || 'Plantilla actualizada correctamente.')
        } else {
            response = await api.post('/api/v2/admin/evaluation-templates', payload)
            showMessage(response.data.message || 'Plantilla creada correctamente.')
            router.replace({ name: 'evaluation-templates.edit', params: { id: response.data.data.id } })
        }
    } catch (error) {
        showMessage(getValidationMessage(error, 'No fue posible guardar la plantilla.'), 'error')
    } finally {
        isSubmitting.value = false
    }
}

async function duplicateCurrentTemplate() {
    if (!isEditMode.value) {
        return
    }

    isDuplicating.value = true

    try {
        const { data } = await api.post(`/api/v2/admin/evaluation-templates/${route.params.id}/duplicate`)
        showMessage(data.message || 'Se creó una nueva versión en borrador.')
        router.push({ name: 'evaluation-templates.edit', params: { id: data.data.id } })
    } catch (error) {
        showMessage(getValidationMessage(error, 'No fue posible duplicar la plantilla.'), 'error')
    } finally {
        isDuplicating.value = false
    }
}

watch(
    () => route.params.id,
    () => {
        loadPage()
    },
    { immediate: true }
)
</script>

<style scoped lang="scss">
@use '../../player-evaluations/shared' as shared;

@include shared.page-shared-styles;

.evaluation-template-editor {
    min-height: 18rem;
}

.surface-card-header {
    background: transparent;
}

.scale-options-box {
    @include shared.outlined-block(1rem);
}

.scale-option-chip {
    opacity: 0.8;
}

.table > :not(caption) > * > * {
    vertical-align: middle;
}
</style>
