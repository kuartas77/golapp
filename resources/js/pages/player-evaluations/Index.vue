<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                <div>
                    <h3 class="mb-1">Evaluaciones de jugadores</h3>
                    <p class="text-muted mb-0">
                        Consulta, compara y gestiona el seguimiento técnico por período.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <router-link :to="{ name: 'player-evaluations.comparison' }" class="btn btn-primary btn-sm">
                        Comparativo
                    </router-link>
                    <router-link :to="{ name: 'player-evaluations.create' }" class="btn btn-primary btn-sm">
                        Nueva evaluación
                    </router-link>
                </div>
            </div>
        </template>

        <template #body>
            <div class="position-relative evaluations-index">
                <Loader :is-loading="isLoading" loading-text="Cargando evaluaciones..." />

                <div class="surface-card card mb-4">
                    <div class="surface-card-body card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-lg-4">
                                <label class="form-label">Buscar</label>
                                <input
                                    v-model.trim="filters.search"
                                    type="text"
                                    class="form-control form-control-sm"
                                    placeholder="Jugador, código o grupo"
                                    @keyup.enter="applyFilters"
                                >
                            </div>

                            <div class="col-12 col-md-6 col-lg-2">
                                <label class="form-label">Jugador</label>
                                <select v-model="filters.player_id" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option v-for="player in filterOptions.players" :key="player.id" :value="String(player.id)">
                                        {{ player.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6 col-lg-2">
                                <label class="form-label">Grupo</label>
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

                            <div class="col-12 col-md-4 col-lg-2">
                                <label class="form-label">Período</label>
                                <select v-model="filters.evaluation_period_id" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option v-for="period in filterOptions.periods" :key="period.id" :value="String(period.id)">
                                        {{ period.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4 col-lg-1">
                                <label class="form-label">Estado</label>
                                <select v-model="filters.status" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4 col-lg-1">
                                <label class="form-label">Tipo</label>
                                <select v-model="filters.evaluation_type" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option
                                        v-for="type in filterOptions.evaluation_types"
                                        :key="type.value"
                                        :value="type.value"
                                    >
                                        {{ type.label }}
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
                            <button type="button" class="btn btn-secondary btn-sm" @click="reload">
                                Recargar
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="globalError" class="alert alert-danger d-flex flex-column flex-md-row justify-content-between gap-3">
                    <span>{{ globalError }}</span>
                    <button type="button" class="btn btn-sm btn-danger align-self-start" @click="reload">
                        Reintentar
                    </button>
                </div>

                <div class="surface-card card mb-4">
                    <div class="surface-card-body card-body compact-stats">
                        <div class="compact-stat-item">
                            <span class="compact-stat-label">Total registrado</span>
                            <strong class="compact-stat-value">{{ pagination.total }}</strong>
                            <span class="text-muted">evaluaciones filtradas</span>
                        </div>

                        <div class="compact-stat-item">
                            <span class="compact-stat-label">Página actual</span>
                            <strong class="compact-stat-value">{{ pagination.current_page }}</strong>
                            <span class="text-muted">de {{ pagination.last_page || 1 }}</span>
                        </div>

                        <div class="compact-stat-item">
                            <span class="compact-stat-label">Rango visible</span>
                            <strong class="compact-stat-value">
                                {{ pagination.from || 0 }} - {{ pagination.to || 0 }}
                            </strong>
                            <span class="text-muted">resultados en esta página</span>
                        </div>
                    </div>
                </div>

                <div class="surface-card card overflow-hidden">
                    <div class="surface-card-header card-header">
                        <div class="section-label mb-2">Listado</div>
                        <h5 class="mb-1">Seguimiento por jugador y período</h5>
                        <p class="text-muted mb-0">
                            Navega entre borradores, evaluaciones completadas y reportes cerrados.
                        </p>
                    </div>

                    <div class="surface-card-body card-body p-0">
                        <div v-if="evaluations.length" class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Jugador</th>
                                        <th>Grupo</th>
                                        <th>Período</th>
                                        <th>Plantilla</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th class="text-center">Nota</th>
                                        <th>Fecha</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="evaluation in evaluations" :key="evaluation.id">
                                        <td class="fw-semibold">#{{ evaluation.id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img
                                                    :src="evaluation.inscription?.player?.photo_url || '/img/user.webp'"
                                                    :alt="evaluation.inscription?.player?.name || 'Jugador'"
                                                    class="player-avatar"
                                                >
                                                <div>
                                                    <router-link
                                                        :to="{ name: 'player-evaluations.show', params: { id: evaluation.id } }"
                                                        class="fw-semibold text-decoration-none"
                                                    >
                                                        {{ evaluation.inscription?.player?.name || '—' }}
                                                    </router-link>
                                                    <div class="small text-muted">
                                                        {{ evaluation.inscription?.player?.unique_code || 'Sin código' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ evaluation.inscription?.training_group?.name || '—' }}</td>
                                        <td>{{ evaluation.evaluation_period?.name || '—' }}</td>
                                        <td>{{ evaluation.template?.name || '—' }}</td>
                                        <td>
                                            <span class="theme-chip">
                                                {{ labelFromOptions(filterOptions.evaluation_types, evaluation.evaluation_type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge text-uppercase" :class="`badge-${statusVariant(evaluation.status)}`">
                                                {{ labelFromOptions(filterOptions.statuses, evaluation.status, evaluation.status || '—') }}
                                            </span>
                                        </td>
                                        <td class="text-center fw-semibold">
                                            {{ formatScore(evaluation.overall_score) }}
                                        </td>
                                        <td>{{ formatDateTime(evaluation.evaluated_at) }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                                <router-link
                                                    :to="{ name: 'player-evaluations.show', params: { id: evaluation.id } }"
                                                    class="btn btn-primary btn-sm"
                                                >
                                                    Ver
                                                </router-link>
                                                <router-link
                                                    v-if="!evaluation.is_closed"
                                                    :to="{ name: 'player-evaluations.edit', params: { id: evaluation.id } }"
                                                    class="btn btn-warning btn-sm"
                                                >
                                                    Editar
                                                </router-link>
                                                <a
                                                    :href="evaluation.urls?.pdf"
                                                    class="btn btn-dark btn-sm"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                >
                                                    PDF
                                                </a>
                                                <button
                                                    v-if="!evaluation.is_closed"
                                                    type="button"
                                                    class="btn btn-danger btn-sm"
                                                    @click="confirmDelete(evaluation)"
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
                            <h5 class="mb-2">No hay evaluaciones para mostrar</h5>
                            <p class="text-muted mb-0">
                                Ajusta los filtros o crea una nueva evaluación para empezar.
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    v-if="pagination.last_page > 1"
                    class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4"
                >
                    <p class="text-muted mb-0">
                        Mostrando {{ pagination.from || 0 }} a {{ pagination.to || 0 }} de {{ pagination.total }} registros.
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

    <breadcrumb :parent="'Plataforma'" :current="'Evaluaciones'" />
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Loader from '@/components/general/Loader.vue'
import api from '@/utils/axios'
import {
    defaultEvaluationTypeOptions,
    defaultStatusOptions,
    formatDateTime,
    formatScore,
    getValidationMessage,
    labelFromOptions,
    statusVariantMap,
    toQueryObject,
} from '@/pages/player-evaluations/utils'

const route = useRoute()
const router = useRouter()

const isLoading = ref(true)
const hasLoadedOnce = ref(false)
const globalError = ref('')
const evaluations = ref([])

const filterOptions = reactive({
    players: [],
    training_groups: [],
    periods: [],
    statuses: [...defaultStatusOptions],
    evaluation_types: [...defaultEvaluationTypeOptions],
})

const filters = reactive({
    search: '',
    player_id: '',
    training_group_id: '',
    evaluation_period_id: '',
    status: '',
    evaluation_type: '',
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
    filters.player_id = String(route.query.player_id || '')
    filters.training_group_id = String(route.query.training_group_id || '')
    filters.evaluation_period_id = String(route.query.evaluation_period_id || '')
    filters.status = String(route.query.status || '')
    filters.evaluation_type = String(route.query.evaluation_type || '')
    pagination.current_page = Number(route.query.page || 1)
}

async function loadOptions() {
    const { data } = await api.get('/api/v2/player-evaluations/options')
    filterOptions.players = data.filters?.players || []
    filterOptions.training_groups = data.filters?.training_groups || []
    filterOptions.periods = data.filters?.periods || []
    filterOptions.statuses = data.filters?.statuses?.length ? data.filters.statuses : [...defaultStatusOptions]
    filterOptions.evaluation_types = data.filters?.evaluation_types?.length
        ? data.filters.evaluation_types
        : [...defaultEvaluationTypeOptions]
}

async function loadEvaluations() {
    const { data } = await api.get('/api/v2/player-evaluations', {
        params: {
            ...toQueryObject(filters),
            page: pagination.current_page,
        },
    })

    evaluations.value = data.data || []
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
        const requests = [loadEvaluations()]

        if (!hasLoadedOnce.value) {
            requests.unshift(loadOptions())
        }

        await Promise.all(requests)
        hasLoadedOnce.value = true
    } catch (error) {
        globalError.value = getValidationMessage(error, 'No fue posible cargar el módulo de evaluaciones.')
    } finally {
        isLoading.value = false
    }
}

function applyFilters() {
    router.replace({
        name: 'player-evaluations.index',
        query: {
            ...toQueryObject(filters),
            page: 1,
        },
    })
}

function resetFilters() {
    router.replace({
        name: 'player-evaluations.index',
    })
}

function reload() {
    loadPage()
}

function goToPage(page) {
    if (page < 1 || page > pagination.last_page || page === pagination.current_page) {
        return
    }

    router.replace({
        name: 'player-evaluations.index',
        query: {
            ...toQueryObject(filters),
            page,
        },
    })
}

async function confirmDelete(evaluation) {
    const result = await Swal.fire({
        title: `Eliminar evaluación #${evaluation.id}`,
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
    })

    if (!result.isConfirmed) {
        return
    }

    try {
        await api.delete(`/api/v2/player-evaluations/${evaluation.id}`)
        showMessage('Evaluación eliminada correctamente.')

        if (evaluations.value.length === 1 && pagination.current_page > 1) {
            goToPage(pagination.current_page - 1)
            return
        }

        await loadPage()
    } catch (error) {
        showMessage(getValidationMessage(error, 'No se pudo eliminar la evaluación.'), 'error')
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

.theme-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    color: inherit;
    border: 1px solid currentColor;
    font-size: 0.8rem;
    font-weight: 600;
    opacity: 0.75;
}

.player-avatar {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border-radius: 14px;
    border: 1px solid rgba(127, 127, 127, 0.22);
}

.empty-state {
    padding: 3rem 1.5rem;
    text-align: center;
}

.table > :not(caption) > * > * {
    padding: 1rem 0.85rem;
}

@media (max-width: 767px) {
    .compact-stats {
        gap: 0.6rem 0.9rem;
    }

    .compact-stat-item {
        width: 100%;
    }

    .compact-stat-item + .compact-stat-item::before {
        display: none;
    }
}
</style>
