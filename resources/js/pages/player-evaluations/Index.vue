<template>
    <panel>
        <template #header>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3" data-tour="player-evaluations-index-actions">
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
                    <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question me-2"></i>
                        Guia
                    </button>
                </div>
            </div>
        </template>

        <template #body>
            <div class="position-relative evaluations-index">
                <Loader :is-loading="isLoading" loading-text="Cargando evaluaciones..." />

                <div class="card mb-4" data-tour="player-evaluations-index-filters">
                    <div class="card-body">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label">Jugador</label>
                                <CustomSelect2
                                    v-model="filters.player_id"
                                    :options="playerOptions"
                                    placeholder="Todos"
                                    search-placeholder="Buscar jugador..."
                                    @change="reloadTable"
                                />
                            </div>

                            <div class="col-12 col-md-6 col-xl-3">
                                <label class="form-label">Grupo</label>
                                <CustomSelect2
                                    v-model="filters.training_group_id"
                                    :options="trainingGroupOptions"
                                    placeholder="Todos"
                                    search-placeholder="Buscar grupo..."
                                    @change="reloadTable"
                                />
                            </div>

                            <div class="col-12 col-md-4 col-xl-2">
                                <label class="form-label">Período</label>
                                <select v-model="filters.evaluation_period_id" class="form-select form-select-sm" @change="reloadTable">
                                    <option value="">Todos</option>
                                    <option v-for="period in filterOptions.periods" :key="period.id" :value="String(period.id)">
                                        {{ period.name }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4 col-xl-2">
                                <label class="form-label">Estado</label>
                                <select v-model="filters.status" class="form-select form-select-sm" @change="reloadTable">
                                    <option value="">Todos</option>
                                    <option v-for="status in filterOptions.statuses" :key="status.value" :value="status.value">
                                        {{ status.label }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-12 col-md-4 col-xl-2">
                                <label class="form-label">Tipo</label>
                                <select v-model="filters.evaluation_type" class="form-select form-select-sm" @change="reloadTable">
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

                <div class="card" data-tour="player-evaluations-index-table">
                    <div class="card-body p-1">
                        <DatatableTemplate
                            id="player_evaluations_table"
                            ref="evaluationTable"
                            :options="options"
                            @click="resolveRouteFromClick"
                        >
                            <template #thead>
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Jugador</th>
                                        <th>Grupo</th>
                                        <th>Período</th>
                                        <th>Plantilla</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Nota</th>
                                        <th>Fecha</th>
                                        <th></th>
                                    </tr>
                                </thead>
                            </template>
                        </DatatableTemplate>
                    </div>
                </div>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Evaluaciones'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import Loader from '@/components/general/Loader.vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import api from '@/utils/axios'
import configLanguaje from '@/utils/datatableUtils'
import { usePageTutorial } from '@/composables/usePageTutorial'
import {
    defaultEvaluationTypeOptions,
    defaultStatusOptions,
    formatDateTime,
    formatScore,
    getValidationMessage,
    statusVariantMap,
    toQueryObject,
} from '@/pages/player-evaluations/utils'
import { playerEvaluationsIndexTutorial } from '@/tutorials/playerEvaluations'

const router = useRouter()
const tutorial = usePageTutorial(playerEvaluationsIndexTutorial)

const isLoading = ref(true)
const globalError = ref('')
const evaluationTable = ref(null)

const filterOptions = reactive({
    players: [],
    training_groups: [],
    periods: [],
    statuses: [...defaultStatusOptions],
    evaluation_types: [...defaultEvaluationTypeOptions],
})

const filters = reactive({
    player_id: '',
    training_group_id: '',
    evaluation_period_id: '',
    status: '',
    evaluation_type: '',
})

const playerOptions = computed(() => (
    filterOptions.players.map((player) => ({
        value: String(player.id),
        label: player.name,
        meta: player.unique_code || '',
    }))
))

const trainingGroupOptions = computed(() => (
    filterOptions.training_groups.map((group) => ({
        value: String(group.id),
        label: group.name,
    }))
))

function statusVariant(status) {
    return statusVariantMap[status] || 'secondary'
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
}

function renderPlayer(data, type, row) {
    const playerName = escapeHtml(data || 'Jugador sin nombre')
    const playerCode = escapeHtml(row.player_code || 'Sin código')
    const photoUrl = escapeHtml(row.player_photo_url || '/img/user.webp')

    return `
        <div class="d-flex align-items-center gap-3">
            <img src="${photoUrl}" alt="${playerName}" class="player-avatar">
            <div>
                <button
                    type="button"
                    class="btn btn-link btn-sm p-0 fw-semibold text-decoration-none text-start"
                    data-item-id="${row.id}"
                    data-type="show"
                    title="Ver evaluación"
                >
                    ${playerName}
                </button>
                <div class="small text-muted">${playerCode}</div>
            </div>
        </div>
    `
}

function renderStatus(data, type, row) {
    return `
        <span class="badge text-uppercase badge-${statusVariant(row.status)}">
            ${escapeHtml(data || row.status || '—')}
        </span>
    `
}

function renderType(data) {
    return `<span class="theme-chip">${escapeHtml(data || '—')}</span>`
}

function renderScore(data) {
    return `<span class="fw-semibold">${escapeHtml(formatScore(data))}</span>`
}

function renderDate(data) {
    return escapeHtml(formatDateTime(data))
}

function renderActions(data, type, row) {
    const editAction = row.is_closed ? '' : `
        <li>
            <button
                class="dropdown-item"
                data-item-id="${row.id}"
                data-type="edit"
                type="button"
            >
                <i class="fa fa-edit fa-width-auto me-2" data-item-id="${row.id}" data-type="edit"></i>
                Editar
            </button>
        </li>
    `

    const deleteAction = row.is_closed ? '' : `
        <li><hr class="dropdown-divider"></li>
        <li>
            <button
                class="dropdown-item text-danger"
                data-item-id="${row.id}"
                data-type="delete"
                type="button"
            >
                <i class="fa fa-trash fa-width-auto me-2" data-item-id="${row.id}" data-type="delete"></i>
                Eliminar
            </button>
        </li>
    `

    return `
        <div class="dropdown player-evaluation-actions-dropdown">
            <button
                class="btn btn-sm btn-primary dropdown-toggle"
                type="button"
                data-bs-toggle="dropdown"
                data-bs-display="static"
                aria-expanded="false"
            >
                Acciones
            </button>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <button
                        class="dropdown-item"
                        data-item-id="${row.id}"
                        data-type="show"
                        type="button"
                    >
                        <i class="fa fa-eye fa-width-auto me-2" data-item-id="${row.id}" data-type="show"></i>
                        Ver
                    </button>
                </li>
                ${editAction}
                <li>
                    <a class="dropdown-item" href="${escapeHtml(row.urls?.pdf || '#')}" target="_blank" rel="noopener noreferrer">
                        <i class="fa-solid fa-file-pdf fa-width-auto me-2"></i>
                        PDF
                    </a>
                </li>
                ${deleteAction}
            </ul>
        </div>
    `
}

const columns = [
    { data: 'id', name: 'player_evaluations.id', width: '1%', className: 'dt-head-center dt-body-center' },
    { data: 'player_name', name: 'player_name', render: renderPlayer },
    { data: 'training_group_name', name: 'training_group_name' },
    { data: 'period_name', name: 'period_name' },
    { data: 'template_name', name: 'template_name' },
    { data: 'evaluation_type_label', name: 'evaluation_type_label', render: renderType },
    { data: 'status_label', name: 'status_label', render: renderStatus },
    { data: 'overall_score', name: 'player_evaluations.overall_score', render: renderScore, className: 'dt-head-center dt-body-center' },
    { data: 'evaluated_at', name: 'player_evaluations.evaluated_at', render: renderDate },
    { data: 'id', title: 'Acciones', render: renderActions, searchable: false, orderable: false, className: 'dt-head-center dt-body-center' },
]

const options = {
    ...configLanguaje,
    lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
    columnDefs: [
        { responsivePriority: 1, targets: columns.length - 1 },
        { responsivePriority: 2, targets: 1 },
        { targets: [0, 5, 6, 7, 8, 9], className: 'dt-head-center dt-body-center' },
        { targets: [1], width: '28%' },
    ],
    serverSide: true,
    processing: true,
    order: [[0, 'desc']],
    ajax: async (data, callback) => {
        try {
            globalError.value = ''
            const response = await api.get('/api/v2/datatables/player_evaluations', {
                params: {
                    ...data,
                    ...toQueryObject(filters),
                },
            })

            callback(response.data)
        } catch (error) {
            globalError.value = getValidationMessage(error, 'No fue posible cargar el listado de evaluaciones.')
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
    return evaluationTable.value?.table?.dt ?? null
}

function reloadTable() {
    const dt = getDataTable()

    if (!dt) {
        return
    }

    dt.ajax.reload(null, false)
}

function resetFilters() {
    filters.player_id = ''
    filters.training_group_id = ''
    filters.evaluation_period_id = ''
    filters.status = ''
    filters.evaluation_type = ''
    reloadTable()
}

async function loadOptions() {
    isLoading.value = true
    globalError.value = ''

    try {
        const { data } = await api.get('/api/v2/player-evaluations/options')
        filterOptions.players = data.filters?.players || []
        filterOptions.training_groups = data.filters?.training_groups || []
        filterOptions.periods = data.filters?.periods || []
        filterOptions.statuses = data.filters?.statuses?.length ? data.filters.statuses : [...defaultStatusOptions]
        filterOptions.evaluation_types = data.filters?.evaluation_types?.length
            ? data.filters.evaluation_types
            : [...defaultEvaluationTypeOptions]
    } catch (error) {
        globalError.value = getValidationMessage(error, 'No fue posible cargar los filtros de evaluaciones.')
    } finally {
        isLoading.value = false
    }
}

async function confirmDelete(evaluationId) {
    const result = await Swal.fire({
        title: `Eliminar evaluación #${evaluationId}`,
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
    })

    if (!result.isConfirmed) {
        return
    }

    try {
        await api.delete(`/api/v2/player-evaluations/${evaluationId}`)
        showMessage('Evaluación eliminada correctamente.')
        reloadTable()
    } catch (error) {
        showMessage(getValidationMessage(error, 'No se pudo eliminar la evaluación.'), 'error')
    }
}

function resolveRouteFromClick(event) {
    const type = event.target.dataset.type
    const itemId = event.target.dataset.itemId

    if (!itemId || !type) {
        return
    }

    event.preventDefault()

    switch (type) {
        case 'show':
            router.push({ name: 'player-evaluations.show', params: { id: itemId } })
            break
        case 'edit':
            router.push({ name: 'player-evaluations.edit', params: { id: itemId } })
            break
        case 'delete':
            confirmDelete(itemId)
            break
        default:
            break
    }
}

onMounted(() => {
    loadOptions()
})
</script>

<style scoped lang="scss">
@use './shared' as shared;

@include shared.page-shared-styles;

.theme-chip {
    font-size: 0.8rem;
    font-weight: 600;
    opacity: 0.75;
}
</style>
