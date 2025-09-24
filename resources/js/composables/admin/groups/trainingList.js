import configLanguaje from '@/utils/datatableUtils';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthUser } from '@/store/auth-user'

export default function useTrainingList() {

    const store = useAuthUser()
    // const router = useRouter()
    const table = ref();

    const columns = [
        { data: 'id', width: '1%', title: 'ID', searchable: false, orderable: true },
        { data: 'name',  title: 'Nombre', searchable: true, orderable: true },
        { data: 'stage',  title: 'Escenario', name : 'roles.name', searchable: true, orderable: true },
        { data: 'category',  title: 'Categorias', searchable: true, orderable: false },
        { data: 'members_count',  title: 'Integrantes', searchable: false, orderable: true },
        { data: 'instructors_names',  title: 'Instructor(es)', searchable: false, orderable: true },
        { data: 'days',  title: 'Días', searchable: false, orderable: false},
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
        url: '/api/v2/datatables/training_groups_enabled',
        type: 'GET',
        beforeSend: function (request) {
            request.setRequestHeader("Authorization", `Bearer ${token}`);
        }
    };

    return { table, columns, options, ajaxConfig }
}