<template>
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="card">
                    <AppPageHeader
                        class="card-header"
                        title="Cargos personalizados"
                        subtitle="Cargos asignados a inscripciones antes de facturarse."
                        icon="fa fa-receipt"
                        data-tour="custom-charges-actions"
                    >
                        <template #actions>
                            <AppButton variant="outline-primary" size="sm" @click="reloadTable">
                                <i class="fa fa-sync" aria-hidden="true"></i>
                                Actualizar
                            </AppButton>
                            <AppButton variant="info" size="sm" @click="tutorial.start()">
                                <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                                Guía
                            </AppButton>
                        </template>
                    </AppPageHeader>

                    <div class="card-body">
                        <div class="table-responsive-sm" data-tour="custom-charges-table">
                            <ContentState
                                v-if="globalError"
                                type="error"
                                title="No fue posible cargar los cargos personalizados"
                                :message="globalError"
                                action-label="Reintentar"
                                class="mb-3"
                                @action="reloadTable"
                            />
                            <div v-show="!globalError">
                            <DatatableTemplate
                                id="custom-charges-table"
                                aria-label="Cargos personalizados"
                                ref="custom_charges_table"
                                :options="options"
                            >
                                <template #actions="props">
                                    <div class="d-inline-flex gap-2">
                                        <AppButton
                                            variant="outline-primary"
                                            size="sm"
                                            :disabled="props.rowData.status === 'paid' || deletingId === props.rowData.id"
                                            @click="openEditModal(props.rowData)"
                                        >
                                            Editar
                                        </AppButton>
                                        <AppButton
                                            variant="outline-danger"
                                            size="sm"
                                            :disabled="!canDeleteCharge(props.rowData) || deletingId === props.rowData.id"
                                            :loading="deletingId === props.rowData.id"
                                            loading-label="Eliminando..."
                                            @click="confirmDelete(props.rowData)"
                                        >
                                            Eliminar
                                        </AppButton>
                                    </div>
                                </template>
                            </DatatableTemplate>
                            </div>
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
                        <button type="button" class="btn-close" aria-label="Cerrar" :disabled="saving" @click="closeModal"></button>
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
                        <AppButton variant="secondary" :disabled="saving" @click="closeModal">
                            Cerrar
                        </AppButton>
                        <AppButton type="submit" :loading="saving" loading-label="Guardando...">
                            Guardar cambios
                        </AppButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <PageTutorialOverlay :tutorial="tutorial" />
</template>

<script setup>
import { onBeforeUnmount, onMounted, reactive, ref } from 'vue'
import api from '@/utils/axios'
import CurrencyInput from '@/components/general/CurrencyInput'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import ContentState from '@/components/general/ContentState.vue'
import AppButton from '@/components/general/AppButton.vue'
import AppPageHeader from '@/components/general/AppPageHeader.vue'
import useInscriptionCustomChargesList from '@/composables/invoices/inscriptionCustomChargesList'
import flatPickr from 'vue-flatpickr-component'
import { Spanish } from 'flatpickr/dist/l10n/es.js'
import 'flatpickr/dist/flatpickr.css'
import '@/assets/sass/forms/custom-flatpickr.css'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { customChargesTutorial } from '@/tutorials/invoices'

const saving = ref(false)
const tutorial = usePageTutorial(customChargesTutorial)
const deletingId = ref(null)
const modalElement = ref(null)
const formMessage = ref('')
const { options, reloadTable, globalError } = useInscriptionCustomChargesList()

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
        showMessage(data.message || 'Cargo personalizado eliminado correctamente.')
        reloadTable()
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
        reloadTable()
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
})

onBeforeUnmount(() => {
    modalInstance?.dispose()
})
</script>
