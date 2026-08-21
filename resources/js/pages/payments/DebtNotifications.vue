<template>
    <panel>
        <template #body>
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
                <div>
                    <h4 class="mb-1">Notificaciones de deuda</h4>
                    <p class="text-muted mb-0">Selecciona deportistas con deuda y envía el recordatorio por correo.</p>
                </div>
                <router-link :to="{ name: 'payments' }" class="btn btn-outline-primary btn-sm">
                    <i class="fa fa-arrow-left me-1" aria-hidden="true"></i>
                    Mensualidades
                </router-link>
            </div>

            <Form :validation-schema="schema" :initial-values="initialValues" class="row align-items-end" @submit="searchDebtors">
                <div class="col-xl-3 col-lg-4 col-sm-6 mb-2">
                    <label for="debt-notification-search" class="form-label">Nombre o código</label>
                    <Field
                        id="debt-notification-search"
                        name="search"
                        type="text"
                        class="form-control form-control-sm"
                        placeholder="Nombre, apellido o código"
                    />
                </div>
                <div class="col-xl-2 col-lg-3 col-sm-6 mb-2">
                    <label for="debt-notification-category" class="form-label">Categoría</label>
                    <Field
                        id="debt-notification-category"
                        name="category"
                        as="CustomSelect2"
                        :options="categories"
                        placeholder="Todas"
                    />
                </div>
                <div class="col-xl-3 col-lg-3 col-sm-6 mb-2">
                    <label for="debt-notification-group" class="form-label">Grupo de entrenamiento</label>
                    <Field
                        id="debt-notification-group"
                        name="training_group_id"
                        as="CustomSelect2"
                        :options="groups"
                        placeholder="Todos"
                    />
                </div>
                <div class="col-xl-2 col-lg-3 col-sm-6 mb-2">
                    <label for="debt-notification-month" class="form-label">Mes <span class="text-danger">*</span></label>
                    <Field
                        id="debt-notification-month"
                        name="month"
                        as="CustomSelect2"
                        :options="months"
                        placeholder="Selecciona un mes"
                    />
                    <ErrorMessage name="month" class="custom-error" />
                </div>
                <div class="col-xl-2 col-lg-3 col-sm-12 mb-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100" :disabled="isLoading">
                        <i class="fa fa-search me-1" aria-hidden="true"></i>
                        Buscar deudores
                    </button>
                </div>
            </Form>

            <hr class="bg-primary border-2 border-top border-primary" />

            <ContentState
                v-if="!hasSearched"
                type="empty"
                title="Selecciona el mes de la deuda"
                message="El mes es obligatorio. Puedes combinarlo con nombre, código, categoría o grupo de entrenamiento."
            />

            <template v-else>
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                    <span class="text-muted">{{ resultCount }} deportistas encontrados.</span>
                    <button
                        type="button"
                        class="btn btn-success btn-sm"
                        :disabled="selectedIds.length === 0 || isSending"
                        @click="confirmSend"
                    >
                        <span v-if="isSending" class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>
                        <i v-else class="fa fa-envelope me-1" aria-hidden="true"></i>
                        Enviar seleccionados ({{ selectedIds.length }})
                    </button>
                </div>

                <div class="alert alert-info py-2 px-3 mb-3" role="status">
                    <i class="fa fa-info-circle me-1" aria-hidden="true"></i>
                    “Seleccionar todos” aplica a la página visible. Puedes navegar entre páginas y la selección se conservará;
                    al cambiar los filtros, se limpiará.
                </div>

                <ContentState
                    v-if="tableError"
                    type="error"
                    title="No fue posible consultar los deudores"
                    :message="tableError"
                    action-label="Reintentar"
                    class="mb-3"
                    @action="reloadTable"
                />

                <div v-show="!tableError" class="table-responsive-md">
                    <DatatableTemplate
                        :key="tableKey"
                        id="debt-notifications-table"
                        ref="debtorsTable"
                        :options="tableOptions"
                        aria-label="Deportistas con deuda"
                    >
                        <template #thead>
                            <thead>
                                <tr>
                                    <th class="text-center">
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            data-select-visible-debtors
                                            aria-label="Seleccionar deportistas notificables de esta página"
                                            :checked="allVisibleSelected"
                                            @change="toggleVisibleRows"
                                        >
                                    </th>
                                    <th>Deportista</th>
                                    <th>Categoría</th>
                                    <th>Grupo</th>
                                    <th>Estado</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-center">Correo</th>
                                </tr>
                            </thead>
                        </template>

                        <template #debt-selection="props">
                            <div class="text-center">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    :data-debt-payment-id="props.rowData.payment_id"
                                    :checked="isSelected(props.rowData.payment_id)"
                                    :disabled="!props.rowData.can_notify"
                                    :aria-label="`Seleccionar a ${props.rowData.player_name}`"
                                    @change="toggleRow(props.rowData)"
                                >
                            </div>
                        </template>

                        <template #debt-player="props">
                            <div>
                                <div class="fw-semibold">{{ props.rowData.player_name }}</div>
                                <small class="text-muted">{{ props.rowData.unique_code }}</small>
                            </div>
                        </template>

                        <template #debt-status="props">
                            <span class="badge" :class="`payments-c-${props.rowData.status}`">
                                {{ props.rowData.status_label }}
                            </span>
                        </template>

                        <template #debt-email="props">
                            <span
                                class="badge"
                                :class="props.rowData.can_notify ? 'outline-badge-success' : 'outline-badge-danger'"
                            >
                                {{ props.rowData.can_notify ? 'Disponible' : 'Sin correo válido' }}
                            </span>
                        </template>
                    </DatatableTemplate>
                </div>
            </template>
        </template>
    </panel>

    <breadcrumb :parent="'Mensualidades'" :current="'Notificaciones de deuda'" />
</template>

<script setup>
import { computed, nextTick, onMounted, reactive, ref, useTemplateRef } from 'vue'
import { ErrorMessage, Field, Form } from 'vee-validate'
import * as yup from 'yup'
import api from '@/utils/axios'
import { useSetting } from '@/store/settings-store'
import { usePageTitle } from '@/composables/use-meta'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import ContentState from '@/components/general/ContentState.vue'
import configLanguaje from '@/utils/datatableUtils'
import { useRecoverableDataTable } from '@/composables/useRecoverableDataTable'

const settings = useSetting()
const monthFields = [
    'january',
    'february',
    'march',
    'april',
    'may',
    'june',
    'july',
    'august',
    'september',
    'october',
    'november',
    'december',
]
const currentMonth = monthFields[new Date().getMonth()]
const months = ref([])
const hasSearched = ref(false)
const isLoading = ref(false)
const isSending = ref(false)
const resultCount = ref(0)
const visibleRows = ref([])
const selectedIds = ref([])
const debtorsTable = useTemplateRef('debtorsTable')

const groups = computed(() => (settings.normal_training_groups.length ? settings.normal_training_groups : settings.groups)
    .filter((group) => group.name !== 'Provisional')
    .map((group) => ({ value: group.id, label: group.full_group })))
const categories = computed(() => settings.categories.map((item) => ({ value: item.category, label: item.category })))
const selectableVisibleRows = computed(() => visibleRows.value.filter((row) => row.can_notify))
const allVisibleSelected = computed(() => (
    selectableVisibleRows.value.length > 0
    && selectableVisibleRows.value.every((row) => selectedIds.value.includes(Number(row.payment_id)))
))

const schema = yup.object({
    month: yup.string().required('Selecciona el mes que deseas consultar.'),
    search: yup.string().nullable().optional(),
    category: yup.mixed().nullable().optional(),
    training_group_id: yup.mixed().nullable().optional(),
})

const initialValues = {
    month: currentMonth,
    search: '',
    category: null,
    training_group_id: null,
}

const filters = reactive({ ...initialValues })
const {
    globalError: tableError,
    tableKey,
    clearError: clearTableError,
    handleError: handleTableError,
    reloadTable,
} = useRecoverableDataTable(
    debtorsTable,
    'No fue posible consultar los deportistas con deuda.',
    'debt-notifications-table'
)

const emptyDataTableResponse = (draw = 0) => ({
    draw,
    data: [],
    recordsTotal: 0,
    recordsFiltered: 0,
})

const tableOptions = computed(() => ({
    ...configLanguaje,
    lengthMenu: [[10, 30, 50, 100], [10, 30, 50, 100]],
    pageLength: 10,
    processing: true,
    serverSide: true,
    deferRender: true,
    searching: false,
    order: [[1, 'asc']],
    drawCallback: syncVisibleSelection,
    ajax: async (data, callback) => {
        isLoading.value = true

        try {
            const datatableParams = { ...data }
            delete datatableParams.search

            const response = await api.get('/api/v2/payments/debt-notifications', {
                params: {
                    ...datatableParams,
                    month: filters.month,
                    ...(filters.search ? { search: filters.search } : {}),
                    ...(filters.category ? { category: filters.category } : {}),
                    ...(filters.training_group_id ? { training_group_id: filters.training_group_id } : {}),
                },
                skipGlobalLoader: true,
            })
            const payload = response.data

            visibleRows.value = payload.data ?? []
            resultCount.value = payload.recordsFiltered ?? 0
            clearTableError()
            callback({
                draw: data.draw,
                data: visibleRows.value,
                recordsTotal: payload.recordsTotal ?? 0,
                recordsFiltered: payload.recordsFiltered ?? 0,
            })
        } catch (error) {
            visibleRows.value = []
            resultCount.value = 0
            handleTableError(error)
            callback(emptyDataTableResponse(data.draw))
        } finally {
            isLoading.value = false
        }
    },
    columns: [
        { data: 'payment_id', name: 'payment_id', render: '#debt-selection', orderable: false, searchable: false, className: 'dt-head-center dt-body-center' },
        { data: 'player_name', name: 'player_name', render: '#debt-player' },
        { data: 'category', name: 'category' },
        { data: 'training_group', name: 'training_group' },
        { data: 'status_label', name: 'status', render: '#debt-status' },
        { data: 'amount', name: 'amount', render: (value) => window.moneyFormat ? window.moneyFormat(Number(value) || 0) : value, className: 'dt-head-right dt-body-right' },
        { data: 'can_notify', name: 'can_notify', render: '#debt-email', orderable: false, searchable: false, className: 'dt-head-center dt-body-center' },
    ],
}))

const clearSelection = () => {
    selectedIds.value = []
}

const isSelected = (paymentId) => selectedIds.value.includes(Number(paymentId))

function syncVisibleSelection() {
    const table = debtorsTable.value?.$el ?? document.getElementById('debt-notifications-table')

    table?.querySelectorAll('[data-debt-payment-id]').forEach((checkbox) => {
        checkbox.checked = isSelected(checkbox.dataset.debtPaymentId)
    })

    const selectVisibleCheckbox = table?.querySelector('[data-select-visible-debtors]')
    if (selectVisibleCheckbox) {
        selectVisibleCheckbox.checked = allVisibleSelected.value
    }
}

const scheduleSelectionSync = () => nextTick(syncVisibleSelection)

const toggleRow = (row) => {
    if (!row.can_notify) {
        return
    }

    const paymentId = Number(row.payment_id)
    selectedIds.value = isSelected(paymentId)
        ? selectedIds.value.filter((id) => id !== paymentId)
        : [...selectedIds.value, paymentId]
    scheduleSelectionSync()
}

const toggleVisibleRows = (event) => {
    const visibleIds = selectableVisibleRows.value.map((row) => Number(row.payment_id))

    selectedIds.value = event.target.checked
        ? [...new Set([...selectedIds.value, ...visibleIds])]
        : selectedIds.value.filter((id) => !visibleIds.includes(id))
    scheduleSelectionSync()
}

const searchDebtors = async (values) => {
    Object.assign(filters, {
        month: values.month,
        search: values.search?.trim() || null,
        category: values.category || null,
        training_group_id: values.training_group_id || null,
    })
    clearSelection()
    clearTableError()

    if (!hasSearched.value) {
        hasSearched.value = true
        await nextTick()
        return
    }

    const dt = debtorsTable.value?.table?.dt
    dt?.ajax.reload()
}

const confirmSend = async () => {
    if (selectedIds.value.length === 0 || isSending.value) {
        return
    }

    const confirmation = await window.Swal.fire({
        icon: 'question',
        title: 'Enviar notificaciones de deuda',
        text: `Se enviarán ${selectedIds.value.length} correo(s). ¿Deseas continuar?`,
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar',
    })

    if (!confirmation.isConfirmed) {
        return
    }

    isSending.value = true

    try {
        const response = await api.post('/api/v2/payments/debt-notifications/send', {
            month: filters.month,
            payment_ids: selectedIds.value,
        })
        const queuedCount = Number(response.data.data?.queued_count ?? 0)
        const skippedCount = Number(response.data.data?.skipped_count ?? 0)

        await window.Swal.fire({
            icon: queuedCount === 0 ? 'info' : (skippedCount > 0 ? 'warning' : 'success'),
            title: queuedCount === 0 ? 'Sin nuevos envíos' : 'Envío procesado',
            text: response.data.message,
        })
        clearSelection()
        debtorsTable.value?.table?.dt?.ajax.reload(null, false)
    } catch (error) {
        await window.Swal.fire({
            icon: 'error',
            title: 'No fue posible enviar',
            text: error.response?.data?.message
                ?? error.response?.data?.errors?.payment_ids?.[0]
                ?? 'Inténtalo nuevamente.',
        })
    } finally {
        isSending.value = false
    }
}

onMounted(async () => {
    usePageTitle('Notificaciones de deuda')

    try {
        const response = await api.get('/api/v2/payments/status-catalog')
        months.value = response.data.months ?? []
    } catch {
        months.value = []
    }
})
</script>
