import configLanguaje from '@/utils/datatableUtils';
import { useTemplateRef, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthUser } from '@/store/auth-user'
import api from '@/utils/axios'

export default function useTrainingList() {

    const store = useAuthUser()
    // const router = useRouter()
    const table = useTemplateRef('table');

    const columns = [
        { data: 'id', width: '1%', title: 'ID', searchable: false, orderable: true },
        { data: 'name', title: 'Nombre', searchable: true, orderable: true },
        { data: 'stage', title: 'Escenario', name: 'roles.name', searchable: true, orderable: true },
        { data: 'category', title: 'Categorias', searchable: true, orderable: false },
        { data: 'members_count', title: 'Integrantes', searchable: false, orderable: true },
        { data: 'instructors_names', title: 'Instructor(es)', searchable: false, orderable: true },
        { data: 'days', title: 'Días', searchable: false, orderable: false },
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
                const response = await api.get('/api/v2/datatables/training_groups_enabled', { params: data }); // Adjust endpoint and method
                callback({
                    data: response.data.data, // Adjust based on your API response structure
                    recordsTotal: response.data.recordsTotal,
                    recordsFiltered: response.data.recordsFiltered,
                });
            } catch (error) {
                console.error('Error fetching data:', error);
                callback({ data: [], recordsTotal: 0, recordsFiltered: 0 });
            }
        },
        columns: columns
    };

    return { table, options }
}