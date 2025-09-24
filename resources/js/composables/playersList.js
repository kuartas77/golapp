import configLanguaje from '@/utils/datatableUtils';
import { ref } from 'vue';
import { useAuthUser } from '@/store/auth-user'
import { useRouter } from 'vue-router';

export default function usePlayerList() {

    const store = useAuthUser()
    const router = useRouter()
    const table = ref();
    const columns = [
        { data: 'photo_url', title: '', width: '1%', render: '#photo'},
        { data: 'unique_code', title: 'Código', width: '5%', render: '#link'},
        { data: 'identification_document', title: 'Doc', width: '5%' },
        { data: 'date_birth', title: 'Fecha Nac', width: '5%', render: '#date'},
        { data: 'full_names', title: 'Nombres', width: '15%' },
        { data: 'created_at', title: 'Registro', render: '#date'},
    ];
    const options = {
        ...configLanguaje,
        lengthMenu: [[15, 30, 50, 70, 100], [15, 30, 50, 70, 100]],
        columnDefs: [
            { responsivePriority: 1, targets: columns.length - 1 },
            {
                targets: [1, 2, 4, 5],
                width: '5%'
            },
            {
                targets: [0, 1, 2, 3, 4, 5],
                className: 'dt-head-center dt-body-center', // Center align their headers
            },
            { searchable: false, targets: [0,4, 5]},
            { orderable: false, targets: [0,4, 5]},
        ],
        scrollX: true,
        serverSide: true,
        processing: true,
        order: [[1, 'desc']],
    };

    const token = store.getToken;

    const ajaxConfig = {
        url: '/api/v2/datatables/players_enabled',
        type: 'GET',
        beforeSend: function (request) {
            request.setRequestHeader("Authorization", `Bearer ${token}`);
        }
    };

    const resolveRouteFromClick = (e) => {
        const itemId = e.target.dataset.itemId
        if (!itemId) {
            return
        }
        e.preventDefault()
        router.push('/deportistas/' + itemId);
    }

    return { columns, options, table, ajaxConfig, resolveRouteFromClick };
}
