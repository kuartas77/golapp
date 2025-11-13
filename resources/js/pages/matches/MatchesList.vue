<template>

    <panel>
        <template #body>
            <a class="btn btn-block btn-primary" href="javascript:void(0);" @click="openGroupSelection()">
                Crear Competencia
            </a>
            <DatatableTemplate :id="'matches_table'" :options="options" ref="matches_table" class="table-hover">
            </DatatableTemplate>
        </template>
    </panel>

    <breadcrumb :parent="'Plataforma'" :current="'Competencias'" />


</template>
<script setup>
import { usePageTitle } from "@/composables/use-meta"
import configLanguaje from '@/utils/datatableUtils';
import { useTemplateRef, onMounted, ref } from 'vue';
import api from '@/utils/axios'
import { useRouter } from 'vue-router'
import { useSetting } from '@/store/settings-store';

const settings = useSetting()
const matches_table = useTemplateRef('matches_table')
const router = useRouter()

const columns = [
    { data: 'tournament_name', title: 'Torneo', name: 'tournaments.name', render: (data) => `<small>${data}</small>`, searchable: true },
    { data: 'competition_group_name', title: 'G. Competencia', name: 'competition_groups.name', render: (data) => `<small>${data}</small>`, searchable: true },
    { data: 'date', title: 'Fecha', render: (data) => `<small>${data}</small>`, searchable: true },
    { data: 'hour', title: 'Hora', render: (data) => `<small>${data}</small>`, searchable: false, orderable: false },
    { data: 'rival_name', title: 'Rival', render: (data) => `<small>${data}</small>`, searchable: true },
    { data: 'final_score', title: 'Marcador', render: (data) => `<small>${data.soccer} - ${data.rival}</small>`, searchable: false, orderable: false },
    {
        data: 'id', title: '#', render: (data, type, row, meta) => {
            return `<div class="btn-group">
                <a class="btn btn-sm btn-success print-btn" href="${row.url_show}" target="_blank"><i href="${row.url_show}" class="fa-solid fa-file-pdf fa-lg"></i></a>
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
        { targets: [5], width: '6%' },
        { targets: [6], width: '9%' },
        { targets: ['_all'], className: 'dt-head-center dt-body-center' },
    ],
    layout: {
        topStart: { pageLength: { menu: [10, 20, 30, 50, 100] } },
        topEnd: { search: { placeholder: 'Torneo / G. competencia' } },
        bottomStart: 'info',
        // bottomEnd: { paging: { buttons: 4 } }
    },
    serverSide: true,
    processing: true,
    order: [[2, 'desc']],
    columns: columns,
    ajax: async (data, callback, settings) => {
        try {
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
            button.onclick = (event) => {
                const id = event.target.dataset.itemId

                Swal.fire({
                    title: "¿Eliminar?",
                    text: "¡No podrás revertir esto!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "¡Sí, bórralo!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        // TODO: Implement your delete logic here
                    }
                });

            };
        });
    }
}

const openGroupSelection = () => {
    Swal.fire({
        title: 'Selecciona Un Grupo De Competencia',
        type: "info",
        input: 'select',
        inputOptions: Object.fromEntries(settings.competition_groups.map(item => [item.id, item.name])),
        inputPlaceholder: 'Selecciona...',
        allowOutsideClick: true,
        allowEscapeKey: true,
        inputValidator: function (value) {
            return new Promise(function (resolve) {
                if (value !== '') {
                    resolve();
                } else {
                    resolve('Necesitas Seleccionar Uno.');
                }
            });
        }
    }).then(function (result) {
        if (result?.value !== undefined) {
            router.push({ name: 'matches-create', params: { grupo_competencia: result.value } })
        }
    });
}


onMounted(() => {
    usePageTitle('Competencias')
})
</script>