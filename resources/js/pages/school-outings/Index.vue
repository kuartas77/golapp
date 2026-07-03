<template>
    <panel>
        <template #body>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3" data-tour="school-outings-actions">
                <div>
                    <h4 class="mb-1">Salidas</h4>
                    <p class="text-muted mb-0">Planea salidas y controla el avance de aportes por deportista.</p>
                </div>
                <div class="d-flex gap-2"><button type="button" class="btn btn-info btn-sm" @click="openForm()">
                    <i class="fa fa-plus me-2" aria-hidden="true"></i>
                    Nueva salida
                </button><button type="button" class="btn btn-info btn-sm" @click="tutorial.start()"><i class="fa-regular fa-circle-question me-2"></i>Guía</button></div>
            </div>

            <div v-if="globalError" class="alert alert-danger" role="alert">{{ globalError }}</div>

            <div v-if="loading" class="text-center text-muted py-4">
                Cargando salidas...
            </div>

            <div v-else class="table-responsive-md" data-tour="school-outings-table">
                <DatatableTemplate
                    :key="tableVersion"
                    id="school_outings_table"
                    :options="tableOptions"
                    :data="outings"
                >
                    <template #thead>
                        <thead class="align-middle">
                            <tr>
                                <th>Salida</th>
                                <th>Fecha</th>
                                <th class="text-center">Deportistas</th>
                                <th class="text-end">Meta</th>
                                <th class="text-end">Recaudado</th>
                                <th class="text-end">Pendiente</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                    </template>

                    <template #actions="props">
                        <div class="d-inline-flex gap-2">
                            <button type="button" class="btn btn-outline-info btn-sm" @click="goToOuting(props.rowData)">
                                Ver
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm" :disabled="props.rowData.is_locked" @click="openForm(props.rowData)">
                                Editar
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" :disabled="props.rowData.is_locked" @click="changeStatus(props.rowData, 'closed')">
                                Cerrar
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" :disabled="props.rowData.is_locked" @click="changeStatus(props.rowData, 'cancelled')">
                                Cancelar
                            </button>
                        </div>
                    </template>
                </DatatableTemplate>
            </div>
        </template>
    </panel>
    <PageTutorialOverlay :tutorial="tutorial" />

    <div v-if="showForm" class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form @submit.prevent="saveOuting">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ form.id ? 'Editar salida' : 'Nueva salida' }}</h5>
                        <button type="button" class="btn-close" :disabled="saving" @click="closeForm"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="formMessage" class="alert alert-danger" role="alert">{{ formMessage }}</div>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label" for="outing-name">Nombre</label>
                                <input id="outing-name" v-model.trim="form.name" type="text" class="form-control" :class="{ 'is-invalid': formErrors.name }">
                                <div v-if="formErrors.name" class="invalid-feedback">{{ formErrors.name }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="outing-date">Fecha de salida</label>
                                <input id="outing-date" v-model="form.departure_date" type="date" class="form-control" :class="{ 'is-invalid': formErrors.departure_date }">
                                <div v-if="formErrors.departure_date" class="invalid-feedback">{{ formErrors.departure_date }}</div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label" for="outing-amount">Valor por deportista</label>
                                <CurrencyInput id="outing-amount" v-model="form.amount_per_player" class="form-control" :class="{ 'is-invalid': formErrors.amount_per_player }" />
                                <div v-if="formErrors.amount_per_player" class="invalid-feedback d-block">{{ formErrors.amount_per_player }}</div>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label" for="outing-notes">Notas</label>
                                <input id="outing-notes" v-model.trim="form.notes" type="text" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" :disabled="saving" @click="closeForm">Cancelar</button>
                        <button type="submit" class="btn btn-info" :disabled="saving">
                            <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/utils/axios'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import CurrencyInput from '@/components/general/CurrencyInput'
import configLanguaje from '@/utils/datatableUtils'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { schoolOutingsTutorial } from '@/tutorials/operations'

const router = useRouter()
const tutorial = usePageTutorial(schoolOutingsTutorial)
const outings = ref([])
const loading = ref(false)
const saving = ref(false)
const tableVersion = ref(0)
const showForm = ref(false)
const globalError = ref('')
const formMessage = ref('')
const formErrors = reactive({})

const form = reactive({
    id: null,
    name: '',
    departure_date: '',
    amount_per_player: 0,
    notes: '',
})

const resetErrors = () => {
    Object.keys(formErrors).forEach((key) => delete formErrors[key])
    formMessage.value = ''
}

const resetForm = () => {
    form.id = null
    form.name = ''
    form.departure_date = ''
    form.amount_per_player = 0
    form.notes = ''
    resetErrors()
}

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

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;')

const renderOutingName = (data, type, row) => {
    const name = row?.name ?? ''
    const notes = row?.notes ?? ''

    if (type === 'filter') {
        return `${name} ${notes}`.trim()
    }

    if (type === 'sort' || type === 'type') {
        return name
    }

    const notesHtml = notes
        ? `<small class="text-muted d-block">${escapeHtml(notes)}</small>`
        : ''

    return `<div class="fw-semibold">${escapeHtml(name)}</div>${notesHtml}`
}

const renderMoney = (data, type) => {
    const value = Number(data || 0)

    if (type === 'sort' || type === 'type') {
        return value
    }

    return formatMoney(value)
}

const renderStatus = (data, type, row) => {
    if (type === 'filter') {
        return row?.status_label ?? data
    }

    if (type === 'sort' || type === 'type') {
        return row?.status ?? data
    }

    return `<span class="badge ${statusClass(row?.status)}">${escapeHtml(row?.status_label ?? '')}</span>`
}

const tableOptions = computed(() => ({
    ...configLanguaje,
    layout: {
        topStart: { pageLength: { menu: [10, 25, 50, 100] } },
        topEnd: 'search',
        bottomStart: 'info',
        bottomEnd: 'paging',
    },
    pageLength: 10,
    order: [[1, 'desc']],
    deferRender: true,
    language: {
        ...configLanguaje.language,
        sEmptyTable: 'No hay salidas creadas.',
        sZeroRecords: 'No se encontraron salidas con ese filtro.',
    },
    columns: [
        { data: 'name', title: 'Salida', render: renderOutingName },
        { data: 'departure_date', title: 'Fecha' },
        { data: 'participants_count_value', title: 'Deportistas' },
        { data: 'target_total', title: 'Meta', render: renderMoney },
        { data: 'raised_total', title: 'Recaudado', render: renderMoney },
        { data: 'pending_total', title: 'Pendiente', render: renderMoney },
        { data: 'status_label', title: 'Estado', render: renderStatus },
        {
            data: 'id',
            title: 'Acciones',
            searchable: false,
            orderable: false,
            render: '#actions',
        },
    ],
    columnDefs: [
        { responsivePriority: 1, targets: 0 },
        { responsivePriority: 2, targets: 7 },
        { targets: [2, 6, 7], className: 'dt-head-center dt-body-center' },
        { targets: [3, 4, 5], className: 'dt-head-right dt-body-right' },
        { targets: 7, width: '1%' },
    ],
}))

const fetchOutings = async () => {
    loading.value = true
    globalError.value = ''

    try {
        const { data } = await api.get('/api/v2/school-outings')
        outings.value = data.data || []
        tableVersion.value += 1
    } catch (error) {
        globalError.value = error.response?.data?.message || 'No fue posible cargar las salidas.'
    } finally {
        loading.value = false
    }
}

const openForm = (outing = null) => {
    resetForm()

    if (outing) {
        form.id = outing.id
        form.name = outing.name
        form.departure_date = outing.departure_date
        form.amount_per_player = Number(outing.amount_per_player || 0)
        form.notes = outing.notes || ''
    }

    showForm.value = true
}

const closeForm = () => {
    showForm.value = false
}

const goToOuting = (outing) => {
    router.push({ name: 'school-outings.show', params: { id: outing.id } })
}

const applyValidationErrors = (errors = {}) => {
    Object.entries(errors).forEach(([key, messages]) => {
        formErrors[key] = Array.isArray(messages) ? messages[0] : messages
    })
}

const saveOuting = async () => {
    resetErrors()
    saving.value = true

    try {
        const payload = {
            name: form.name,
            departure_date: form.departure_date,
            amount_per_player: Number(form.amount_per_player || 0),
            notes: form.notes || null,
        }
        const request = form.id
            ? api.put(`/api/v2/school-outings/${form.id}`, payload)
            : api.post('/api/v2/school-outings', payload)

        const { data } = await request
        window.showMessage?.(data.message || 'Salida guardada correctamente.')
        closeForm()
        await fetchOutings()
    } catch (error) {
        formMessage.value = error.response?.data?.message || 'No fue posible guardar la salida.'
        applyValidationErrors(error.response?.data?.errors || {})
    } finally {
        saving.value = false
    }
}

const changeStatus = async (outing, status) => {
    try {
        const { data } = await api.patch(`/api/v2/school-outings/${outing.id}/status`, { status })
        window.showMessage?.(data.message || 'Estado actualizado correctamente.')
        await fetchOutings()
    } catch (error) {
        globalError.value = error.response?.data?.message || 'No fue posible actualizar el estado.'
    }
}

onMounted(fetchOutings)
</script>
