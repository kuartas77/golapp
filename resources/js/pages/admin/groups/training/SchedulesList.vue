<template>
    <div class="layout-px-spacing training-schedules-page">
        <div class="row layout-top-spacing justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-10 col-12 layout-spacing">
                <div class="panel training-schedules-panel">
                    <div class="panel-heading">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                            <div>
                                <h5 class="mb-1">Horarios</h5>
                                <small class="text-muted">
                                    Selector múltiple de los grupos de entrenamiento.
                                </small>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <router-link :to="{ name: 'training-groups' }" class="btn btn-outline-secondary btn-sm">
                                    Volver
                                </router-link>
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm"
                                    :disabled="isSaving"
                                    @click="openCreateModal"
                                >
                                    Agregar horario
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="panel-body">
                        <div v-if="listError" class="alert alert-danger py-2" role="alert">
                            {{ listError }}
                        </div>

                        <div v-if="isLoading" class="py-4 text-center">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <p class="text-muted mb-0">Cargando horarios...</p>
                        </div>

                        <template v-else>
                            <div class="table-responsive-md schedules-table">
                                <DatatableTemplate
                                    id="training_schedules_table"
                                    :options="tableOptions"
                                    :data="items"
                                >
                                    <template #thead>
                                        <thead class="align-middle">
                                            <tr>
                                                <th>Horario</th>
                                                <th class="text-end">Acciones</th>
                                            </tr>
                                        </thead>
                                    </template>

                                    <template #actions="props">
                                        <div class="d-inline-flex gap-2">
                                            <button
                                                type="button"
                                                class="btn btn-outline-primary btn-sm"
                                                :disabled="isDeletingId === props.rowData.id"
                                                @click="openEditModal(props.rowData)"
                                            >
                                                Editar
                                            </button>
                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-sm"
                                                :disabled="isDeletingId === props.rowData.id"
                                                @click="confirmDelete(props.rowData)"
                                            >
                                                <span
                                                    v-if="isDeletingId === props.rowData.id"
                                                    class="spinner-border spinner-border-sm me-1"
                                                    role="status"
                                                ></span>
                                                Eliminar
                                            </button>
                                        </div>
                                    </template>
                                </DatatableTemplate>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div ref="modalElement" id="modal-schedule" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <Form
                ref="scheduleForm"
                :validation-schema="schema"
                :initial-values="buildDefaultFormValues()"
                @submit="submitForm"
            >
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title mb-0">
                                {{ isEditMode ? 'Editar horario' : 'Nuevo horario' }}
                            </h5>
                            <small class="text-muted">Este horario aparecerá en los grupos de entrenamiento.</small>
                        </div>
                        <button
                            type="button"
                            class="btn-close"
                            aria-label="Cerrar"
                            :disabled="isSaving"
                            @click="closeModal"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info py-2">
                            Usa el mismo formato de hora del sistema. Ejemplo: <strong>07:00 AM</strong>.
                        </div>

                        <div v-if="globalError" class="alert alert-danger" role="alert">
                            {{ globalError }}
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="schedule-start">Inicio</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    <Field name="schedule_start" v-slot="{ field, errorMessage, meta }">
                                        <flat-pickr
                                            v-bind="field"
                                            v-model="field.value"
                                            :config="flatpickrConfigHour"
                                            id="schedule-start"
                                            class="form-control form-control-sm flatpickr"
                                            :class="{ 'is-invalid': meta.touched && errorMessage }"
                                            :disabled="isSaving"
                                            placeholder="07:00 AM"
                                        />
                                    </Field>
                                </div>
                                <ErrorMessage name="schedule_start" class="invalid-feedback d-block" />
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="schedule-end">Fin</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    <Field name="schedule_end" v-slot="{ field, errorMessage, meta }">
                                        <flat-pickr
                                            v-bind="field"
                                            v-model="field.value"
                                            :config="flatpickrConfigHour"
                                            id="schedule-end"
                                            class="form-control form-control-sm flatpickr"
                                            :class="{ 'is-invalid': meta.touched && errorMessage }"
                                            :disabled="isSaving"
                                            placeholder="08:00 AM"
                                        />
                                    </Field>
                                </div>
                                <ErrorMessage name="schedule_end" class="invalid-feedback d-block" />
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" :disabled="isSaving" @click="closeModal">
                            Cerrar
                        </button>
                        <button type="submit" class="btn btn-primary" :disabled="isSaving">
                            <span v-if="isSaving" class="spinner-border spinner-border-sm me-1" role="status"></span>
                            {{ isEditMode ? 'Guardar cambios' : 'Guardar horario' }}
                        </button>
                    </div>
                </div>
            </Form>
        </div>
    </div>

    <breadcrumb :parent="'Configuración'" :current="'Horarios'" />
</template>

<script setup>
import "@/assets/sass/forms/custom-flatpickr.css"
import 'flatpickr/dist/flatpickr.css'
import { computed, getCurrentInstance, onBeforeUnmount, onMounted, ref, useTemplateRef } from 'vue'
import { ErrorMessage, Field, Form } from 'vee-validate'
import flatPickr from 'vue-flatpickr-component'
import * as yup from 'yup'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import api from '@/utils/axios'
import configLanguaje from '@/utils/datatableUtils'
import { usePageTitle } from '@/composables/use-meta'

const items = ref([])
const isLoading = ref(true)
const listError = ref('')
const isSaving = ref(false)
const isDeletingId = ref(null)
const editingId = ref(null)
const modalElement = ref(null)
const globalError = ref('')
const scheduleForm = useTemplateRef('scheduleForm')
const { proxy } = getCurrentInstance()

const isEditMode = computed(() => editingId.value !== null)

let modalInstance = null

const flatpickrConfigHour = {
    static: true,
    enableTime: true,
    noCalendar: true,
    dateFormat: "h:i K",
}

const schema = yup.object().shape({
    schedule_start: yup.string().matches(
        /^((1[0-2]|[1-9]):([0-5][0-9]))\s(AM|PM)$/i,
        'La hora debe estar en formato de 12 horas. (ejemplo: 7:00 AM)'
    ).required(),
    schedule_end: yup.string().matches(
        /^((1[0-2]|[1-9]):([0-5][0-9]))\s(AM|PM)$/i,
        'La hora debe estar en formato de 12 horas. (ejemplo: 8:00 AM)'
    ).required(),
})

const buildDefaultFormValues = () => ({
    schedule_start: '',
    schedule_end: '',
})

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;')

const timeToMinutes = (value) => {
    const match = String(value ?? '').trim().match(/^(\d{1,2}):([0-5]\d)\s*(AM|PM)$/i)

    if (!match) {
        return Number.MAX_SAFE_INTEGER
    }

    const hour = Number(match[1])
    const minutes = Number(match[2])
    const meridiem = match[3].toUpperCase()
    const hour24 = meridiem === 'AM'
        ? (hour === 12 ? 0 : hour)
        : (hour === 12 ? 12 : hour + 12)

    return (hour24 * 60) + minutes
}

const scheduleSortValue = (row) => {
    const start = timeToMinutes(row?.schedule_start)
    const end = timeToMinutes(row?.schedule_end)

    return (start * 1440) + end
}

const scheduleLabel = (row) => {
    if (row?.schedule) {
        return row.schedule
    }

    return [row?.schedule_start, row?.schedule_end].filter(Boolean).join(' a ')
}

const renderSchedule = (data, type, row) => {
    if (type === 'sort' || type === 'type') {
        return scheduleSortValue(row)
    }

    if (type === 'filter') {
        return scheduleLabel(row)
    }

    return `<span class="fw-semibold">${escapeHtml(scheduleLabel(row))}</span>`
}

const tableOptions = computed(() => ({
    ...configLanguaje,
    dom: 'litp',
    lengthMenu: [[10, 30, 50, 70, 100], [10, 30, 50, 70, 100]],
    pageLength: 10,
    order: [[0, 'asc']],
    deferRender: true,
    language: {
        ...configLanguaje.language,
        sEmptyTable: 'Todavía no hay horarios configurados para esta escuela.',
        sZeroRecords: 'No se encontraron horarios con ese filtro',
    },
    columns: [
        {
            data: 'schedule',
            title: 'Horario',
            render: renderSchedule,
        },
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
        { responsivePriority: 2, targets: 1 },
        { targets: 1, className: 'dt-head-right dt-body-right', width: '1%' },
    ],
}))

const resetFormState = (values = buildDefaultFormValues()) => {
    globalError.value = ''
    scheduleForm.value?.resetForm({ values })
}

const fetchSchedules = async () => {
    isLoading.value = true
    listError.value = ''

    try {
        const { data } = await api.get('/api/v2/admin/schedules')
        items.value = Array.isArray(data) ? data : []
    } catch (error) {
        listError.value = error.response?.data?.message || 'No fue posible cargar los horarios.'
        items.value = []
    } finally {
        isLoading.value = false
    }
}

const openCreateModal = () => {
    editingId.value = null
    resetFormState()
    modalInstance?.show()
}

const openEditModal = (item) => {
    editingId.value = item.id
    resetFormState({
        schedule_start: item.schedule_start ?? '',
        schedule_end: item.schedule_end ?? '',
    })
    modalInstance?.show()
}

const closeModal = () => {
    modalInstance?.hide()
}

const submitForm = async (values, actions) => {
    globalError.value = ''
    isSaving.value = true

    try {
        const endpoint = isEditMode.value
            ? `/api/v2/admin/schedules/${editingId.value}`
            : '/api/v2/admin/schedules'
        const request = isEditMode.value
            ? api.put(endpoint, values)
            : api.post(endpoint, values)

        const { data } = await request

        showMessage(data.message || (isEditMode.value ? 'Horario actualizado correctamente.' : 'Horario creado correctamente.'))
        closeModal()
        await fetchSchedules()
    } catch (error) {
        const fallbackMessage = error.response?.data?.message || 'No fue posible guardar el horario.'

        proxy.$handleBackendErrors(
            error,
            actions.setErrors,
            (message) => (globalError.value = message || fallbackMessage)
        )

        if (error.response?.status !== 422) {
            showMessage(fallbackMessage, 'error')
        }
    } finally {
        isSaving.value = false
    }
}

const confirmDelete = async (item) => {
    const result = await window.Swal.fire({
        title: '¿Eliminar horario?',
        text: `Se eliminará ${item.schedule} del catálogo disponible.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    })

    if (!result.isConfirmed) {
        return
    }

    isDeletingId.value = item.id

    try {
        await api.delete(`/api/v2/admin/schedules/${item.id}`)
        showMessage('Horario eliminado correctamente.')
        await fetchSchedules()
    } catch (error) {
        showMessage(error.response?.data?.message || 'No fue posible eliminar el horario.', 'error')
    } finally {
        isDeletingId.value = null
    }
}

const handleModalHidden = () => {
    editingId.value = null
    resetFormState()
}

onMounted(() => {
    usePageTitle('Horarios')
    fetchSchedules()

    modalInstance = new window.bootstrap.Modal(modalElement.value)
    modalElement.value?.addEventListener('hidden.bs.modal', handleModalHidden)
})

onBeforeUnmount(() => {
    modalElement.value?.removeEventListener('hidden.bs.modal', handleModalHidden)
    modalInstance?.hide()
})
</script>

<style scoped>
.training-schedules-panel .panel-heading {
    padding: 16px 18px 10px;
}

.training-schedules-panel .panel-body {
    padding: 14px 18px 18px;
}

.schedules-table :deep(.dataTables_wrapper .row:first-child) {
    row-gap: 0.5rem;
    margin-bottom: 0.35rem;
}

.schedules-table :deep(table.dataTable) {
    margin-bottom: 0.5rem !important;
}

.schedules-table :deep(table.dataTable td),
.schedules-table :deep(table.dataTable th) {
    padding: 0.55rem 0.75rem;
}
</style>
