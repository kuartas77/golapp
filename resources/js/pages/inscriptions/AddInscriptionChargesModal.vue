<template>
    <div ref="modalElement" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form class="modal-content" @submit.prevent="save">
                <div class="modal-header">
                    <h5 class="modal-title">Agregar cargos personalizados</h5>
                    <button type="button" class="btn-close" :disabled="saving" @click="close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="error" class="alert alert-danger">{{ error }}</div>
                    <div v-if="existing.length" class="mb-4">
                        <h6>Cargos registrados</h6>
                        <div class="table-responsive"><table class="table table-sm">
                            <thead><tr><th>Concepto</th><th>Vence</th><th>Valor</th><th>Estado</th></tr></thead>
                            <tbody><tr v-for="charge in existing" :key="charge.id">
                                <td>{{ charge.name }}</td><td>{{ charge.due_date }}</td><td>{{ money(charge.value) }}</td><td>{{ statusLabel(charge.status) }}</td>
                            </tr></tbody>
                        </table></div>
                    </div>

                    <h6>Nuevos cargos</h6>
                    <div v-for="(row, index) in rows" :key="row.key" class="row g-2 align-items-end border rounded p-2 mb-2 charge-row">
                        <div class="col-md-5">
                            <label class="form-label">Concepto</label>
                            <select v-model="row.invoice_custom_item_id" class="form-select" required @change="applyDefaultValue(row)">
                                <option value="">Selecciona...</option>
                                <option v-for="item in availableOptions(row)" :key="item.id" :value="item.id">{{ item.name }}</option>
                            </select>
                        </div>
                        <div class="col-md-3"><label class="form-label">Valor</label><input v-model.number="row.value" type="number" min="1" step="1" class="form-control" required></div>
                        <div class="col-md-3"><label class="form-label">Fecha de vencimiento</label><input v-model="row.due_date" type="date" class="form-control" required></div>
                        <div class="col-md-1"><button type="button" class="btn btn-outline-danger" :disabled="rows.length === 1" title="Retirar selección" @click="rows.splice(index, 1)"><i class="fa fa-trash"></i></button></div>
                    </div>
                    <button type="button" class="btn btn-outline-primary btn-sm" :disabled="rows.length >= options.length" @click="addRow"><i class="fa fa-plus me-1"></i>Otro cargo</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" :disabled="saving" @click="close">Cancelar</button>
                    <button type="submit" class="btn btn-primary" :disabled="saving || !options.length"><span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>Guardar cargos</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import api from '@/utils/axios'

const props = defineProps({ modelValue: Boolean, inscriptionId: { type: Number, required: true } })
const emit = defineEmits(['update:modelValue', 'saved'])
const modalElement = ref(null)
const options = ref([])
const existing = ref([])
const rows = ref([])
const saving = ref(false)
const error = ref('')
let modal
let key = 0
const newRow = () => ({ key: ++key, invoice_custom_item_id: '', value: '', due_date: '' })
const addRow = () => rows.value.push(newRow())
const money = (value) => Number(value || 0).toLocaleString('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 })
const statusLabel = (status) => ({ pending: 'Pendiente', due: 'Debe', paid: 'Pagado' }[status] || status)
const availableOptions = (row) => {
    const selected = new Set(rows.value.filter((item) => item !== row).map((item) => Number(item.invoice_custom_item_id)))
    const activeIds = new Set(existing.value.filter((item) => ['pending', 'due'].includes(item.status) && !item.invoice_id).map((item) => Number(item.invoice_custom_item_id)))
    return options.value.filter((item) => !selected.has(Number(item.id)) && !activeIds.has(Number(item.id)))
}
const applyDefaultValue = (row) => {
    const item = options.value.find((option) => Number(option.id) === Number(row.invoice_custom_item_id))
    if (item && !row.value) row.value = Number(item.unit_price)
}
const close = () => { modal?.hide(); emit('update:modelValue', false) }
const firstError = (requestError) => Object.values(requestError.response?.data?.errors || {}).flat()[0] || requestError.response?.data?.message

async function save() {
    saving.value = true
    error.value = ''
    try {
        await api.post(`/api/v2/inscriptions/${props.inscriptionId}/custom-charges`, {
            charges: rows.value.map(({ invoice_custom_item_id, value, due_date }) => ({ invoice_custom_item_id, value, due_date })),
        })
        emit('saved')
        close()
    } catch (requestError) {
        error.value = firstError(requestError) || 'No fue posible guardar los cargos.'
    } finally { saving.value = false }
}

onMounted(async () => {
    modal = new window.bootstrap.Modal(modalElement.value, { backdrop: 'static', keyboard: false })
    try {
        const [optionsResponse, existingResponse] = await Promise.all([
            api.get(`/api/v2/inscriptions/${props.inscriptionId}/custom-charge-options`),
            api.get(`/api/v2/inscriptions/${props.inscriptionId}/custom-charges`),
        ])
        options.value = optionsResponse.data || []
        existing.value = existingResponse.data || []
    } catch (requestError) {
        error.value = firstError(requestError) || 'No fue posible cargar los conceptos.'
    }
    addRow()
    modal.show()
})
onBeforeUnmount(() => modal?.dispose())
</script>

<style scoped>
:global(body.dark .charge-row) { border-color: #3b3f4a !important; }
</style>
