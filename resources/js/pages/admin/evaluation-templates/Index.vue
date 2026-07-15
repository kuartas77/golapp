<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3" data-tour="admin-evaluation-templates-actions">
                <div>
                    <h3 class="mb-1">Plantillas de evaluación</h3>
                    <p class="text-muted mb-0">
                        Administra las plantillas y sus criterios antes de usarlas en evaluaciones de jugadores.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <router-link :to="{ name: 'evaluation-templates.create' }" class="btn btn-primary btn-sm">
                        Nueva plantilla
                    </router-link>
                    <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question me-2"></i>
                        Guia
                    </button>
                </div>
            </div>
        </template>

        <template #body>
            <div class="position-relative evaluation-templates-page">
                <Loader :is-loading="isLoading" loading-text="Cargando plantillas..." />

                <div class="surface-card card overflow-visible mb-4" data-tour="admin-evaluation-templates-filters">
                    <div class="surface-card-body card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-lg-4">
                                <label class="form-label">Buscar</label>
                                <input
                                    v-model.trim="filters.search"
                                    type="text"
                                    class="form-control form-control-sm"
                                    placeholder="Nombre o descripción"
                                    @keyup.enter="applyFilters"
                                >
                            </div>

                            <div class="col-12 col-md-4 col-lg-2">
                                <label class="form-label">Año</label>
                                <select v-model="filters.year" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option v-for="year in filterOptions.years" :key="year.value" :value="String(year.value)">
                                        {{ year.label }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4 col-lg-2">
                                <label class="form-label">Estado</label>
                                <select v-model="filters.status" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4 col-lg-4">
                                <label class="form-label">Grupo de entrenamiento</label>
                                <CustomSelect2
                                    v-model="filters.training_group_id"
                                    :options="trainingGroupOptions"
                                    placeholder="Todos"
                                    search-placeholder="Buscar grupo..."
                                />
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button type="button" class="btn btn-primary btn-sm" @click="applyFilters">
                                Aplicar filtros
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" @click="resetFilters">
                                Limpiar
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" @click="reloadTable">
                                Recargar
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between gap-3">
                    <span>{{ globalError }}</span>
                    <button type="button" class="btn btn-sm btn-danger align-self-start" @click="reloadTable">
                        Reintentar
                    </button>
                </div>

                <div class="surface-card card overflow-hidden" data-tour="admin-evaluation-templates-table">
                    <div class="surface-card-body card-body p-1">
                        <DatatableTemplate
                            id="evaluation_templates_table"
                            ref="templateTable"
                            :options="options"
                        >
                            <template #thead>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Plantilla</th>
                                        <th>Grupo</th>
                                        <th>Año</th>
                                        <th>Versión</th>
                                        <th>Criterios</th>
                                        <th>Uso</th>
                                        <th>Estado</th>
                                        <th>Actualizada</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                            </template>

                            <template #template-name="props">
                                <div class="fw-semibold">{{ props.rowData.name }}</div>
                                <div class="small text-muted">{{ props.rowData.description || 'Sin descripción' }}</div>
                            </template>

                            <template #template-version="props">
                                v{{ props.cellData }}
                            </template>

                            <template #template-usage="props">
                                <span class="theme-chip usage-chip" :class="{ 'is-in-use': props.rowData.is_in_use }">
                                    {{ props.rowData.is_in_use ? `${props.rowData.evaluations_count || 0} evaluaciones` : 'Sin uso' }}
                                </span>
                            </template>

                            <template #template-status="props">
                                <span class="badge text-uppercase" :class="`badge-${statusVariant(props.rowData.status)}`">
                                    {{ labelFromOptions(filterOptions.statuses, props.rowData.status, props.rowData.status) }}
                                </span>
                            </template>

                            <template #template-date="props">
                                {{ formatDateTime(props.cellData) }}
                            </template>

                            <template #template-actions="props">
                                <div class="d-flex flex-wrap justify-content-end gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-primary btn-sm"
                                        @click="goToTemplate(props.rowData)"
                                    >
                                        {{ props.rowData.is_in_use ? 'Ver' : 'Editar' }}
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-secondary btn-sm"
                                        @click="duplicateTemplate(props.rowData)"
                                    >
                                        Duplicar
                                    </button>

                                    <button
                                        v-if="props.rowData.status !== 'active'"
                                        type="button"
                                        class="btn btn-success btn-sm"
                                        @click="changeStatus(props.rowData, 'active')"
                                    >
                                        Activar
                                    </button>

                                    <button
                                        v-if="props.rowData.status !== 'inactive'"
                                        type="button"
                                        class="btn btn-warning btn-sm"
                                        @click="changeStatus(props.rowData, 'inactive')"
                                    >
                                        Inactivar
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-danger btn-sm"
                                        :disabled="!props.rowData.can_delete"
                                        @click="confirmDelete(props.rowData)"
                                    >
                                        Eliminar
                                    </button>
                                </div>
                            </template>
                        </DatatableTemplate>
                    </div>
                </div>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Configuración'" :current="'Plantillas de evaluación'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import CustomSelect2 from '@/components/form/CustomSelect2.vue'
import Loader from '@/components/general/Loader.vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { usePageTitle } from '@/composables/use-meta'
import api from '@/utils/axios'
import configLanguaje from '@/utils/datatableUtils'
import {
    defaultStatusOptions,
    formatDateTime,
    getValidationMessage,
    labelFromOptions,
    statusVariantMap,
    toQueryObject,
} from '@/pages/admin/evaluation-templates/utils'
import { evaluationTemplatesIndexTutorial } from '@/tutorials/admin'

usePageTitle('Plantillas de evaluación')

const route = useRoute()
const router = useRouter()
const tutorial = usePageTutorial(evaluationTemplatesIndexTutorial)

const isLoading = ref(false)
const globalError = ref('')
const templateTable = ref(null)

const filterOptions = reactive({
    years: [],
    statuses: [...defaultStatusOptions],
    training_groups: [],
})

const filters = reactive({
    search: '',
    year: '',
    status: '',
    training_group_id: '',
})

function statusVariant(status) {
    return statusVariantMap[status] || 'secondary'
}

function mapTrainingGroupOption(group) {
    const value = String(group.value ?? group.id)
    const label = group.label || (group.is_complementary ? `${group.name} (Complementario)` : group.name)

    return {
        value,
        label,
        meta: group.is_complementary ? 'Complementario' : '',
    }
}

const trainingGroupOptions = computed(() => (
    filterOptions.training_groups.map(mapTrainingGroupOption)
))

function syncFiltersFromRoute() {
    filters.search = String(route.query.search || '')
    filters.year = String(route.query.year || '')
    filters.status = String(route.query.status || '')
    filters.training_group_id = String(route.query.training_group_id || '')
}

async function loadOptions() {
    const { data } = await api.get('/api/v2/admin/evaluation-templates/options')

    filterOptions.years = data.filters?.years || []
    filterOptions.statuses = data.filters?.statuses?.length ? data.filters.statuses : [...defaultStatusOptions]
    filterOptions.training_groups = data.filters?.training_groups || []
}

const columns = [
    { data: 'id', name: 'evaluation_templates.id', width: '1%', className: 'dt-head-center dt-body-center' },
    { data: 'name', name: 'name', render: '#template-name' },
    { data: 'training_group_name', name: 'training_group_name', defaultContent: 'General' },
    { data: 'year', name: 'year', className: 'dt-head-center dt-body-center' },
    { data: 'version', name: 'version', render: '#template-version', className: 'dt-head-center dt-body-center' },
    { data: 'criteria_count', name: 'criteria_count', className: 'dt-head-center dt-body-center', searchable: false, orderable: false },
    { data: 'evaluations_count', name: 'evaluations_count', render: '#template-usage', className: 'dt-head-center dt-body-center', searchable: false, orderable: false },
    { data: 'status', name: 'status', render: '#template-status', className: 'dt-head-center dt-body-center' },
    { data: 'updated_at', name: 'updated_at', render: '#template-date' },
    { data: 'id', name: 'actions', render: '#template-actions', searchable: false, orderable: false, className: 'dt-head-center dt-body-end' },
]

const options = {
    ...configLanguaje,
    lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
    serverSide: true,
    pipeline: { pages: 5 },
    processing: true,
    order: [[3, 'desc'], [1, 'asc'], [4, 'desc']],
    columnDefs: [
        { responsivePriority: 1, targets: columns.length - 1 },
        { responsivePriority: 2, targets: 1 },
        { targets: [0, 3, 4, 5, 6, 7], className: 'dt-head-center dt-body-center' },
        { targets: [9], className: 'dt-head-right dt-body-right' },
        { targets: [1], width: '28%' },
    ],
    ajax: async (data, callback) => {
        try {
            globalError.value = ''
            const response = await api.get('/api/v2/admin/evaluation-templates', {
                params: {
                    ...data,
                    ...toQueryObject({
                        year: filters.year,
                        status: filters.status,
                        training_group_id: filters.training_group_id,
                        template_search: filters.search,
                    }),
                },
            })

            callback(response.data)
        } catch (error) {
            globalError.value = getValidationMessage(error, 'No fue posible cargar las plantillas.')
            callback({
                data: [],
                recordsTotal: 0,
                recordsFiltered: 0,
                draw: data.draw,
            })
        }
    },
    columns,
}

function getDataTable() {
    return templateTable.value?.table?.dt ?? null
}

function reloadTable() {
    const dt = getDataTable()

    if (!dt) {
        return
    }

    dt.clearPipeline()
    dt.ajax.reload(null, false)
}

function applyFilters() {
    router.replace({
        name: 'evaluation-templates.index',
        query: toQueryObject(filters),
    })
}

function resetFilters() {
    router.replace({ name: 'evaluation-templates.index' })
}

function goToTemplate(template) {
    if (!template?.id) {
        return
    }

    router.push({ name: 'evaluation-templates.edit', params: { id: template.id } })
}

async function duplicateTemplate(template) {
    const result = await Swal.fire({
        title: `Duplicar ${template.name}`,
        text: 'Se creará una nueva versión en borrador con los mismos criterios.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Duplicar',
    })

    if (!result.isConfirmed) {
        return
    }

    try {
        const { data } = await api.post(`/api/v2/admin/evaluation-templates/${template.id}/duplicate`)
        showMessage(data.message || 'Se creó una nueva versión en borrador.')
        router.push({ name: 'evaluation-templates.edit', params: { id: data.data.id } })
    } catch (error) {
        showMessage(getValidationMessage(error, 'No fue posible duplicar la plantilla.'), 'error')
    }
}

async function changeStatus(template, status) {
    const label = status === 'active' ? 'activar' : 'inactivar'

    try {
        await api.patch(`/api/v2/admin/evaluation-templates/${template.id}/status`, { status })
        showMessage(`Plantilla ${label}da correctamente.`)
        reloadTable()
    } catch (error) {
        showMessage(getValidationMessage(error, `No fue posible ${label} la plantilla.`), 'error')
    }
}

async function confirmDelete(template) {
    if (!template.can_delete) {
        showMessage('La plantilla ya tiene evaluaciones asociadas y no se puede eliminar.', 'warning')
        return
    }

    const result = await Swal.fire({
        title: `Eliminar ${template.name}`,
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
    })

    if (!result.isConfirmed) {
        return
    }

    try {
        await api.delete(`/api/v2/admin/evaluation-templates/${template.id}`)
        showMessage('Plantilla eliminada correctamente.')
        reloadTable()
    } catch (error) {
        showMessage(getValidationMessage(error, 'No fue posible eliminar la plantilla.'), 'error')
    }
}

onMounted(async () => {
    syncFiltersFromRoute()
    isLoading.value = true
    globalError.value = ''

    try {
        await loadOptions()
    } catch (error) {
        globalError.value = getValidationMessage(error, 'No fue posible cargar los filtros de plantillas.')
    } finally {
        isLoading.value = false
    }
})

watch(
    () => route.fullPath,
    () => {
        syncFiltersFromRoute()
        reloadTable()
    },
    { immediate: true }
)
</script>

<style scoped lang="scss">
@use '../../player-evaluations/shared' as shared;

@include shared.page-shared-styles;

.evaluation-templates-page {
    min-height: 18rem;
}

.usage-chip {
    opacity: 0.68;
}

.usage-chip.is-in-use {
    opacity: 0.95;
}

</style>
