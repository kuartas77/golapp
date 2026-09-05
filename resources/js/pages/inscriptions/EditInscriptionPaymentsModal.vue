<template>
    <div ref="modalElement" class="modal fade" tabindex="-1" aria-labelledby="editPaymentsModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="editPaymentsModalTitle" class="modal-title">Modificar mensualidades</h5>
                    <button type="button" class="btn-close" aria-label="Cerrar" @click="close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="payment" class="mb-3"><strong>{{ payment.player?.full_names }}</strong><small class="text-muted ms-2">{{ payment.player?.unique_code }}</small></div>
                    <div v-if="successMessage" class="alert alert-success py-2" role="alert">{{ successMessage }}</div>
                    <div v-if="isLoading" class="text-center py-4">Cargando mensualidades...</div>
                    <div v-else-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
                    <div v-else-if="payment" class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead><tr><th>Mes</th><th>Estado</th><th>Monto</th><th></th></tr></thead>
                            <tbody>
                                <tr v-for="month in months" :key="month.value">
                                    <td>{{ month.label }}</td>
                                    <td><select v-model="payment[month.value]" class="form-select form-select-sm" :disabled="!canEdit(month.value)" @change.stop="markDirty"><option v-for="status in editableStatuses(month.value)" :key="status.value" :value="Number(status.value)">{{ status.label }}</option></select></td>
                                    <td><CurrencyInput v-model="payment[`${month.value}_amount`]" class="form-control form-control-sm" :disabled="!canEdit(month.value)" /></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" @click="close">Cerrar</button>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        :disabled="isLoading || isSaving || !hasChanges"
                        @click.stop="saveAll"
                    >
                        {{ isSaving ? 'Guardando...' : 'Guardar cambios' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import api from '@/utils/axios'
import CurrencyInput from '@/components/general/CurrencyInput'
import { useAuthUser } from '@/store/auth-user'

const props = defineProps({ modelValue: Boolean, uniqueCode: { type: String, required: true }, year: { type: [String, Number], required: true } })
const emit = defineEmits(['update:model-value'])
const auth = useAuthUser()
const modalElement = ref(null)
const payment = ref(null)
const originalPayment = ref(null)
const months = [['enrollment', 'Matrícula'], ['january', 'Enero'], ['february', 'Febrero'], ['march', 'Marzo'], ['april', 'Abril'], ['may', 'Mayo'], ['june', 'Junio'], ['july', 'Julio'], ['august', 'Agosto'], ['september', 'Septiembre'], ['october', 'Octubre'], ['november', 'Noviembre'], ['december', 'Diciembre']].map(([value, label]) => ({ value, label }))
const statusCatalog = ref({ statuses: [], capabilities: null })
const isLoading = ref(false)
const isSaving = ref(false)
const isDirty = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
let modalInstance = null

const isAssistant = () => auth.hasRole('assistant')
const canEdit = (field) => {
    if (!payment.value || payment.value.inscription_deleted || !isAssistant()) return Boolean(payment.value)
    const capabilities = statusCatalog.value.capabilities
    const sourceStatus = originalPayment.value?.[field] ?? payment.value[field]
    return Boolean(capabilities?.fields?.includes(field) && capabilities.source_statuses.includes(Number(sourceStatus)))
}
const editableStatuses = (field) => {
    const statuses = statusCatalog.value.statuses || []
    if (!isAssistant()) return statuses
    const targets = statusCatalog.value.capabilities?.target_statuses || []
    return statuses.filter((status) => targets.includes(Number(status.value)) || Number(status.value) === Number(payment.value?.[field]))
}
const close = () => emit('update:model-value', false)
const markDirty = () => {
    isDirty.value = true
}
const changedFields = () => months
    .filter((month) => canEdit(month.value))
    .filter((month) => payment.value?.[month.value] !== originalPayment.value?.[month.value]
        || payment.value?.[`${month.value}_amount`] !== originalPayment.value?.[`${month.value}_amount`])
    .map((month) => month.value)
const hasChanges = computed(() => Boolean(payment.value && originalPayment.value && changedFields().length))
const load = async () => {
    isLoading.value = true
    errorMessage.value = ''
    successMessage.value = ''
    try {
        const [paymentResponse, catalogResponse] = await Promise.all([
            api.get('/api/v2/payments', { params: { year: props.year, player_search: props.uniqueCode, dataRaw: true } }),
            api.get('/api/v2/payments/status-catalog'),
        ])
        const loadedPayment = paymentResponse.data?.rows?.[0] || null
        payment.value = loadedPayment ? { ...loadedPayment } : null
        originalPayment.value = loadedPayment ? { ...loadedPayment } : null
        isDirty.value = false
        statusCatalog.value = catalogResponse.data || statusCatalog.value
        if (!payment.value) errorMessage.value = 'No se encontraron mensualidades para este deportista.'
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'No fue posible cargar las mensualidades.'
    } finally { isLoading.value = false }
}
const saveAll = async () => {
    if (!payment.value || !hasChanges.value || isSaving.value) return
    isSaving.value = true
    errorMessage.value = ''
    successMessage.value = ''
    try {
        const fields = changedFields()
        for (const field of fields) {
            const response = await api.post(`/api/v2/payments/${payment.value.id}`, { _method: 'PUT', column: field, [field]: payment.value[field], [`${field}_amount`]: payment.value[`${field}_amount`] })
            if (response.data?.data) {
                payment.value = { ...payment.value, ...response.data.data }
            }
        }
        originalPayment.value = { ...payment.value }
        isDirty.value = false
        successMessage.value = 'Los cambios de mensualidades se guardaron correctamente.'
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'No fue posible guardar la mensualidad.'
    } finally { isSaving.value = false }
}
watch(() => props.modelValue, async (isOpen) => {
    if (!isOpen) return
    await nextTick()
    modalInstance = window.bootstrap?.Modal?.getOrCreateInstance(modalElement.value)
    modalInstance?.show()
    await load()
}, { immediate: true })
onBeforeUnmount(() => modalInstance?.dispose())
</script>