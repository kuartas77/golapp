<template>
    <panel>
        <template #lateral/>
        <template #body>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <a class="btn btn-block btn-primary" href="javascript:void(0);" @click="openGroupSelection()" data-tour="matches-list-actions">
                    Crear Competencia
                </a>
                <div class="d-flex align-items-center gap-2">
                    <select v-model="statusFilter" class="form-select form-select-sm" aria-label="Filtrar por estado" @change="reloadMatchesTable">
                        <option value="">Todos los estados</option>
                        <option value="scheduled">Programados</option>
                        <option value="played">Jugados</option>
                    </select>
                    <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                        <i class="fa-regular fa-circle-question me-2"></i>
                        Guia
                    </button>
                </div>
            </div>
            <div data-tour="matches-list-table">
                <DatatableTemplate :id="'matches_table'" :options="options" ref="matches_table" class="table-hover"/>
            </div>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Competencias'" />
    <PageTutorialOverlay :tutorial="tutorial" />
</template>
<script setup>
import DatatableTemplate from '@/components/general/DatatableTemplate.vue'
import { usePageTitle } from "@/composables/use-meta"
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import configLanguaje from '@/utils/datatableUtils';
import { usePageTutorial } from '@/composables/usePageTutorial'
import { useTemplateRef, onMounted, ref } from 'vue';
import api from '@/utils/axios'
import { useRouter } from 'vue-router'
import { useSetting } from '@/store/settings-store';
import { matchesListTutorial } from '@/tutorials/matches'

const settings = useSetting()
const matches_table = useTemplateRef('matches_table')
const router = useRouter()
const tutorial = usePageTutorial(matchesListTutorial)
const statusFilter = ref('')

const columns = [
    { data: 'tournament_name', title: 'Torneo', name: 'tournaments.name', render: (data) => `<small>${data}</small>`, searchable: true },
    { data: 'competition_group_name', title: 'G. Competencia', name: 'competition_groups.name', render: (data) => `<small>${data}</small>`, searchable: true },
    { data: 'date', title: 'Fecha', render: (data) => `<small>${data}</small>`, searchable: true },
    { data: 'hour', title: 'Hora', render: (data) => `<small>${data}</small>`, searchable: false, orderable: false },
    { data: 'rival_name', title: 'Rival', render: (data) => `<small>${data}</small>`, searchable: true },
    {
        data: 'status_label', title: 'Estado', name: 'games.status',
        render: (data, type, row) => `<span class="badge bg-${row.status === 'played' ? 'success' : 'warning'}">${data}</span>`,
        searchable: false,
    },
    { data: 'final_score', title: 'Marcador', render: (data, type, row) => row.status === 'played' && data ? `<small>${data.soccer} - ${data.rival}</small>` : '<small class="text-muted">Pendiente</small>', searchable: false, orderable: false },
    {
        data: 'id', title: '#', render: (data, type, row, meta) => {
            const pdfAction = row.status === 'played'
                ? `<a class="btn btn-sm btn-success print-btn" href="${row.url_show}" target="_blank"><i href="${row.url_show}" class="fa-solid fa-file-pdf fa-lg"></i></a>`
                : ''

            return `<div class="btn-group">
                ${pdfAction}
                <button class="btn btn-sm btn-info edit-btn" data-item-id="${data}"><i data-item-id="${data}" class="fa fa-edit fa-lg"></i></button>
                <button class="btn btn-sm btn-danger delete-btn" data-item-id="${data}"><i data-item-id="${data}" class="fa fa-trash fa-lg"></i></button>

            </div>`
        }, searchable: false, orderable: false
    }
];

const options = {
    ...configLanguaje,
    columnDefs: [
        { responsivePriority: 1, targets: columns.length - 1 },
        { targets: [6], width: '6%' },
        { targets: [7], width: '9%' },
        { targets: ['_all'], className: 'dt-head-center dt-body-center' },
    ],
    layout: {
        topStart: { pageLength: { menu: [10, 20, 30, 50, 100] } },
        topEnd: { search: { placeholder: 'Torneo / G. competencia' } },
        bottomStart: 'info',
        // bottomEnd: { paging: { buttons: 4 } }
    },
    serverSide: true,
    pipeline: { pages: 5 },
    processing: true,
    order: [[2, 'desc']],
    columns: columns,
    ajax: async (data, callback, settings) => {
        try {
            data.status = statusFilter.value || undefined
            const response = await api.get('/api/v2/datatables/matches', { params: data }); // Adjust endpoint and method
            callback({
                data: response.data.data, // Adjust based on your API response structure
                recordsTotal: response.data.recordsTotal,
                recordsFiltered: response.data.recordsFiltered,
            });
        } catch (error) {
            callback({ data: [], recordsTotal: 0, recordsFiltered: 0 });
        }
    },
    drawCallback: () => {
        const tableElement = matches_table.value.$el

        tableElement.querySelectorAll('.edit-btn').forEach(button => {
            button.onclick = (event) => {
                const id = event.target.dataset.itemId
                // Implement your edit logic here
                router.push({ name: 'matches-edit', params: { id: id } })
            };
        });

        tableElement.querySelectorAll('.delete-btn').forEach(button => {
            button.onclick = async (event) => {
                const id = event.target.dataset.itemId

                const result = await Swal.fire({
                    title: "¿Eliminar?",
                    text: "¡No podrás revertir esto!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "¡Sí, bórralo!"
                });

                if (!result.isConfirmed) {
                    return
                }

                try {
                    const response = await api.delete(`/api/v2/matches/${id}`)

                    if (!response.data?.success) {
                        showMessage('No fue posible eliminar la competencia.', 'error')
                        return
                    }

                    showMessage('Competencia eliminada correctamente.')
                    reloadMatchesTable()
                } catch (error) {
                    showMessage(error.response?.data?.message || 'No fue posible eliminar la competencia.', 'error')
                }
            };
        });
    }
}

const reloadMatchesTable = () => {
    const dt = matches_table.value?.table?.dt

    if (!dt) {
        return
    }

    dt.clearPipeline()
    dt.ajax.reload(null, false)
}

const openGroupSelection = () => {
    Swal.fire({
        backdrop: true,
        title: 'Selecciona Un Grupo De Competencia',
        icon: "info",
        input: 'select',
        inputOptions: Object.fromEntries(settings.competition_groups.map(item => [item.id, item.name])),
        inputPlaceholder: 'Selecciona...',
        allowEscapeKey: true,
        showLoaderOnConfirm: true,
        confirmButtonText: 'Crear competencia',
        allowOutsideClick: () => !Swal.isLoading(),
        inputValidator: function (value) {
            return new Promise(function (resolve) {
                if (value !== '') {
                    resolve();
                } else {
                    resolve('Necesitas Seleccionar Uno.');
                }
            });
        },
        preConfirm: async (value) => {
            try {
                const response = await api.get('/api/v2/matches/0', {
                    params: { competition_group: value }
                })

                if (!response.data?.skills_controls?.length) {
                    Swal.showValidationMessage('No se puede crear la competencia porque el grupo seleccionado no tiene integrantes.')
                    return false
                }

                return value
            } catch (error) {
                Swal.showValidationMessage(error.response?.data?.message || 'No fue posible validar el grupo de competencia.')
                return false
            }
        }
    }).then(function (result) {
        if (result.isConfirmed && result?.value !== undefined) {
            router.push({ name: 'matches-create', params: { grupo_competencia: result.value } })
        }
    });
}


onMounted(() => {
    usePageTitle('Competencias')
})
</script>
