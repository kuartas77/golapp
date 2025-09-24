import configLanguaje from '@/utils/datatableUtils';
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthUser } from '@/store/auth-user'
import api from '@/utils/axios'

export default function useInscriptionList() {
    const store = useAuthUser()
    const router = useRouter()
    const inscription_table = ref()

    const columns = [
        { data: 'player.photo_url', width: '1%', render: '#photo', searchable: false },
        { data: 'unique_code', render: '#link', searchable: true },
        { data: 'player.identification_document', searchable: true },
        { data: 'training_group.name', name: 'training_group_id', orderable: false, searchable: true },
        { data: 'player.category', name: 'inscriptions.category', orderable: false, searchable: true },
        { data: 'player.full_names', name: 'player.last_names', searchable: true, orderable: false },
        { data: 'created_at', render: '#date', searchable: false },
    ];

    const options = {
        ...configLanguaje,
        lengthMenu: [[15, 30, 50, 70, 100], [15, 30, 50, 70, 100]],
        columnDefs: [
            { responsivePriority: 1, targets: columns.length - 1 },
            {
                targets: [1, 2, 3, 4, 6],
                width: '5%'
            },
            {
                targets: ['_all'],
                className: 'dt-head-center dt-body-center', // Center align their headers
            },
            // { searchable: false, targets: [0,4, 5]},
            // { orderable: false, targets: [0,4, 5]},
        ],
        scrollX: true,
        serverSide: true,
        processing: true,
        order: [[1, 'desc']],
        ajax: async (data, callback, settings) => {
            try {
                const response = await api.get('/api/v2/datatables/inscriptions_enabled', data); // Adjust endpoint and method
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
    };

    const resolveRouteFromClick = (e) => {
        const itemId = e.target.dataset.itemId
        if (!itemId) {
            return
        }
        e.preventDefault()
        router.push('/inscripciones/' + itemId);
    }

    onMounted(() => {
        if (inscription_table.value) {
            let dt = inscription_table.value.dt;
            const selectGroups = document.querySelector('thead select[placeholder="Grupos"]');
            if (selectGroups) {
                selectGroups.addEventListener('change', function () {
                    return dt.column(3).search(this.value).draw()
                });
            }
            const selectCategories = document.querySelector('thead select[placeholder="Categorias"]');
            if (selectCategories) {
                selectCategories.addEventListener('change', function () {
                    return dt.column(4).search(this.value).draw()
                });
            }
        }
    });

    return { columns, options, inscription_table, resolveRouteFromClick };
}
