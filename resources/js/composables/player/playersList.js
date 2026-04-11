import configLanguaje from '@/utils/datatableUtils';
import { useTemplateRef, ref, onMounted, getCurrentInstance } from 'vue';
import api from '@/utils/axios'
import { usePageTitle } from "@/composables/use-meta";
import { useRouter } from 'vue-router'

export default function usePlayerList() {

    const { proxy } = getCurrentInstance()
    const router = useRouter()
    const table = useTemplateRef('table');
    const globalError = ref(null)
    const hasShownLoadErrorAlert = ref(false)

    const columns = [
        { data: 'photo_url', title: '', width: '1%', render: '#photo', searchable: false, orderable: false},
        { data: 'unique_code', title: 'Código', width: '5%', render: '#link', searchable: true, orderable: true},
        { data: 'identification_document', title: 'Doc', width: '5%', searchable: true, orderable: true},
        { data: 'date_birth', title: 'Fecha Nac', width: '5%', render: '#date', searchable: false, orderable: false},
        { data: 'full_names', title: 'Nombres', name: 'full_names', width: '15%', searchable: true, orderable: true},
        { data: 'created_at', title: 'Registro', render: '#date', width: '5%', searchable: true, orderable: true},
    ];

    const clearLoadError = () => {
        globalError.value = null
        hasShownLoadErrorAlert.value = false
    }

    const showLoadErrorAlert = async () => {
        if (hasShownLoadErrorAlert.value || !proxy?.$swal) {
            return
        }

        hasShownLoadErrorAlert.value = true

        await proxy.$swal.fire({
            icon: 'error',
            title: 'No fue posible cargar el listado',
            text: 'Intenta nuevamente en unos segundos.',
            confirmButtonText: 'Entendido',
        })
    }

    const options = {
        ...configLanguaje,
        lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
        columnDefs: [
            { responsivePriority: 1, targets: columns.length - 1 },
            {
                targets: ['_all'],
                className: 'dt-head-center dt-body-center', // Center align their headers
            },
        ],
        // scrollX: true,
        serverSide: true,
        processing: true,
        order: [[1, 'desc']],
        ajax: async (data, callback, settings) => {
            try {
                const response = await api.get('/api/v2/datatables/players_enabled', { params: data });
                clearLoadError()

                callback({
                    data: response.data.data,
                    recordsTotal: response.data.recordsTotal,
                    recordsFiltered: response.data.recordsFiltered,
                });
            } catch (error) {
                globalError.value = error.response?.data?.message || 'No fue posible cargar el listado de deportistas.'
                callback({ data: [], recordsTotal: 0, recordsFiltered: 0 });
                showLoadErrorAlert()
            }
        },
        columns: columns
    };

    const onClickRow = (e) => {
        const itemId = e.target.dataset.itemId
        if (!itemId) {
            return
        }
        e.preventDefault()
        router.push({ name: 'player-detail', params: { unique_code: itemId } })
    }

    const reloadTable = () => {
        clearLoadError()

        if (table.value?.table?.dt) {
            table.value.table.dt.ajax.reload(null, false)
        }
    }

    onMounted(() => {
        usePageTitle('Deportistas')
    })

    return { options, table, onClickRow, reloadTable, globalError };
}
