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

                            <template #evaluation-player="props">
                                <div class="d-flex align-items-center gap-3">
                                    <img
                                        :src="props.rowData.player_photo_url || '/img/user.webp'"
                                        :alt="props.cellData || 'Jugador sin nombre'"
                                        class="player-avatar"
                                    >
                                    <div>
                                        <button
                                            type="button"
                                            class="btn btn-link btn-sm p-0 fw-semibold text-decoration-none text-start"
                                            title="Ver evaluación"
                                            @click.stop="goToEvaluation(props.rowData, 'show')"
                                        >
                                            {{ props.cellData || 'Jugador sin nombre' }}
                                        </button>
                                        <div class="small text-muted">{{ props.rowData.player_code || 'Sin código' }}</div>
                                    </div>
                                </div>
                            </template>

                            <template #evaluation-type="props">
                                <span class="theme-chip">{{ props.cellData || '—' }}</span>
                            </template>

                            <template #evaluation-status="props">
                                <span class="badge text-uppercase" :class="`badge-${statusVariant(props.rowData.status)}`">
                                    {{ props.cellData || props.rowData.status || '—' }}
                                </span>
                            </template>

                            <template #evaluation-score="props">
                                <span class="fw-semibold">{{ formatScore(props.cellData) }}</span>
                            </template>

                            <template #evaluation-date="props">
                                {{ formatDateTime(props.cellData) }}
                            </template>

                            <template #evaluation-actions="props">
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
                                                type="button"
                                                @click="goToEvaluation(props.rowData, 'show')"
                                            >
                                                <i class="fa fa-eye fa-width-auto me-2"></i>
                                                Ver
                                            </button>
                                        </li>
                                        <li v-if="!props.rowData.is_closed">
                                            <button
                                                class="dropdown-item"
                                                type="button"
                                                @click="goToEvaluation(props.rowData, 'edit')"
                                            >
                                                <i class="fa fa-edit fa-width-auto me-2"></i>
                                                Editar
                                            </button>
                                        </li>
                                        <li>
                                            <a
                                                class="dropdown-item"
                                                :href="props.rowData.urls?.pdf || '#'"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                            >
                                                <i class="fa-solid fa-file-pdf fa-width-auto me-2"></i>
                                                PDF
                                            </a>
                                        </li>
                                        <template v-if="!props.rowData.is_closed">
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button
                                                    class="dropdown-item text-danger"
                                                    type="button"
                                                    @click="confirmDelete(props.rowData.id)"
                                                >
                                                    <i class="fa fa-trash fa-width-auto me-2"></i>
                                                    Eliminar
                                                </button>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
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

const columns = [
    { data: 'id', name: 'player_evaluations.id', width: '1%', className: 'dt-head-center dt-body-center' },
    { data: 'player_name', name: 'player_name', render: '#evaluation-player' },
    { data: 'training_group_name', name: 'training_group_name' },
    { data: 'period_name', name: 'period_name' },
    { data: 'template_name', name: 'template_name' },
    { data: 'evaluation_type_label', name: 'evaluation_type_label', render: '#evaluation-type' },
    { data: 'status_label', name: 'status_label', render: '#evaluation-status' },
    { data: 'overall_score', name: 'player_evaluations.overall_score', render: '#evaluation-score', className: 'dt-head-center dt-body-center' },
    { data: 'evaluated_at', name: 'player_evaluations.evaluated_at', render: '#evaluation-date' },
    { data: 'id', title: 'Acciones', render: '#evaluation-actions', searchable: false, orderable: false, className: 'dt-head-center dt-body-center' },
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
    pipeline: { pages: 5 },
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

    dt.clearPipeline()
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

function goToEvaluation(row, type) {
    const itemId = row?.id

    if (!itemId) {
        return
    }

    if (type === 'edit') {
        router.push({ name: 'player-evaluations.edit', params: { id: itemId } })
        return
    }

    router.push({ name: 'player-evaluations.show', params: { id: itemId } })
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
