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
        {
            data: 'id', title: 'Acciones', render: (data, type, row, meta) => {
                return `<div class="btn-group">
                    <a class="btn btn-sm btn-success" href="${row.url_impression}" target="_blank" title="Imprimir inscripción"><i href="${row.url_show}" class="fa-solid fa-file-pdf fa-width-auto"></i></a>
                    <button class="btn btn-sm btn-warning" data-item-id="${row.id}" data-type="invoice" title="Crear factura"><i data-item-id="${row.id}" class="fa fa-file-invoice fa-width-auto" data-type="delete"></i></button>
                    <button class="btn btn-sm btn-info" data-item-id="${row.unique_code}" data-type="edit" title="Modificar Inscripción"><i data-item-id="${row.unique_code}" class="fa fa-edit fa-width-auto" data-type="edit"></i></button>
                    <button class="btn btn-sm btn-danger" data-item-id="${row.url_destroy}" data-type="delete" title="Eliminar inscripción"><i data-item-id="${row.url_destroy}" class="fa fa-trash fa-width-auto" data-type="delete"></i></button>


                </div>`
            }, searchable: false, orderable: false
        }
    ];

    const options = {
        ...configLanguaje,
        lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
        columnDefs: [
            { responsivePriority: 1, targets: columns.length - 1 },
            { targets: [1, 2, 3],  width: '9%' },
            { targets: [4, 6, 7, 8], width: '1%', className: 'dt-head-center dt-body-center' },
            { targets: [5], width: '35%'},
            { targets: [0, 1, 2, 3], className: 'dt-head-center dt-body-center' },
        ],
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
        const type = e.target.dataset.type
        const itemId = e.target.dataset.itemId
        if (!itemId || !type) {
            return
        }
        e.preventDefault()
        switch (type) {
            case 'edit':
                uniqueCodeSelected.value = itemId
                break;
            case 'delete':
                // router.push({ name: 'invoices.show', params: { id: itemId } })
                break;

            default:
                break;
        }
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
