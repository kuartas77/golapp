import configLanguaje from '@/utils/datatableUtils';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthUser } from '@/store/auth-user'

export default function useCompetitionGList() {

    const store = useAuthUser()
    // const router = useRouter()
    const table = ref();

    const columns = [
        { data: 'id', width: '1%', title: 'ID', searchable: false, orderable: true },
        { data: 'name',  title: 'Nombre', searchable: true, orderable: true },
        { data: 'category',  title: 'Categoria', searchable: true, orderable: true },
        { data: 'year',  title: 'Año', searchable: true, orderable: false },
        { data: 'tournament.name',  title: 'Torneo', searchable: false, orderable: false },
        { data: 'professor.name',  title: 'Instructor', searchable: false, orderable: false },
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
    };

    const token = store.getToken;

    const ajaxConfig = {
        url: '/api/datatables/competition_groups_enabled',
        type: 'GET',
        beforeSend: function (request) {
            request.setRequestHeader("Authorization", `Bearer ${token}`);
        }
    };

    return { table, columns, options, ajaxConfig }
}