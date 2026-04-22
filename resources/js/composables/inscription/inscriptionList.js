import configLanguaje from '@/utils/datatableUtils';
import { computed, nextTick, ref, useTemplateRef } from 'vue';
import api from '@/utils/axios'
import { useRouter } from 'vue-router'

export default function useInscriptionConfig(selectedYear, canManageInscriptions) {
    const router = useRouter()
    const inscription_table = useTemplateRef('inscription_table')
    const selectedInscriptionId = ref(null)
    const selectedAttendanceQrCode = ref(null)
    const disableUrlSelected = ref(null)
    const currentYear = new Date().getFullYear()
    const canManageSelectedYear = computed(() => {
        const year = Number(selectedYear?.value ?? currentYear)

        return Boolean(canManageInscriptions?.value) && year >= currentYear
    })

    const columns = [
        { data: 'player.photo_url', width: '1%', render: '#photo', searchable: false, orderable: false },
        { data: 'unique_code', name:'inscriptions.unique_code', searchable: true },
        { data: 'training_group.name', name: 'inscriptions.training_group_id', orderable: false, searchable: true },
        { data: 'player.category', name: 'inscriptions.category', orderable: false, searchable: true },
        { data: 'player.gender', name: 'player.gender', orderable: false, searchable: false },
        { data: 'player.full_names', render: '#inscription', name: 'player.last_names', orderable: false, searchable: true  },
        { data: 'eps_certificate', render: (data) => `<span class="badge badge-warning">`+(data ? 'Sí':'No')+`</span>`, orderable: false, searchable: true },
        { data: 'start_date', name: 'inscriptions.start_date', render: '#date', searchable: false },
        {
            data: 'id',
            title: 'Acciones',
            render: (data, type, row, meta) => {
                const manageActions = canManageSelectedYear.value
                    ? `
                        <li>
                            <button
                                class="dropdown-item"
                                data-item-id="${row.id}"
                                data-type="invoice"
                                title="Crear factura"
                                type="button"
                            >
                                <i
                                    data-item-id="${row.id}"
                                    class="fa fa-file-invoice fa-width-auto me-2"
                                    data-type="invoice"
                                ></i>
                                Crear factura
                            </button>
                        </li>

                        <li>
                            <button
                                class="dropdown-item"
                                data-item-id="${row.id}"
                                data-type="edit"
                                title="Modificar Inscripción"
                                type="button"
                            >
                                <i
                                    data-item-id="${row.id}"
                                    class="fa fa-edit fa-width-auto me-2"
                                    data-type="edit"
                                ></i>
                                Modificar inscripción
                            </button>
                        </li>

                        <li><hr class="dropdown-divider"></li>

                        <li>
                            <button
                                class="dropdown-item text-danger"
                                data-item-id="${row.url_destroy}"
                                data-type="disable"
                                title="Eliminar inscripción"
                                type="button"
                            >
                                <i
                                    data-item-id="${row.url_destroy}"
                                    class="fa fa-trash fa-width-auto me-2"
                                    data-type="disable"
                                ></i>
                                Eliminar inscripción
                            </button>
                        </li>
                    `
                    : ''

                return `
                <div class="dropdown">
                    <button
                        class="btn btn-sm btn-primary dropdown-toggle"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                    >
                        Acciones
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <button
                                class="dropdown-item"
                                data-item-id="${row.unique_code}"
                                data-type="attendance-qr"
                                title="Ver QR de asistencia"
                                type="button"
                            >
                                <i
                                    data-item-id="${row.unique_code}"
                                    class="fa-solid fa-qrcode fa-width-auto me-2"
                                    data-type="attendance-qr"
                                ></i>
                                QR asistencia
                            </button>
                        </li>

                        <li>
                            <a
                                class="dropdown-item"
                                href="${row.url_impression}"
                                target="_blank"
                                title="Imprimir inscripción"
                            >
                                <i href="${row.url_show}" class="fa-solid fa-file-pdf fa-width-auto me-2"></i>
                                Imprimir inscripción
                            </a>
                        </li>

                        ${manageActions}
                    </ul>
                </div>
            `
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
                const response = await api.get('/api/v2/datatables/inscriptions_enabled', {
                    params: {
                        ...data,
                        inscription_year: selectedYear?.value ?? currentYear,
                    },
                });
                callback({
                    data: response.data.data,
                    recordsTotal: response.data.recordsTotal,
                    recordsFiltered: response.data.recordsFiltered,
                });
            } catch (error) {
                callback({ data: [], recordsTotal: 0, recordsFiltered: 0 });
            }
        },
        columns: columns
    };

    const getDataTable = () => inscription_table.value?.table?.dt ?? null

    const reloadTable = async () => {
        await nextTick()

        const dt = getDataTable()
        const tableNode = typeof dt?.table === 'function' ? dt.table().node() : null

        if (!dt || !tableNode?.isConnected) {
            return
        }

        dt.ajax.reload(null, false)
    }

    const applyColumnFilter = (columnIndex, value = '') => {
        const dt = getDataTable()

        if (!dt) {
            return
        }

        dt.column(columnIndex).search(value).draw()
    }

    const onGroupFilterChange = (event) => {
        applyColumnFilter(2, event?.target?.value ?? '')
    }

    const onCategoryFilterChange = (event) => {
        applyColumnFilter(3, event?.target?.value ?? '')
    }

    const resolveRouteFromClick = (e) => {
        const type = e.target.dataset.type
        const itemId = e.target.dataset.itemId
        if (!itemId || !type) {
            return
        }
        e.preventDefault()
        switch (type) {
            case 'edit':
                selectedInscriptionId.value = Number(itemId)
                break;
            case 'attendance-qr':
                selectedAttendanceQrCode.value = String(itemId)
                break;
            case 'invoice':
                router.push({ name: 'invoices.create', params: { inscription: itemId } })
                break;
            case 'disable':
                disableUrlSelected.value = itemId
                confirmDisable()
                break;
            default:
                Swal.fire("Acción no implementada");
                break;
        }
    }

    const onCancelModal = () => {
        selectedInscriptionId.value = null
        disableUrlSelected.value = null
    }

    const onAttendanceQrModalToggle = (isOpen) => {
        if (!isOpen) {
            selectedAttendanceQrCode.value = null
        }
    }

    const onSuccessModal = () => {
        selectedInscriptionId.value = null
        disableUrlSelected.value = null
        reloadTable()
    }

    const confirmDisable = async () => {
        Swal.fire({
            title: "¿Retirar inscripción?",
            text: "¡Despues de esto sí lo necesitas, sólo inscribelo de nuevo!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "¡Sí, retirarlo!"
        }).then((result) => {
            if (result.isConfirmed) {
                api.delete(disableUrlSelected.value)
                    .then(() => {
                        disableUrlSelected.value = null
                        reloadTable()
                        Swal.fire({
                            title: "Inscripción retirada",
                            text: "La inscripción fue eliminada exitosamente.",
                            icon: "success",
                            confirmButtonColor: "#3085d6",
                        })
                    })
                    .catch(() => {
                        Swal.fire({
                            title: "Error",
                            text: "No se pudo eliminar la inscripción.",
                            icon: "error",
                            confirmButtonColor: "#3085d6",
                        })
                    })
            }
        });
    }

    return {
        options,
        inscription_table,
        reloadTable,
        selectedInscriptionId,
        selectedAttendanceQrCode,
        onGroupFilterChange,
        onCategoryFilterChange,
        resolveRouteFromClick,
        onAttendanceQrModalToggle,
        onCancelModal,
        onSuccessModal,
    };
}
