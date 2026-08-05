import configLanguaje from '@/utils/datatableUtils';
import { onMounted, ref } from 'vue';
import api from '@/utils/axios'
import { usePageTitle } from "@/composables/use-meta";
import { useRecoverableDataTable } from '@/composables/useRecoverableDataTable'

export default function useCompetitionGList(table) {

    const selectedId = ref(null)
    const {
        globalError,
        tableKey,
        clearError,
        handleError,
        reloadTable: reloadDataTable,
    } = useRecoverableDataTable(table, 'No fue posible cargar los grupos de competencia.', 'competition_table')

    const columns = [
        { data: 'id', width: '1%', title: 'ID', render:'#link',searchable: false, orderable: true },
        { data: 'name', title: 'Nombre', searchable: true, orderable: true },
        { data: 'category', title: 'Categoría', searchable: true, orderable: true },
        { data: 'year', title: 'Año', searchable: true, orderable: false },
        { data: 'tournament.name', title: 'Torneo', searchable: false, orderable: false },
        { data: 'professor.name', title: 'Instructor', searchable: false, orderable: false },
    ];

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
        pipeline: { pages: 5 },
        processing: true,
        order: [[0, 'desc']],
        ajax: async (data, callback, settings) => {
            try {
                const response = await api.get('/api/v2/datatables/competition_groups_enabled', { params: data }); // Adjust endpoint and method
                clearError()
                callback({
                    data: response.data.data, // Adjust based on your API response structure
                    recordsTotal: response.data.recordsTotal,
                    recordsFiltered: response.data.recordsFiltered,
                });
            } catch (error) {
                handleError(error)
                callback({ data: [], recordsTotal: 0, recordsFiltered: 0 });
            }
        },
        columns: columns
    };

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
        reloadDataTable()
    }

    const onCancel = () => {
        selectedId.value = null
    }

    onMounted(() => {
        usePageTitle('Grupos de competencia')
    })

    return { table, tableKey, options, selectedId, globalError, onClickRow, reloadTable, onCancel }
}
