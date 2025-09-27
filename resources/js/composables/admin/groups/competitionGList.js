import configLanguaje from '@/utils/datatableUtils';
import { useTemplateRef, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthUser } from '@/store/auth-user'
import api from '@/utils/axios'

export default function useCompetitionGList() {

    const store = useAuthUser()
    // const router = useRouter()
    const table = useTemplateRef('table');

    const columns = [
        { data: 'id', width: '1%', title: 'ID', searchable: false, orderable: true },
        { data: 'name', title: 'Nombre', searchable: true, orderable: true },
        { data: 'category', title: 'Categoria', searchable: true, orderable: true },
        { data: 'year', title: 'Año', searchable: true, orderable: false },
        { data: 'tournament.name', title: 'Torneo', searchable: false, orderable: false },
        { data: 'professor.name', title: 'Instructor', searchable: false, orderable: false },
    ];

    const options = {
        ...configLanguaje,
        lengthMenu: [[15, 30, 50, 70, 100], [15, 30, 50, 70, 100]],
        columnDefs: [
            { responsivePriority: 2, targets: columns.length - 1 },
            {
                targets: ['_all'],
                className: 'dt-head-center dt-body-center', // Center align their headers
            }
        ],
        scrollX: true,
        serverSide: true,
        processing: true,
        order: [[0, 'desc']],
        ajax: async (data, callback, settings) => {
            try {
                const response = await api.get('/api/v2/datatables/competition_groups_enabled', { params: data }); // Adjust endpoint and method
                callback({
                    data: response.data.data, // Adjust based on your API response structure
                    recordsTotal: response.data.recordsTotal,
                    recordsFiltered: response.data.recordsFiltered,
                });
            } catch (error) {
                callback({ data: [], recordsTotal: 0, recordsFiltered: 0 });
                console.error('Error fetching data:', error);
            }
        },
        columns: columns
    };

    return { table, options }
}