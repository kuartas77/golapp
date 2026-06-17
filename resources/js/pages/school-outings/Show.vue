<template>
    <panel>
        <template #body>
            <div v-if="globalError" class="alert alert-danger" role="alert">{{ globalError }}</div>
            <div v-if="loading" class="text-center text-muted py-4">Cargando salida...</div>

            <template v-else-if="outing">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-3">
                    <div>
                        <router-link class="btn btn-link px-0 mb-2" :to="{ name: 'school-outings.index' }">
                            Volver a salidas
                        </router-link>
                        <h4 class="mb-1">{{ outing.name }}</h4>
                        <p class="text-muted mb-0">
                            Fecha {{ outing.departure_date }} · {{ outing.status_label }}
                        </p>
                    </div>
                    <span class="badge fs-6" :class="statusClass(outing.status)">{{ outing.status_label }}</span>
                </div>

                <div class="row g-3 mb-4">
                    <div v-for="metric in summaryMetrics" :key="metric.label" class="col-sm-6 col-xl-3">
                        <div class="outing-metric">
                            <span class="outing-metric__label">{{ metric.label }}</span>
                            <strong>{{ metric.value }}</strong>
                        </div>
                    </div>
                </div>

                <div class="progress mb-4" style="height: 10px;">
                    <div class="progress-bar bg-info" role="progressbar" :style="{ width: `${outing.progress_percent || 0}%` }"></div>
                </div>

                <ul class="nav nav-tabs mb-3">
                    <li class="nav-item">
                        <button type="button" class="nav-link" :class="{ active: activeTab === 'participants' }" @click="activeTab = 'participants'">
                            Deportistas
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" :class="{ active: activeTab === 'activities' }" @click="activeTab = 'activities'">
                            Actividades
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" :class="{ active: activeTab === 'contributions' }" @click="activeTab = 'contributions'">
                            Abonos
                        </button>
                    </li>
                </ul>

                <section v-show="activeTab === 'participants'">
                    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
                        <div class="row g-2 flex-grow-1">
                            <div class="col-md-4">
                                <CustomSelect2
                                    id="school-outing-filter-group"
                                    v-model="filters.training_group_id"
                                    :options="groupOptions"
                                    :disabled="outing.is_locked"
                                    placeholder="Todos los grupos"
                                    @change="fetchEligible"
                                />
                            </div>
                            <div class="col-md-3">
                                <CustomSelect2
                                    id="school-outing-filter-category"
                                    v-model="filters.category"
                                    :options="categoryOptions"
                                    :disabled="outing.is_locked"
                                    placeholder="Todas las categorías"
                                    @change="fetchEligible"
                                />
                            </div>
                            <div class="col-md-5">
                                <input v-model.trim="filters.search" type="search" class="form-control" placeholder="Buscar por nombre o código" :disabled="outing.is_locked" @input="fetchEligible">
                            </div>
                        </div>
                        <button type="button" class="btn btn-info btn-sm" :disabled="outing.is_locked || selectedInscriptions.length === 0 || addingParticipants" @click="addParticipants">
                            Agregar seleccionados
                        </button>
                    </div>

                    <div v-if="eligible.length > 0 && !outing.is_locked" class="table-responsive-md mb-4">
                        <table class="table table-bordered table-sm align-middle">
                            <thead>
                                <tr>
                                    <th class="text-center">Sel.</th>
                                    <th>Deportista disponible</th>
                                    <th>Categoría</th>
                                    <th>Grupo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in eligible" :key="item.id">
                                    <td class="text-center">
                                        <input v-model="selectedInscriptions" class="form-check-input" type="checkbox" :value="item.id">
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ item.player_name }}</div>
                                        <small class="text-muted">{{ item.unique_code }}</small>
                                    </td>
                                    <td>{{ item.category }}</td>
                                    <td>{{ item.training_group_name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-responsive-md">
                        <table class="table table-bordered table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Deportista</th>
                                    <th>Categoría</th>
                                    <th>Grupo</th>
                                    <th class="text-end">Meta</th>
                                    <th class="text-end">Recaudado</th>
                                    <th class="text-end">Pendiente</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="outing.participants.length === 0">
                                    <td colspan="8" class="text-center text-muted py-4">No hay deportistas seleccionados.</td>
                                </tr>
                                <tr v-for="participant in outing.participants" :key="participant.id">
                                    <td>
                                        <div class="fw-semibold">{{ participant.player?.full_names }}</div>
                                        <small class="text-muted">{{ participant.player?.unique_code }}</small>
                                    </td>
                                    <td>{{ participant.inscription?.category || participant.player?.category }}</td>
                                    <td>{{ participant.inscription?.training_group?.full_group || participant.inscription?.training_group?.name }}</td>
                                    <td class="text-end">{{ formatMoney(participant.target_amount) }}</td>
                                    <td class="text-end">{{ formatMoney(participant.raised_total) }}</td>
                                    <td class="text-end">{{ formatMoney(participant.pending_total) }}</td>
                                    <td class="text-center">{{ participant.status_label }}</td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" class="btn btn-outline-info btn-sm" :disabled="outing.is_locked" @click="openContribution(participant)">
                                                Abono
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" :disabled="outing.is_locked || Number(participant.raised_total || 0) > 0" @click="removeParticipant(participant)">
                                                Quitar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section v-show="activeTab === 'activities'">
                    <form class="row g-2 mb-3" @submit.prevent="saveActivity">
                        <div class="col-md-8">
                            <input v-model.trim="activityForm.name" type="text" class="form-control" placeholder="Nombre de actividad" :disabled="outing.is_locked">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-info w-100" :disabled="outing.is_locked || !activityForm.name || savingActivity">
                                {{ activityForm.id ? 'Actualizar actividad' : 'Agregar actividad' }}
                            </button>
                        </div>
                    </form>

                    <div class="table-responsive-md">
                        <table class="table table-bordered table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Actividad</th>
                                    <th class="text-center">Principal</th>
                                    <th class="text-center">Abonos</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="activity in outing.activities" :key="activity.id">
                                    <td>{{ activity.name }}</td>
                                    <td class="text-center">{{ activity.is_default ? 'Sí' : 'No' }}</td>
                                    <td class="text-center">{{ contributionsByActivity(activity.id).length }}</td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" class="btn btn-outline-primary btn-sm" :disabled="outing.is_locked" @click="editActivity(activity)">
                                                Editar
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" :disabled="outing.is_locked || contributionsByActivity(activity.id).length > 0" @click="deleteActivity(activity)">
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section v-show="activeTab === 'contributions'">
                    <div class="table-responsive-md">
                        <table class="table table-bordered table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Deportista</th>
                                    <th>Actividad</th>
                                    <th class="text-end">Valor</th>
                                    <th>Nota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="outing.contributions.length === 0">
                                    <td colspan="5" class="text-center text-muted py-4">No hay abonos registrados.</td>
                                </tr>
                                <tr v-for="contribution in outing.contributions" :key="contribution.id">
                                    <td>{{ contribution.contribution_date }}</td>
                                    <td>{{ contribution.participant?.player?.full_names }}</td>
                                    <td>{{ contribution.activity?.name }}</td>
                                    <td class="text-end">{{ formatMoney(contribution.amount) }}</td>
                                    <td>{{ contribution.notes }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </template>
        </template>
    </panel>

    <div v-if="showContributionModal" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form @submit.prevent="saveContribution">
                    <div class="modal-header">
                        <h5 class="modal-title">Registrar abono</h5>
                        <button type="button" class="btn-close" :disabled="savingContribution" @click="closeContribution"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="contributionMessage" class="alert alert-danger" role="alert">{{ contributionMessage }}</div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Deportista</small>
                            <strong>{{ contributionParticipant?.player?.full_names }}</strong>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="contribution-activity">Actividad</label>
                            <select id="contribution-activity" v-model="contributionForm.school_outing_activity_id" class="form-select">
                                <option v-for="activity in outing.activities" :key="activity.id" :value="activity.id">
                                    {{ activity.name }}
                                </option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="contribution-amount">Valor</label>
                            <CurrencyInput id="contribution-amount" v-model="contributionForm.amount" class="form-control" />
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="contribution-date">Fecha</label>
                            <input id="contribution-date" v-model="contributionForm.contribution_date" type="date" class="form-control">
                        </div>
                        <div>
                            <label class="form-label" for="contribution-notes">Nota</label>
                            <textarea id="contribution-notes" v-model.trim="contributionForm.notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" :disabled="savingContribution" @click="closeContribution">Cancelar</button>
                        <button type="submit" class="btn btn-info" :disabled="savingContribution">
                            <span v-if="savingContribution" class="spinner-border spinner-border-sm me-2"></span>
                            Guardar abono
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/utils/axios'
import CurrencyInput from '@/components/general/CurrencyInput'
import CustomSelect2 from '@/components/form/CustomSelect2.vue'

const route = useRoute()
const outing = ref(null)
const eligible = ref([])
const selectedInscriptions = ref([])
const activeTab = ref('participants')
const loading = ref(false)
const addingParticipants = ref(false)
const savingActivity = ref(false)
const savingContribution = ref(false)
const showContributionModal = ref(false)
const contributionParticipant = ref(null)
const globalError = ref('')
const contributionMessage = ref('')

const filters = reactive({
    training_group_id: '',
    category: '',
    search: '',
})

const settings = reactive({
    groups: [],
    categories: [],
})

const activityForm = reactive({
    id: null,
    name: '',
})

const contributionForm = reactive({
    school_outing_participant_id: null,
    school_outing_activity_id: null,
    amount: 0,
    contribution_date: new Date().toISOString().slice(0, 10),
    notes: '',
})

let eligibleTimer = null

const summaryMetrics = computed(() => [
    { label: 'Deportistas', value: outing.value?.participants_count_value ?? 0 },
    { label: 'Meta total', value: formatMoney(outing.value?.target_total) },
    { label: 'Recaudado', value: formatMoney(outing.value?.raised_total) },
    { label: 'Pendiente', value: formatMoney(outing.value?.pending_total) },
])

const groupOptions = computed(() => settings.groups.map((group) => ({
    value: Number(group.id),
    label: group.full_schedule_group || group.full_group || group.name,
})))

const categoryOptions = computed(() => settings.categories.map((category) => ({
    value: category.category,
    label: category.category,
})))

const formatMoney = (value) => {
    if (typeof window.moneyFormat === 'function') {
        return window.moneyFormat(Number(value || 0))
    }

    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(value || 0))
}

const statusClass = (status) => ({
    open: 'bg-success',
    closed: 'bg-secondary',
    cancelled: 'bg-danger',
}[status] || 'bg-secondary')

const fetchSettings = async () => {
    const { data } = await api.get('/api/v2/settings/general')
    settings.groups = data.all_t_groups || data.t_groups || []
    settings.categories = data.categories || []
}

const fetchOuting = async () => {
    loading.value = true
    globalError.value = ''

    try {
        const { data } = await api.get(`/api/v2/school-outings/${route.params.id}`)
        outing.value = data.data
    } catch (error) {
        globalError.value = error.response?.data?.message || 'No fue posible cargar la salida.'
    } finally {
        loading.value = false
    }
}

const fetchEligibleNow = async () => {
    if (!outing.value || outing.value.is_locked) {
        eligible.value = []
        return
    }

    const { data } = await api.get(`/api/v2/school-outings/${outing.value.id}/eligible-inscriptions`, {
        params: {
            training_group_id: filters.training_group_id || undefined,
            category: filters.category || undefined,
            search: filters.search || undefined,
        },
    })

    eligible.value = data.data || []
}

const fetchEligible = () => {
    window.clearTimeout(eligibleTimer)
    eligibleTimer = window.setTimeout(fetchEligibleNow, 250)
}

const refreshAll = async () => {
    await fetchOuting()
    await fetchEligibleNow()
}

const addParticipants = async () => {
    addingParticipants.value = true

    try {
        const { data } = await api.post(`/api/v2/school-outings/${outing.value.id}/participants`, {
            inscription_ids: selectedInscriptions.value,
        })
        window.showMessage?.(data.message || 'Deportistas agregados correctamente.')
        selectedInscriptions.value = []
        outing.value = data.data
        await fetchEligibleNow()
    } catch (error) {
        globalError.value = error.response?.data?.message || 'No fue posible agregar los deportistas.'
    } finally {
        addingParticipants.value = false
    }
}

const removeParticipant = async (participant) => {
    try {
        const { data } = await api.delete(`/api/v2/school-outings/${outing.value.id}/participants/${participant.id}`)
        window.showMessage?.(data.message || 'Deportista retirado correctamente.')
        outing.value = data.data
        await fetchEligibleNow()
    } catch (error) {
        globalError.value = error.response?.data?.message || 'No fue posible retirar el deportista.'
    }
}

const saveActivity = async () => {
    savingActivity.value = true

    try {
        const payload = { name: activityForm.name }
        const request = activityForm.id
            ? api.put(`/api/v2/school-outings/${outing.value.id}/activities/${activityForm.id}`, payload)
            : api.post(`/api/v2/school-outings/${outing.value.id}/activities`, payload)

        const { data } = await request
        window.showMessage?.(data.message || 'Actividad guardada correctamente.')
        activityForm.id = null
        activityForm.name = ''
        await refreshAll()
    } catch (error) {
        globalError.value = error.response?.data?.message || 'No fue posible guardar la actividad.'
    } finally {
        savingActivity.value = false
    }
}

const editActivity = (activity) => {
    activityForm.id = activity.id
    activityForm.name = activity.name
}

const deleteActivity = async (activity) => {
    try {
        const { data } = await api.delete(`/api/v2/school-outings/${outing.value.id}/activities/${activity.id}`)
        window.showMessage?.(data.message || 'Actividad eliminada correctamente.')
        outing.value = data.data
    } catch (error) {
        globalError.value = error.response?.data?.message || 'No fue posible eliminar la actividad.'
    }
}

const contributionsByActivity = (activityId) => {
    return outing.value?.contributions?.filter((contribution) => Number(contribution.school_outing_activity_id) === Number(activityId)) || []
}

const openContribution = (participant) => {
    contributionParticipant.value = participant
    contributionMessage.value = ''
    contributionForm.school_outing_participant_id = participant.id
    contributionForm.school_outing_activity_id = outing.value.activities[0]?.id || null
    contributionForm.amount = Number(participant.pending_total || participant.target_amount || 0)
    contributionForm.contribution_date = new Date().toISOString().slice(0, 10)
    contributionForm.notes = ''
    showContributionModal.value = true
}

const closeContribution = () => {
    showContributionModal.value = false
}

const saveContribution = async () => {
    savingContribution.value = true
    contributionMessage.value = ''

    try {
        const { data } = await api.post(`/api/v2/school-outings/${outing.value.id}/contributions`, {
            school_outing_participant_id: contributionForm.school_outing_participant_id,
            school_outing_activity_id: contributionForm.school_outing_activity_id,
            amount: Number(contributionForm.amount || 0),
            contribution_date: contributionForm.contribution_date,
            notes: contributionForm.notes || null,
        })
        window.showMessage?.(data.message || 'Abono registrado correctamente.')
        outing.value = data.outing
        closeContribution()
    } catch (error) {
        contributionMessage.value = error.response?.data?.message || 'No fue posible registrar el abono.'
    } finally {
        savingContribution.value = false
    }
}

onMounted(async () => {
    await Promise.all([fetchSettings(), fetchOuting()])
    await fetchEligibleNow()
})
</script>

<style scoped>
.outing-metric {
    border: 1px solid #e0e6ed;
    border-radius: 6px;
    padding: 12px;
    min-height: 76px;
}

.outing-metric__label {
    color: #6c757d;
    display: block;
    font-size: 0.78rem;
    margin-bottom: 6px;
}
</style>
