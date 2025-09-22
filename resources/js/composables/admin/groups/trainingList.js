import configLanguaje from '@/utils/datatableUtils';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

export default function useTrainingList() {

    // const router = useRouter()
    const table = ref();

    const columns = [
        { data: 'id', width: '1%', title: 'ID', searchable: false, orderable: true },
        { data: 'name',  title: 'Nombre', searchable: true, orderable: true },
        { data: 'stage',  title: 'Escenario', name : 'roles.name', searchable: true, orderable: true },
        { data: 'category',  title: 'Categorias', searchable: true, orderable: false },
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

    // onMounted(() => {})

    return { table, columns, options }
}