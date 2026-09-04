<template>
    <panel>
        <template #header>
            <AppPageHeader
                :title="documentPlural"
                :subtitle="`Consulta montos, pagos, estados y accesos al detalle de cada ${documentSingular.toLowerCase()}.`"
                icon="fa fa-file-invoice"
                data-tour="invoices-index-actions"
            >
                <template #actions>
                    <AppButton v-if="!isReadOnly" variant="primary" size="sm" class="invoice-toolbar-action" @click="openCreateInvoiceModal">
                        <i class="fa fa-plus" aria-hidden="true"></i>
                        Crear {{ documentSingular.toLowerCase() }}
                    </AppButton>
                    <AppButton variant="info" size="sm" class="invoice-toolbar-action" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question" aria-hidden="true"></i>
                        Guía
                    </AppButton>
                </template>
            </AppPageHeader>
        </template>
        <template #body>
            <div class="row g-3 align-items-end mb-3" data-tour="invoices-index-filters">
                <div class="col-12 invoice-status-filter">
                    <div class="form-group mb-0">
                        <label class="form-label" for="filterStatus">Estado</label>
                        <select class="form-select form-select-sm" id="filterStatus">
                            <option value="">Todos los estados</option>
                            <option value="pending">Pendiente</option>
                            <option value="partial">Parcial</option>
                            <option value="paid">{{ auth.user?.electronic_invoicing_enabled ? 'Pagada' : 'Pagado' }}</option>
                            <option value="cancelled">{{ auth.user?.electronic_invoicing_enabled ? 'Cancelada' : 'Cancelado' }}</option>
                        </select>
                    </div>
                </div>
                <div class="col-12 invoice-date-filter">
                    <div class="form-group mb-0">
                        <label class="form-label" for="filterDate">Rango fecha facturación</label>
                        <div class="input-group">
                            <flat-pickr
                                id="filterDate"
                                v-model="filterDate"
                                :config="flatpickrConfig"
                                class="form-control form-control-sm flatpickr"
                                placeholder="Selecciona un rango"
                            />
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-sm"
                                aria-label="Limpiar rango de fechas"
                                title="Limpiar rango de fechas"
                                @click="clearDate"
                            >
                                <i class="fa-solid fa-x" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="groupOptionsLoaded" data-tour="invoices-index-table">
                <ContentState
                    v-if="globalError"
                    type="error"
                    :title="`No fue posible cargar ${documentPlural.toLowerCase()}`"
                    :message="globalError"
                    action-label="Reintentar"
                    class="mb-3"
                    @action="reloadTable"
                />
                <div v-show="!globalError">
                <DatatableTemplate :options="options" :id="'invoives_table'" :aria-label="documentPlural" ref="invoives_table" @click="onClickRow">
                <template #thead>
                    <thead>
                        <tr>
                            <th>
                                <div>
                                    <input
                                        v-model="invoiceNumberFilter"
                                        type="search"
                                        class="form-control form-control-sm datatable-header-filter-input"
                                        :placeholder="`# ${documentSingular}`"
                                        :aria-label="`Buscar por número de ${documentSingular.toLowerCase()}`"
                                        autocomplete="off"
                                        @click.stop
                                        @keydown.stop
                                        @input="applyInvoiceNumberFilter"
                                    >
                                </div>
                            </th>
                            <th>
                                <div>
                                    <input
                                        v-model="studentNameFilter"
                                        type="search"
                                        class="form-control form-control-sm datatable-header-filter-input"
                                        placeholder="Deportista"
                                        aria-label="Buscar por deportista"
                                        autocomplete="off"
                                        @click.stop
                                        @keydown.stop
                                        @input="applyStudentNameFilter"
                                    >
                                </div>
                            </th>
                            <th>
                                <div>
                                    <select
                                        v-model="trainingGroupFilter"
                                        class="form-select form-select-sm form-select-custom"
                                        aria-label="Filtrar por grupo"
                                        @click.stop
                                        @keydown.stop
                                        @change="applyTrainingGroupFilter"
                                    >
                                        <option value="">Grupo</option>
                                        <option v-for="group in groupOptions" :key="group.value" :value="group.value">
                                            {{ group.label }}
                                        </option>
                                    </select>
                                </div>
                            </th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Pagado</th>
                            <th class="text-center">Estado</th>
                            <th>Fecha</th>
                            <th><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Totales de esta página:</th>
                            <th></th>
                            <th></th>
                            <th class="text-right"></th>
                            <th class="text-right"></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </template>
                </DatatableTemplate>
                </div>
            </div>

        </template>

    </panel>

    <div
        v-if="createInvoiceModalOpen"
        ref="createInvoiceDialog"
        class="modal fade show d-block"
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby="create-invoice-modal-title"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form @submit.prevent="continueToInvoiceCreate">
                    <div class="modal-header">
                        <h5 id="create-invoice-modal-title" class="modal-title">Crear {{ documentSingular.toLowerCase() }}</h5>
                        <button type="button" class="btn-close" aria-label="Cerrar" @click="closeCreateInvoiceModal"></button>
                    </div>
                    <div class="modal-body">
                        <ContentState
                            v-if="creationInscriptionsLoading"
                            type="loading"
                            title="Cargando inscripciones"
                            :message="`Estamos preparando los deportistas disponibles para crear el ${documentSingular.toLowerCase()}.`"
                        />
                        <ContentState
                            v-else-if="creationInscriptionsError"
                            type="error"
                            title="No fue posible cargar las inscripciones"
                            :message="creationInscriptionsError"
                            action-label="Reintentar"
                            @action="loadCreationInscriptions"
                        />
                        <ContentState
                            v-else-if="!creationInscriptionOptions.length"
                            type="empty"
                            title="No hay inscripciones disponibles"
                            message="No encontramos inscripciones vigentes para el año actual."
                        />
                        <div v-else>
                            <label class="form-label" for="invoice-inscription-select">Inscripción</label>
                            <CustomSelect2
                                id="invoice-inscription-select"
                                v-model="selectedCreationInscriptionId"
                                :options="creationInscriptionOptions"
                                placeholder="Selecciona una inscripción"
                                search-placeholder="Buscar por deportista, código o grupo..."
                                :aria-label="`Inscripción para crear ${documentSingular.toLowerCase()}`"
                            />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <AppButton variant="secondary" @click="closeCreateInvoiceModal">
                            Cancelar
                        </AppButton>
                        <AppButton
                            type="submit"
                            variant="primary"
                            :disabled="!selectedCreationInscriptionId || creationInscriptionsLoading"
                        >
                            Continuar
                        </AppButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div v-if="createInvoiceModalOpen" class="modal-backdrop fade show"></div>

    <breadcrumb :parent="'Plataforma'" :current="documentPlural" />
    <PageTutorialOverlay :tutorial="tutorial" />

</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import ContentState from '@/components/general/ContentState.vue'
import AppButton from '@/components/general/AppButton.vue'
import AppPageHeader from '@/components/general/AppPageHeader.vue'
import CustomSelect2 from '@/components/form/CustomSelect2.vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import useInvoicesList from '@/composables/invoices/invoicesList'
import { useDialogFocusTrap } from '@/composables/useDialogFocusTrap'
import { usePageTutorial } from '@/composables/usePageTutorial'
import api from '@/utils/axios'
import dayjs from '@/utils/dayjs';
import { Spanish } from "flatpickr/dist/l10n/es.js"
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import "@/assets/sass/forms/custom-flatpickr.css";
import { invoicesIndexTutorial } from '@/tutorials/invoices'
import { useAuthUser } from '@/store/auth-user'
import { invoiceDocumentPlural, invoiceDocumentSingularForSchool } from '@/utils/invoiceTerminology'

const auth = useAuthUser()
const isReadOnly = computed(() => auth.hasRole('viewer'))
const documentPlural = computed(() => invoiceDocumentPlural(Boolean(auth.user?.electronic_invoicing_enabled)))
const documentSingular = computed(() => invoiceDocumentSingularForSchool(Boolean(auth.user?.electronic_invoicing_enabled)))

const flatpickrConfig = {
    wrap: true,
    mode: "range",
    locale: Spanish,
    maxDate: dayjs().format('YYYY-M-D'),
    minDate: dayjs().subtract(5, 'year').format('YYYY-M-D'),
}
const {
    options,
    invoives_table,
    filterDate,
    clearDate,
    onClickRow,
    reloadTable,
    invoiceNumberFilter,
    studentNameFilter,
    trainingGroupFilter,
    groupOptions,
    groupOptionsLoaded,
    globalError,
    applyInvoiceNumberFilter,
    applyStudentNameFilter,
    applyTrainingGroupFilter,
} = useInvoicesList()
const router = useRouter()
const tutorial = usePageTutorial(invoicesIndexTutorial)
const createInvoiceModalOpen = ref(false)
const createInvoiceDialog = ref(null)
const creationInscriptions = ref([])
const creationInscriptionsLoading = ref(false)
const creationInscriptionsError = ref('')
const selectedCreationInscriptionId = ref(null)

const creationInscriptionOptions = computed(() => creationInscriptions.value.map((inscription) => ({
    value: String(inscription.id),
    label: `${inscription.player_name} · ${inscription.unique_code}`,
    meta: inscription.training_group_name || '',
})))

const loadCreationInscriptions = async () => {
    creationInscriptionsLoading.value = true
    creationInscriptionsError.value = ''

    try {
        const response = await api.get('/api/v2/invoices/creation-inscriptions')
        creationInscriptions.value = response.data?.data || []
    } catch (error) {
        creationInscriptions.value = []
        creationInscriptionsError.value = error.response?.data?.message
            || 'Intenta nuevamente. Si el problema continúa, comunícate con soporte.'
    } finally {
        creationInscriptionsLoading.value = false
    }
}

const openCreateInvoiceModal = () => {
    selectedCreationInscriptionId.value = null
    createInvoiceModalOpen.value = true
    loadCreationInscriptions()
}

const closeCreateInvoiceModal = () => {
    createInvoiceModalOpen.value = false
}

const continueToInvoiceCreate = () => {
    if (!selectedCreationInscriptionId.value) {
        return
    }

    const inscriptionId = selectedCreationInscriptionId.value
    closeCreateInvoiceModal()
    router.push({ name: 'invoices.create', params: { inscription: inscriptionId } })
}

useDialogFocusTrap(createInvoiceDialog, createInvoiceModalOpen, {
    onEscape: closeCreateInvoiceModal,
})
</script>

<style scoped>
.cursor-pointer {
    cursor: pointer;
}

.cursor-pointer:hover {
    background-color: #f8f9fa;
}

.page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}

.badge {
    font-size: 0.85em;
    padding: 0.25em 0.6em;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

@media (max-width: 575.98px) {
    .invoice-toolbar-action {
        width: 100%;
    }
}

@media (min-width: 768px) {
    .invoice-status-filter {
        width: 14rem;
    }

    .invoice-date-filter {
        width: 24rem;
    }
}

</style>
