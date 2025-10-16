import configLanguaje from '@/utils/datatableUtils';
import { useTemplateRef, onMounted, ref } from 'vue';
import api from '@/utils/axios'
import { usePageTitle } from "@/composables/use-meta";

export default function useTrainingList() {

    const table = useTemplateRef('table')
    const selectedId = ref(null)

    const columns = [
        { data: 'id', width: '1%', title: 'ID', render: '#link', searchable: false, orderable: true },
        { data: 'name', title: 'Nombre', searchable: true, orderable: true },
        { data: 'stage', title: 'Escenario', name: 'roles.name', searchable: true, orderable: true },
        { data: 'category', title: 'Categorias', searchable: true, orderable: false },
        { data: 'members_count', title: 'Integrantes', searchable: false, orderable: true },
        { data: 'instructors_names', title: 'Instructor(es)', searchable: false, orderable: true },
        { data: 'days', title: 'Días', searchable: false, orderable: false },
    ]

    const options = {
        ...configLanguaje,
        lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
        columnDefs: [
            { responsivePriority: 2, targets: columns.length - 1 },
            {
                targets: ['_all'],
                className: 'dt-head-center dt-body-center', // Center align their headers
            }
        ],
        // scrollX: true,
        serverSide: true,
        processing: true,
        order: [[0, 'desc']],
        ajax: async (data, callback, settings) => {
            try {
                const response = await api.get('/api/v2/datatables/training_groups_enabled', { params: data }); // Adjust endpoint and method
                callback({
                    data: response.data.data, // Adjust based on your API response structure
                    recordsTotal: response.data.recordsTotal,
                    recordsFiltered: response.data.recordsFiltered,
                });
            } catch (error) {
                callback({ data: [], recordsTotal: 0, recordsFiltered: 0 });
            }
        },
        columns: columns
    }

    const onClickRow = async (e) => {
        const itemId = e.target.dataset.itemId
        if (!itemId) {
            return
        }
        e.preventDefault()

        selectedId.value = itemId
    }

    const reloadTable = () => {
        selectedId.value = null
        if (table.value) {
            let dt = table.value.table.dt;
            dt.ajax.reload(null, false)
        }
    }

    const onCancel = () => {
        selectedId.value = null
    }

    onMounted(() => {
        usePageTitle('G. Entrenamiento')
    })

    return { table, options, selectedId, onClickRow, reloadTable, onCancel }
}