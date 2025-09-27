import configLanguaje from '@/utils/datatableUtils';
import { useTemplateRef } from 'vue';
import { useAuthUser } from '@/store/auth-user'
import { useRouter } from 'vue-router';
import api from '@/utils/axios'
import { name } from 'dayjs/locale/es';

export default function usePlayerList() {

    const store = useAuthUser()
    const router = useRouter()
    const table = useTemplateRef('');
    const columns = [
        { data: 'photo_url', title: '', width: '1%', render: '#photo', searchable: false, orderable: false},
        { data: 'unique_code', title: 'Código', width: '5%', render: '#link', searchable: true, orderable: true},
        { data: 'identification_document', title: 'Doc', width: '5%', searchable: true, orderable: true},
        { data: 'date_birth', title: 'Fecha Nac', width: '5%', render: '#date', searchable: false, orderable: false},
        { data: 'full_names', title: 'Nombres', name: 'last_names', width: '15%', searchable: true, orderable: true},
        { data: 'created_at', title: 'Registro', render: '#date', width: '5%', searchable: true, orderable: true},
    ];
    const options = {
        ...configLanguaje,
        lengthMenu: [[15, 30, 50, 70, 100], [15, 30, 50, 70, 100]],
        columnDefs: [
            { responsivePriority: 1, targets: columns.length - 1 },
            {
                targets: ['_all'],
                className: 'dt-head-center dt-body-center', // Center align their headers
            },
        ],
        scrollX: true,
        serverSide: true,
        processing: true,
        order: [[1, 'desc']],
        ajax: async (data, callback, settings) => {
            try {
                const response = await api.get('/api/v2/datatables/players_enabled', { params: data }); // Adjust endpoint and method
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

    const resolveRouteFromClick = (e) => {
        const itemId = e.target.dataset.itemId
        if (!itemId) {
            return
        }
        e.preventDefault()
        router.push('/deportistas/' + itemId);
    }

    return { options, table, resolveRouteFromClick };
}
