<template>
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h4 class="mb-1"><i class="fa fa-receipt"></i> Cargos Personalizados</h4>
                            <small class="text-muted">Cargos asignados a inscripciones antes de facturarse.</small>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" :disabled="loading" @click="fetchCharges">
                            <i class="fa fa-sync"></i> Actualizar
                        </button>
                    </div>

                    <div class="card-body">
                        <div v-if="loading" class="text-center py-5">
                            <span class="spinner-border text-primary"></span>
                        </div>

                        <div v-else class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Jugador</th>
                                        <th>Año</th>
                                        <th>Cargo</th>
                                        <th>Valor</th>
                                        <th>Estado</th>
                                        <th>Vence</th>
                                        <th>Factura</th>
                                        <th class="text-end">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="!charges.length">
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No hay cargos personalizados asignados.
                                        </td>
                                    </tr>
                                    <tr v-for="charge in charges" :key="charge.id">
                                        <td>{{ charge.inscription?.player?.full_names || 'N/D' }}</td>
                                        <td>{{ charge.inscription?.year || 'N/D' }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ charge.name }}</div>
                                            <small class="text-muted">{{ charge.invoice_custom_item?.name || 'Snapshot histórico' }}</small>
                                        </td>
                                        <td>{{ moneyFormat(Number(charge.value || 0)) }}</td>
                                        <td>
                                            <span class="badge" :class="statusClass(charge.status)">
                                                {{ statusLabel(charge.status) }}
                                            </span>
                                        </td>
                                        <td>{{ formatDate(charge.due_date) }}</td>
                                        <td>{{ charge.invoice_item?.invoice?.invoice_number || 'Sin facturar' }}</td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-primary btn-sm"
                                                    :disabled="charge.status === 'paid' || deletingId === charge.id"
                                                    @click="openEditModal(charge)"
                                                >
                                                    Editar
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-outline-danger btn-sm"
                                                    :disabled="!canDeleteCharge(charge) || deletingId === charge.id"
                                                    @click="confirmDelete(charge)"
                                                >
                                                    <span v-if="deletingId === charge.id" class="spinner-border spinner-border-sm me-1"></span>
                                                    Eliminar
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div ref="modalElement" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form @submit.prevent="submitForm">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar cargo personalizado</h5>
                        <button type="button" class="btn-close" :disabled="saving" @click="closeModal"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="formMessage" class="alert alert-danger py-2">{{ formMessage }}</div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Cargo</small>
                            <div class="fw-semibold">{{ form.name }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="charge-value">Valor</label>
                            <CurrencyInput
                                id="charge-value"
                                v-model="form.value"
                                class="form-control"
                                :disabled="saving"
                                autocomplete="off"
                            />
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="charge-status">Estado</label>
                            <select id="charge-status" v-model="form.status" class="form-select" :disabled="saving">
                                <option value="pending">Pendiente</option>
                                <option value="due">Debe</option>
                                <option value="paid">Pagado</option>
                            </select>
                        </div>

                        <div class="mb-0">
                            <label class="form-label" for="charge-due-date">Fecha de vencimiento</label>
                            <flat-pickr
                                id="charge-due-date"
                                v-model="form.due_date"
                                :config="flatpickrConfig"
                                class="form-control"
                                :disabled="saving"
                            />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" :disabled="saving" @click="closeModal">
                            Cerrar
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                            Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import api from '@/utils/axios'
import CurrencyInput from '@/components/general/CurrencyInput'
import flatPickr from 'vue-flatpickr-component'
import { Spanish } from 'flatpickr/dist/l10n/es.js'
import 'flatpickr/dist/flatpickr.css'
import '@/assets/sass/forms/custom-flatpickr.css'

const charges = ref([])
const loading = ref(false)
const saving = ref(false)
const deletingId = ref(null)
const modalElement = ref(null)
const formMessage = ref('')

const form = reactive({
    id: null,
    name: '',
    value: 0,
    status: 'pending',
    due_date: null,
})

let modalInstance = null

const flatpickrConfig = {
    locale: Spanish,
}

const statusLabel = (status) => ({
    pending: 'Pendiente',
    due: 'Debe',
    paid: 'Pagado',
}[status] ?? status)

const statusClass = (status) => ({
    pending: 'badge-warning',
    due: 'badge-danger',
    paid: 'badge-success',
}[status] ?? 'badge-light')

const formatDate = (value) => {
    if (!value) {
        return 'N/D'
    }

    const parsedDate = new Date(value)
    return Number.isNaN(parsedDate.getTime()) ? 'N/D' : parsedDate.toLocaleDateString('es-CO')
}

const fetchCharges = async () => {
    loading.value = true

    try {
        const { data } = await api.get('/api/v2/admin/inscription-custom-charges')
        charges.value = Array.isArray(data) ? data : data.data ?? []
    } catch (error) {
        showMessage(error.response?.data?.message || 'No fue posible cargar los cargos personalizados.', 'error')
    } finally {
        loading.value = false
    }
}

const openEditModal = (charge) => {
    formMessage.value = ''
    form.id = charge.id
    form.name = charge.name
    form.value = Number(charge.value || 0)
    form.status = charge.status
    form.due_date = charge.due_date || null
    modalInstance?.show()
}

const closeModal = () => {
    modalInstance?.hide()
}

const canDeleteCharge = (charge) => {
    return charge.status !== 'paid' && !charge.invoice_item_id && !charge.invoice_item
}

const confirmDelete = async (charge) => {
    const result = await window.Swal.fire({
        title: '¿Eliminar cargo personalizado?',
        text: `Se eliminará "${charge.name}" de la inscripción.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    })

    if (!result.isConfirmed) {
        return
    }

    deletingId.value = charge.id

    try {
        const { data } = await api.delete(`/api/v2/admin/inscription-custom-charges/${charge.id}`)
        charges.value = charges.value.filter((currentCharge) => currentCharge.id !== charge.id)
        showMessage(data.message || 'Cargo personalizado eliminado correctamente.')
    } catch (error) {
        showMessage(error.response?.data?.message || 'No fue posible eliminar el cargo personalizado.', 'error')
    } finally {
        deletingId.value = null
    }
}

const submitForm = async () => {
    saving.value = true
    formMessage.value = ''

    try {
        const { data } = await api.put(`/api/v2/admin/inscription-custom-charges/${form.id}`, {
            value: Number(form.value || 0),
            status: form.status,
            due_date: form.due_date,
        })

        showMessage(data.message || 'Cargo personalizado actualizado correctamente.')
        closeModal()
        await fetchCharges()
    } catch (error) {
        formMessage.value = error.response?.data?.message || 'No fue posible actualizar el cargo personalizado.'
    } finally {
        saving.value = false
    }
}

onMounted(() => {
    if (modalElement.value) {
        modalInstance = new window.bootstrap.Modal(modalElement.value, {
            backdrop: 'static',
            keyboard: false,
        })
    }

    fetchCharges()
})

onBeforeUnmount(() => {
    modalInstance?.dispose()
})
</script>
