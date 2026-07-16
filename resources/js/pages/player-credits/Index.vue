<template>
    <panel>
        <template #body>
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h5 class="mb-1">Saldos a favor</h5>
                            <small class="text-muted">Consulta bolsas con movimientos o busca un deportista para cargar su primer saldo.</small>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge outline-badge-info">Saldo total {{ moneyFormat(summary.total_balance || 0) }}</span>
                            <span class="badge outline-badge-info">Con saldo {{ summary.players_with_balance || 0 }}</span>
                        </div>
                    </div>

                    <DatatableTemplate
                        ref="creditsTable"
                        id="player_credits_table"
                        :options="tableOptions"
                    >
                        <template #thead>
                            <thead>
                                <tr>
                                    <th>Deportista</th>
                                    <th>Grupo</th>
                                    <th class="text-end">Cargas</th>
                                    <th class="text-end">Descuentos</th>
                                    <th class="text-end">Saldo</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                        </template>

                        <template #player-credit-player="props">
                            <div class="d-flex align-items-center gap-2">
                                <img :src="props.rowData.photo_url" alt="avatar" class="player-credit-avatar" />
                                <div>
                                    <div>{{ props.rowData.full_names }}</div>
                                    <small class="text-muted">{{ props.rowData.unique_code }} | {{ props.rowData.category || 'Sin categoría' }}</small>
                                </div>
                            </div>
                        </template>

                        <template #money="props">
                            <div class="text-end">{{ moneyFormat(props.cellData || 0) }}</div>
                        </template>

                        <template #balance="props">
                            <div class="text-end fw-semibold">{{ moneyFormat(props.cellData || 0) }}</div>
                        </template>

                        <template #actions="props">
                            <button type="button" class="btn btn-outline-info btn-sm" @click="selectPlayer(props.rowData)">
                                Ver
                            </button>
                        </template>
                    </DatatableTemplate>
                </div>

                <div class="col-lg-4">
                    <div v-if="selectedPlayer" class="player-credit-side">
                        <h6 class="mb-1">{{ selectedPlayer.full_names }}</h6>
                        <p class="text-muted mb-2">{{ selectedPlayer.unique_code }}</p>
                        <div class="alert alert-info py-2">
                            Saldo disponible: <strong>{{ moneyFormat(detail.balance || selectedPlayer.balance || 0) }}</strong>
                        </div>

                        <form class="row g-2 mb-3" @submit.prevent="submitMovement">
                            <div class="col-6">
                                <label class="form-label">Tipo</label>
                                <select v-model="form.type" class="form-select form-select-sm" required>
                                    <option value="credit">Carga</option>
                                    <option value="debit">Descuento</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Monto</label>
                                <CurrencyInput v-model="form.amount" class="form-control form-control-sm" required />
                            </div>
                            <div class="col-12">
                                <label class="form-label">Fecha</label>
                                <input v-model="form.movement_date" type="date" class="form-control form-control-sm" required />
                            </div>
                            <div class="col-12">
                                <label class="form-label">Concepto</label>
                                <input v-model="form.concept" type="text" class="form-control form-control-sm" maxlength="150" required />
                            </div>
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea v-model="form.notes" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary btn-sm w-100" type="submit" :disabled="isSaving">
                                    Registrar movimiento
                                </button>
                            </div>
                        </form>

                        <h6>Historial</h6>
                        <div class="player-credit-history">
                            <div v-for="movement in detail.movements" :key="movement.id" class="border-bottom py-2">
                                <div class="d-flex justify-content-between">
                                    <strong>{{ movement.concept }}</strong>
                                    <span :class="movement.type === 'credit' ? 'text-success' : 'text-danger'">
                                        {{ movement.type === 'credit' ? '+' : '-' }}{{ moneyFormat(movement.amount) }}
                                    </span>
                                </div>
                                <small class="text-muted">{{ movement.movement_date }} · {{ movement.creator?.name || 'Sistema' }}</small>
                            </div>
                            <p v-if="!detail.movements?.length" class="text-muted">Sin movimientos registrados.</p>
                        </div>
                    </div>
                    <div v-else class="alert alert-light border">
                        Busca o selecciona un deportista para ver su historial y registrar movimientos.
                    </div>
                </div>
            </div>
        </template>
    </panel>
    <breadcrumb :parent="'Plataforma'" :current="'Saldos a favor'" />
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import api from '@/utils/axios'
import CurrencyInput from '@/components/general/CurrencyInput'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import configLanguaje from '@/utils/datatableUtils'
import { usePageTitle } from '@/composables/use-meta'

const summary = ref({})
const detail = ref({ movements: [], balance: 0 })
const selectedPlayer = ref(null)
const isSaving = ref(false)
const creditsTable = ref(null)
const today = new Date().toISOString().slice(0, 10)
const form = reactive({
    type: 'credit',
    amount: 0,
    movement_date: today,
    concept: '',
    notes: '',
})

const tableOptions = computed(() => ({
    ...configLanguaje,
    processing: true,
    serverSide: true,
    searching: true,
    order: [[4, 'desc']],
    pageLength: 10,
    lengthMenu: [10, 20, 50],
    ajax: async (data, callback) => {
        const response = await api.get('/api/v2/player-credits/datatable', { params: data })
        callback(response.data)
    },
    columns: [
        { data: 'full_names', title: 'Deportista', render: '#player-credit-player' },
        { data: 'training_group', title: 'Grupo', defaultContent: 'Sin grupo' },
        { data: 'credit_total', title: 'Cargas', render: '#money', className: 'text-end' },
        { data: 'debit_total', title: 'Descuentos', render: '#money', className: 'text-end' },
        { data: 'balance', title: 'Saldo', render: '#balance', className: 'text-end' },
        { data: 'id', title: 'Acciones', render: '#actions', orderable: false, searchable: false, className: 'text-center' },
    ],
}))

const fetchSummary = async () => {
    const response = await api.get('/api/v2/player-credits')
    summary.value = response.data.data.summary
}

const reloadTable = () => {
    creditsTable.value?.table?.dt?.ajax?.reload(null, false)
}

const selectPlayer = async (player) => {
    selectedPlayer.value = player
    const response = await api.get(`/api/v2/player-credits/${player.id}`)
    detail.value = response.data.data
}

const resetForm = () => {
    form.type = 'credit'
    form.amount = 0
    form.movement_date = today
    form.concept = ''
    form.notes = ''
}

const submitMovement = async () => {
    if (!selectedPlayer.value) {
        return
    }

    isSaving.value = true
    try {
        await api.post(`/api/v2/player-credits/${selectedPlayer.value.id}/movements`, form)
        await selectPlayer(selectedPlayer.value)
        await fetchSummary()
        reloadTable()
        resetForm()
        showMessage('Movimiento registrado correctamente')
    } catch (error) {
        showMessage(error.response?.data?.message || 'No fue posible registrar el movimiento', 'error')
    } finally {
        isSaving.value = false
    }
}

onMounted(() => {
    usePageTitle('Saldos a favor')
    fetchSummary()
})
</script>

<style scoped>
.player-credit-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    object-fit: cover;
}

.player-credit-side {
    border-left: 1px solid var(--bs-border-color, #e0e6ed);
    padding-left: 16px;
}

.player-credit-history {
    max-height: 360px;
    overflow-y: auto;
}
</style>
