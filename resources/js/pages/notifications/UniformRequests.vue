<template>
    <panel>
        <template #body>
            <div class="d-flex justify-content-end mb-3">
                <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                    <i class="fa-regular fa-circle-question me-2"></i>
                    Guia
                </button>
            </div>
            <p>Podrás encontrar las solicitudes pendientes de uniformes generadas desde la App GOLAPPLINK.</p>
            <span class="text-muted d-block mb-3">
                Al dar click en crear una factura las solicitudes pendientes se agregarán a esta, sin importar la
                solicitud seleccionada del estudiante y se aprobará si está incluida en la factura.
            </span>

            <div class="row mb-3" data-tour="uniform-requests-filter">
                <div class="col-md-4 col-lg-3">
                    <label class="form-label" for="uniformRequestTypeFilter">Tipo</label>
                    <select id="uniformRequestTypeFilter" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="UNIFORM">Uniforme</option>
                        <option value="BALL">Balón</option>
                        <option value="SOCKS">Medias</option>
                        <option value="SHOES">Guayos</option>
                        <option value="SHORTS">Pantaloneta</option>
                        <option value="JERSEY">Camisa</option>
                        <option value="OTHER">Otro</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive-md" data-tour="uniform-requests-table">
                <DatatableTemplate :options="options" id="uniform_requests_table" ref="uniformRequestsTable">
                    <template #thead>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Deportista</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Cantidad</th>
                                <th>Talla</th>
                                <th>Notas</th>
                                <th>Solicitado en</th>
                                <th>Crear Factura</th>
                            </tr>
                        </thead>
                    </template>
                </DatatableTemplate>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Facturación'" :current="'Solicitudes de Uniformes'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>

<script setup>
import { onMounted, useTemplateRef } from 'vue'
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import api from '@/utils/axios'
import dayjs from '@/utils/dayjs'
import configLanguaje from '@/utils/datatableUtils'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { usePageTitle } from '@/composables/use-meta'
import { uniformRequestsTutorial } from '@/tutorials/notifications'

usePageTitle('Solicitudes de Uniformes')

const uniformRequestsTable = useTemplateRef('uniformRequestsTable')
const tutorial = usePageTutorial(uniformRequestsTutorial)

const typeLabels = {
    UNIFORM: 'Uniforme',
    BALL: 'Balón',
    SOCKS: 'Medias',
    SHOES: 'Guayos',
    SHORTS: 'Pantaloneta',
    JERSEY: 'Camisa',
    OTHER: 'Otro (ver notas)',
}

const statusLabels = {
    PENDING: { text: 'Pendiente', className: 'badge badge-info' },
    APPROVED: { text: 'Aprobada', className: 'badge badge-success' },
    REJECTED: { text: 'Rechazada', className: 'badge badge-warning' },
    CANCELLED: { text: 'Cancelada', className: 'badge badge-danger' },
}

const options = {
    ...configLanguaje,
    lengthMenu: [[10, 30, 50, 70, 100], [10, 30, 50, 70, 100]],
    order: [[0, 'desc']],
    processing: true,
    serverSide: true,
    deferRender: true,
    ajax: async (data, callback) => {
        try {
            const response = await api.get('/api/v2/notifications/uniform-requests', { params: data })
            callback({
                data: response.data.data,
                recordsTotal: response.data.recordsTotal,
                recordsFiltered: response.data.recordsFiltered,
            })
        } catch {
            callback({ data: [], recordsTotal: 0, recordsFiltered: 0 })
        }
    },
    columnDefs: [
        { targets: [8], className: 'dt-body-center' },
    ],
    columns: [
        {
            data: 'id',
            name: 'id',
            searchable: false,
            orderable: true,
        },
        {
            data: 'full_names',
            name: 'full_names',
            searchable: true,
            orderable: true,
        },
        {
            data: 'type',
            searchable: true,
            orderable: false,
            render: (data) => `<span class="badge badge-info">${typeLabels[data] ?? typeLabels.OTHER}</span>`,
        },
        {
            data: 'status',
            searchable: false,
            orderable: true,
            render: (data) => {
                const status = statusLabels[data] ?? statusLabels.CANCELLED
                return `<span class="${status.className}">${status.text}</span>`
            },
        },
        {
            data: 'quantity',
            searchable: false,
            orderable: true,
        },
        {
            data: 'size',
            searchable: false,
            orderable: false,
        },
        {
            data: 'additional_notes',
            searchable: false,
            orderable: false,
            render: (data) => data || 'Sin notas',
        },
        {
            data: 'created_at',
            searchable: false,
            orderable: true,
            render: (data) => dayjs(data).format('DD-MM-YYYY'),
        },
        {
            data: 'inscription_id',
            searchable: false,
            orderable: false,
            render: (data) => `
                <a href="/facturas/crear/${data}" class="btn btn-success btn-sm" title="Crear factura">
                    <i class="fas fa-file-alt"></i>
                </a>
            `,
        },
    ],
}

onMounted(() => {
    const dt = uniformRequestsTable.value?.table?.dt
    const filter = document.getElementById('uniformRequestTypeFilter')

    if (dt && filter) {
        filter.addEventListener('change', function() {
            dt.column(2).search(this.value).draw()
        })
    }
})
</script>
