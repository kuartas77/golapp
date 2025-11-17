import configLanguaje from '@/utils/datatableUtils';
import { useTemplateRef, onMounted, ref } from 'vue';
import api from '@/utils/axios'

export default function useInscriptionConfig() {
    const inscription_table = useTemplateRef('inscription_table')
    const uniqueCodeSelected = ref(null)

    const columns = [
        { data: 'player.photo_url', width: '1%', render: '#photo', searchable: false, orderable: false },
        { data: 'unique_code', name:'inscriptions.unique_code', render: '#link', searchable: true },
        { data: 'training_group.name', name: 'inscriptions.training_group_id', orderable: false, searchable: true },
        { data: 'player.category', name: 'inscriptions.category', orderable: false, searchable: true },
        { data: 'player.gender', name: 'player.gender', orderable: false, searchable: false },
        { data: 'player.full_names', render: '#inscription', name: 'player.last_names', orderable: false, searchable: true  },
        { data: 'eps_certificate', render: (data) => `<span class="badge badge-warning">`+(data ? 'Sí':'No')+`</span>`, orderable: false, searchable: true },
        { data: 'created_at', render: '#date', searchable: false },
    ];

    const options = {
        ...configLanguaje,
        lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
        columnDefs: [
            { responsivePriority: 1, targets: columns.length - 1 },
            {
                targets: [1, 2, 3, 6],
                width: '9%'
            },
            {
                targets: [4],
                width: '1%'
            },
            {
                targets: [5],
                width: '35%'
            },
            {
                targets: [0,1, 2, 3, 4, 6, 7],
                className: 'dt-head-center dt-body-center', // Center align their headers
            },
            // { searchable: false, targets: [0,4, 5]},
            // { orderable: false, targets: [0,4, 5]},
        ],
        // scrollX: true,
        // dom: 'lfitp',//lftip
        serverSide: true,
        processing: true,
        order: [[1, 'desc']],
        ajax: async (data, callback, settings) => {
            try {
                const response = await api.get('/api/v2/datatables/inscriptions_enabled', { params: data }); // Adjust endpoint and method
                callback({
                    data: response.data.data, // Adjust based on your API response structure
                    recordsTotal: response.data.recordsTotal,
                    recordsFiltered: response.data.recordsFiltered,
                });
            } catch (error) {
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
        uniqueCodeSelected.value = itemId
    }

    const onCancelModal = () => {
        uniqueCodeSelected.value = null
    }

    const onSuccessModal = () => {
        uniqueCodeSelected.value = null
        if (inscription_table.value) {
            let dt = inscription_table.value.table.dt;
            dt.ajax.reload(null, false)
        }
    }

    onMounted(() => {
        if (inscription_table.value) {
            let dt = inscription_table.value.table.dt;
            const selectGroups = document.querySelector('thead select[placeholder="Grupos"]');
            if (selectGroups) {
                selectGroups.addEventListener('change', function () {
                    return dt.column(2).search(this.value).draw()
                });
            }
            const selectCategories = document.querySelector('thead select[placeholder="Categorias"]');
            if (selectCategories) {
                selectCategories.addEventListener('change', function () {
                    return dt.column(3).search(this.value).draw()
                });
            }
        }
    });

    return { options, inscription_table, uniqueCodeSelected, resolveRouteFromClick, onCancelModal, onSuccessModal };
}
