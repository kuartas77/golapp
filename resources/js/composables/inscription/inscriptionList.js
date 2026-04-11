import configLanguaje from '@/utils/datatableUtils';
import { nextTick, onBeforeUnmount, onMounted, ref, useTemplateRef } from 'vue';
import api from '@/utils/axios'
import { useRouter } from 'vue-router'

export default function useInscriptionConfig() {
    const router = useRouter()
    const inscription_table = useTemplateRef('inscription_table')
    const selectedInscriptionId = ref(null)
    const disableUrlSelected = ref(null)
    let selectGroupsElement = null
    let selectCategoriesElement = null
    let handleGroupsChange = null
    let handleCategoriesChange = null

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

    const reloadTable = () => {
        if (inscription_table.value) {
            const dt = inscription_table.value.table.dt
            dt.ajax.reload(null, false)
        }
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

    onMounted(async () => {
        await nextTick()

        if (inscription_table.value) {
            const dt = inscription_table.value.table.dt

            selectGroupsElement = document.querySelector('thead select[placeholder="Grupos"]')
            if (selectGroupsElement) {
                handleGroupsChange = function () {
                    return dt.column(2).search(this.value).draw()
                }
                selectGroupsElement.addEventListener('change', handleGroupsChange)
            }

            selectCategoriesElement = document.querySelector('thead select[placeholder="Categorias"]')
            if (selectCategoriesElement) {
                handleCategoriesChange = function () {
                    return dt.column(3).search(this.value).draw()
                }
                selectCategoriesElement.addEventListener('change', handleCategoriesChange)
            }
        }
    })

    onBeforeUnmount(() => {
        if (selectGroupsElement && handleGroupsChange) {
            selectGroupsElement.removeEventListener('change', handleGroupsChange)
        }

        if (selectCategoriesElement && handleCategoriesChange) {
            selectCategoriesElement.removeEventListener('change', handleCategoriesChange)
        }
    })

    return { options, inscription_table, selectedInscriptionId, resolveRouteFromClick, onCancelModal, onSuccessModal };
}
