<template>
    <panel>
        <template #header>
            <div class="row g-3 align-items-center">
                <div class="col-md-auto">
                    <button type="button" class="btn btn-primary" @click="openCreate">
                        Nueva sesión
                    </button>
                </div>
                <div class="col">
                    <p class="mb-0">
                        Administra las sesiones de entrenamiento, consulta el histórico y edita sus ejercicios por pasos.
                    </p>
                </div>
            </div>
        </template>

        <template #body>
            <DatatableTemplate
                ref="table"
                id="training-sessions-table"
                :options="options"
            >
                <template #actions="props">
                    <div class="d-flex justify-content-center gap-1">
                        <a
                            :href="props.rowData.export_pdf_url"
                            target="_blank"
                            class="btn btn-info btn-sm"
                            title="Exportar PDF"
                        >
                            <i class="fa-solid fa-file-pdf fa-width-auto me-2" aria-hidden="true"></i>
                        </a>

                        <button
                            type="button"
                            class="btn btn-warning btn-sm"
                            title="Editar sesión"
                            @click="openEdit(props.rowData.id)"
                        >
                            <i class="fa fa-edit fa-width-auto me-2" aria-hidden="true"></i>
                        </button>

                        <button
                            v-if="canDelete"
                            type="button"
                            class="btn btn-danger btn-sm"
                            title="Eliminar sesión"
                            @click="confirmDelete(props.rowData)"
                        >
                            <i class="fa fa-trash fa-width-auto me-2" aria-hidden="true"></i>
                        </button>
                    </div>
                </template>
            </DatatableTemplate>
        </template>
    </panel>

    <TrainingSessionModal
        :show="isModalOpen"
        :session-id="selectedId"
        @updated="reloadTable"
        @cancel="closeModal"
    />

    <breadcrumb :parent="'Plataforma'" :current="'Sesiones de entrenamiento'" />
</template>

<script setup>
import { computed, ref, useTemplateRef } from 'vue'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import { usePageTitle } from '@/composables/use-meta'
import TrainingSessionModal from './TrainingSessionModal.vue'
import api from '@/utils/axios'
import configLanguaje from '@/utils/datatableUtils'
import { useAuthUser } from '@/store/auth-user'

usePageTitle('Sesiones de entrenamiento')

const auth = useAuthUser()
const table = useTemplateRef('table')
const selectedId = ref(null)
const isModalOpen = ref(false)
const canDelete = computed(() => auth.hasAnyRole(['super-admin', 'school']))

const emptyDataTableResponse = (draw = 0) => ({
    draw,
    data: [],
    recordsTotal: 0,
    recordsFiltered: 0,
})

const columns = [
    { data: 'creator_name', title: 'Creado por', name: 'creator_name' },
    { data: 'training_group_name', title: 'Grupo entrenamiento', name: 'training_group_name' },
    { data: 'training_ground', title: 'Lugar', name: 'training_ground' },
    { data: 'period', title: 'Periodo', name: 'period' },
    { data: 'session', title: 'Sesión', name: 'session' },
    { data: 'date', title: 'Fecha', name: 'date' },
    { data: 'hour', title: 'Hora', name: 'hour' },
    { data: 'tasks_count', title: 'N° Ejercicios', name: 'tasks_count', searchable: false },
    { data: 'created_at', title: 'Creado en', name: 'created_at' },
    { data: 'id', title: 'Opciones', searchable: false, orderable: false, render: '#actions' },
]

const options = {
    ...configLanguaje,
    lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
    pageLength: 10,
    processing: true,
    serverSide: true,
    pipeline: { pages: 5 },
    deferRender: true,
    searchDelay: 400,
    order: [[8, 'desc']],
    ajax: async (data, callback) => {
        try {
            const response = await api.get('/api/v2/datatables/training_sessions_enabled', {
                params: data,
            })

            callback({
                draw: data.draw,
                data: response.data.data ?? [],
                recordsTotal: response.data.recordsTotal ?? 0,
                recordsFiltered: response.data.recordsFiltered ?? 0,
            })
        } catch {
            callback(emptyDataTableResponse(data.draw))
        }
    },
    columns,
    columnDefs: [
        { responsivePriority: 1, targets: columns.length - 1 },
        {
            targets: '_all',
            className: 'dt-head-center dt-body-center',
        },
    ],
}

const openCreate = () => {
    selectedId.value = null
    isModalOpen.value = true
}

const openEdit = (id) => {
    selectedId.value = id
    isModalOpen.value = true
}

const closeModal = () => {
    isModalOpen.value = false
    selectedId.value = null
}

const reloadTable = () => {
    closeModal()

    const dt = table.value?.table?.dt

    if (dt) {
        dt.clearPipeline()
        dt.ajax.reload(null, false)
    }
}

const notify = (message, type = 'success') => {
    window.Swal?.fire({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 4000,
        icon: type,
        title: message,
    })
}

const errorMessage = (error, fallback) => {
    return error.response?.data?.message
        || Object.values(error.response?.data?.errors || {})?.flat()?.[0]
        || fallback
}

const confirmDelete = async (session) => {
    const result = await window.Swal.fire({
        title: `Eliminar sesión #${session.id}`,
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
    })

    if (!result.isConfirmed) {
        return
    }

    try {
        await api.delete(`/api/v2/training-sessions/${session.id}`)
        notify('Sesión eliminada correctamente.')
        reloadTable()
    } catch (error) {
        notify(errorMessage(error, 'No fue posible eliminar la sesión.'), 'error')
    }
}
</script>
