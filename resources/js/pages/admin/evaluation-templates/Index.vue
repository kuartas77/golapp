<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
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
                </div>
            </div>
        </template>

        <template #body>
            <div class="position-relative evaluation-templates-page">
                <Loader :is-loading="isLoading" loading-text="Cargando plantillas..." />

                <div class="surface-card card mb-4">
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
                                <select v-model="filters.training_group_id" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option
                                        v-for="group in filterOptions.training_groups"
                                        :key="group.id"
                                        :value="String(group.id)"
                                    >
                                        {{ group.name }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <button type="button" class="btn btn-primary btn-sm" @click="applyFilters">
                                Aplicar filtros
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" @click="resetFilters">
                                Limpiar
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" @click="loadPage">
                                Recargar
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between gap-3">
                    <span>{{ globalError }}</span>
                    <button type="button" class="btn btn-sm btn-danger align-self-start" @click="loadPage">
                        Reintentar
                    </button>
                </div>

                <div class="surface-card card mb-4">
                    <div class="surface-card-body card-body compact-stats">
                        <div class="compact-stat-item">
                            <span class="compact-stat-label">Total filtrado</span>
                            <strong class="compact-stat-value">{{ pagination.total }}</strong>
                            <span class="text-muted">plantillas encontradas</span>
                        </div>

                        <div class="compact-stat-item">
                            <span class="compact-stat-label">Página actual</span>
                            <strong class="compact-stat-value">{{ pagination.current_page }}</strong>
                            <span class="text-muted">de {{ pagination.last_page || 1 }}</span>
                        </div>

                        <div class="compact-stat-item">
                            <span class="compact-stat-label">Rango visible</span>
                            <strong class="compact-stat-value">{{ pagination.from || 0 }} - {{ pagination.to || 0 }}</strong>
                            <span class="text-muted">resultados en esta página</span>
                        </div>
                    </div>
                </div>

                <div class="surface-card card overflow-hidden">
                    <div class="surface-card-body card-body p-0">
                        <div v-if="templates.length" class="table-responsive">
                            <table class="table table-hover table-bordered align-middle mb-0">
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
                                <tbody>
                                    <tr v-for="template in templates" :key="template.id">
                                        <td class="fw-semibold">#{{ template.id }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ template.name }}</div>
                                            <div class="small text-muted">{{ template.description || 'Sin descripción' }}</div>
                                        </td>
                                        <td>{{ template.training_group_name || 'General' }}</td>
                                        <td>{{ template.year }}</td>
                                        <td>v{{ template.version }}</td>
                                        <td>{{ template.criteria_count || 0 }}</td>
                                        <td>
                                            <span class="theme-chip usage-chip" :class="{ 'is-in-use': template.is_in_use }">
                                                {{ template.is_in_use ? `${template.evaluations_count || 0} evaluaciones` : 'Sin uso' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge text-uppercase" :class="`badge-${statusVariant(template.status)}`">
                                                {{ labelFromOptions(filterOptions.statuses, template.status, template.status) }}
                                            </span>
                                        </td>
                                        <td>{{ formatDateTime(template.updated_at) }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                                <router-link
                                                    :to="{ name: 'evaluation-templates.edit', params: { id: template.id } }"
                                                    class="btn btn-primary btn-sm"
                                                >
                                                    {{ template.is_in_use ? 'Ver' : 'Editar' }}
                                                </router-link>

                                                <button
                                                    type="button"
                                                    class="btn btn-secondary btn-sm"
                                                    @click="duplicateTemplate(template)"
                                                >
                                                    Duplicar
                                                </button>

                                                <button
                                                    v-if="template.status !== 'active'"
                                                    type="button"
                                                    class="btn btn-success btn-sm"
                                                    @click="changeStatus(template, 'active')"
                                                >
                                                    Activar
                                                </button>

                                                <button
                                                    v-if="template.status !== 'inactive'"
                                                    type="button"
                                                    class="btn btn-warning btn-sm"
                                                    @click="changeStatus(template, 'inactive')"
                                                >
                                                    Inactivar
                                                </button>

                                                <button
                                                    type="button"
                                                    class="btn btn-danger btn-sm"
                                                    :disabled="!template.can_delete"
                                                    @click="confirmDelete(template)"
                                                >
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else-if="!isLoading" class="empty-state">
                            <h5 class="mb-2">No hay plantillas para mostrar</h5>
                            <p class="text-muted mb-0">
                                Ajusta los filtros o crea una nueva plantilla para empezar.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="pagination.last_page > 1"
                    class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4"
                >
                    <p class="text-muted mb-0">
                        Mostrando {{ pagination.from || 0 }} a {{ pagination.to || 0 }} de {{ pagination.total }} plantillas.
                    </p>

                    <div class="d-flex flex-wrap gap-2">
                        <button
                            type="button"
                            class="btn btn-secondary btn-sm"
                            :disabled="pagination.current_page <= 1"
                            @click="goToPage(pagination.current_page - 1)"
                        >
                            Anterior
                        </button>

                        <button
                            v-for="page in visiblePages"
                            :key="page"
                            type="button"
                            class="btn btn-sm"
                            :class="page === pagination.current_page ? 'btn-primary' : 'btn-secondary'"
                            @click="goToPage(page)"
                        >
                            {{ page }}
                        </button>

                        <button
                            type="button"
                            class="btn btn-secondary btn-sm"
                            :disabled="pagination.current_page >= pagination.last_page"
                            @click="goToPage(pagination.current_page + 1)"
                        >
                            Siguiente
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Administración'" :current="'Plantillas de evaluación'" />
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Loader from '@/components/general/Loader.vue'
import { usePageTitle } from '@/composables/use-meta'
import api from '@/utils/axios'
import {
    defaultStatusOptions,
    formatDateTime,
    getValidationMessage,
    labelFromOptions,
    statusVariantMap,
    toQueryObject,
} from '@/pages/admin/evaluation-templates/utils'

usePageTitle('Plantillas de evaluación')

const route = useRoute()
const router = useRouter()

const isLoading = ref(false)
const hasLoadedOptions = ref(false)
const globalError = ref('')
const templates = ref([])

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

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    total: 0,
    from: 0,
    to: 0,
})

const visiblePages = computed(() => {
    const pages = []
    const windowSize = 5
    let start = Math.max(1, pagination.current_page - Math.floor(windowSize / 2))
    let end = Math.min(pagination.last_page, start + windowSize - 1)

    if ((end - start + 1) < windowSize) {
        start = Math.max(1, end - windowSize + 1)
    }

    for (let page = start; page <= end; page += 1) {
        pages.push(page)
    }

    return pages
})

function statusVariant(status) {
    return statusVariantMap[status] || 'secondary'
}

function syncFiltersFromRoute() {
    filters.search = String(route.query.search || '')
    filters.year = String(route.query.year || '')
    filters.status = String(route.query.status || '')
    filters.training_group_id = String(route.query.training_group_id || '')
    pagination.current_page = Number(route.query.page || 1)
}

async function loadOptions() {
    const { data } = await api.get('/api/v2/admin/evaluation-templates/options')

    filterOptions.years = data.filters?.years || []
    filterOptions.statuses = data.filters?.statuses?.length ? data.filters.statuses : [...defaultStatusOptions]
    filterOptions.training_groups = data.filters?.training_groups || []
}

async function loadTemplates() {
    const { data } = await api.get('/api/v2/admin/evaluation-templates', {
        params: {
            ...toQueryObject(filters),
            page: pagination.current_page,
        },
    })

    templates.value = data.data || []
    pagination.current_page = data.meta?.current_page || 1
    pagination.last_page = data.meta?.last_page || 1
    pagination.total = data.meta?.total || 0
    pagination.from = data.meta?.from || 0
    pagination.to = data.meta?.to || 0
}

async function loadPage() {
    isLoading.value = true
    globalError.value = ''
    syncFiltersFromRoute()

    try {
        const requests = [loadTemplates()]

        if (!hasLoadedOptions.value) {
            requests.unshift(loadOptions())
        }

        await Promise.all(requests)
        hasLoadedOptions.value = true
    } catch (error) {
        globalError.value = getValidationMessage(error, 'No fue posible cargar las plantillas.')
    } finally {
        isLoading.value = false
    }
}

function applyFilters() {
    router.replace({
        name: 'evaluation-templates.index',
        query: {
            ...toQueryObject(filters),
            page: 1,
        },
    })
}

function resetFilters() {
    router.replace({ name: 'evaluation-templates.index' })
}

function goToPage(page) {
    if (page < 1 || page > pagination.last_page || page === pagination.current_page) {
        return
    }

    router.replace({
        name: 'evaluation-templates.index',
        query: {
            ...toQueryObject(filters),
            page,
        },
    })
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
        await loadPage()
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

        if (templates.value.length === 1 && pagination.current_page > 1) {
            goToPage(pagination.current_page - 1)
            return
        }

        await loadPage()
    } catch (error) {
        showMessage(getValidationMessage(error, 'No fue posible eliminar la plantilla.'), 'error')
    }
}

watch(
    () => route.fullPath,
    () => {
        loadPage()
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

.compact-stats {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem 1.25rem;
    padding-top: 1rem;
    padding-bottom: 1rem;
}

.compact-stat-item {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.35rem;
}

.compact-stat-item + .compact-stat-item::before {
    content: '';
    width: 1px;
    height: 1rem;
    margin-right: 0.2rem;
    background: rgba(127, 127, 127, 0.2);
}

.compact-stat-label {
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: inherit;
    opacity: 0.75;
}

.compact-stat-value {
    font-size: 1rem;
    line-height: 1.2;
}

.usage-chip {
    opacity: 0.68;
}

.usage-chip.is-in-use {
    opacity: 0.95;
}

.empty-state {
    padding: 3rem 1.5rem;
    text-align: center;
}
</style>
