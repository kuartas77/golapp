import configLanguaje from '@/utils/datatableUtils';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

export default function useUsersList() {

    // const router = useRouter()
    const table = ref();

    const columns = [
        { data: 'id', width: '1%', title: 'ID', searchable: false, orderable: true },
        { data: 'name',  title: 'Nombres', searchable: true, orderable: true },
        { data: 'role_name',  title: 'Perfil', name : 'roles.name', searchable: true, orderable: true },
        { data: 'email',  title: 'Correo', searchable: true, orderable: false },
        { data: 'created_at',  title: 'Registro', name:'users.created_at', render: '#date', searchable: false, orderable: false},
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

    // onMounted(() => {})

    return { table, columns, options }
}