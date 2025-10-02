import configLanguaje from '@/utils/datatableUtils';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
// import { useAuthUser } from '@/store/auth-user'
import api from '@/utils/axios'

export default function useSchoolList() {
    const table = ref(null)

    const columns = [
        { data: 'logo_file', width: '1%', render: '#photo', searchable: false, orderable: false},
        { data: 'is_enable', width: '1%', title: 'Habilitada?', render: '#check', searchable: false, orderable: true },
        { data: 'name', title: 'Nombre', searchable: true, orderable: true },
        { data: 'agent', title: 'Representante', name: 'agent', searchable: true, orderable: true },
        { data: 'email', title: 'Correo', searchable: false, orderable: false },
        { data: 'phone', title: 'Teléfono', searchable: false, orderable: false },
        { data: 'created_at', title: 'Registro', name: 'created_at', render: '#date', searchable: false, orderable: true },
    ]

    const ajaxConfig = async (data, callback, settings) => {
        try {
            const response = await api.get('/api/v2/datatables/schools', { params: data }); // Adjust endpoint and method
            callback({
                data: response.data.data, // Adjust based on your API response structure
                recordsTotal: response.data.recordsTotal,
                recordsFiltered: response.data.recordsFiltered,
            });
        } catch (error) {
            callback({ data: [], recordsTotal: 0, recordsFiltered: 0 });
        }
    }

    const options = {
        ...configLanguaje,
        lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
        columnDefs: [
            { responsivePriority: 2, targets: columns.length - 5 },
            {
                targets: ['_all'],
                className: 'dt-head-center dt-body-center', // Center align their headers
            }
        ],
        // scrollX: true,
        serverSide: true,
        processing: true,
        order: [[6, 'desc']],
        ajax: ajaxConfig,
        columns: columns
    };

    return { table, options }
}