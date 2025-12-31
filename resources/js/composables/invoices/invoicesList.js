import configLanguaje from '@/utils/datatableUtils';
import { useTemplateRef, onMounted } from 'vue';
import api from '@/utils/axios'
import { usePageTitle } from "@/composables/use-meta";
import { useRouter } from 'vue-router'
import dayjs from '@/utils/dayjs';

export default function useInvoicesList() {
    const router = useRouter()
    const invoives_table = useTemplateRef('invoives_table')
    const columns = [
        { data: 'invoice_number', name: 'invoice_number', searchable: true, orderable: false },
        { data: 'student_name', name: 'student_name', searchable: true, orderable: false },
        { data: 'training_group.name', searchable: false, orderable: false },
        { data: 'total_amount', searchable: false, orderable: false, render: (data, type, row) => `${moneyFormat(data)}` },
        { data: 'paid_amount', searchable: false, orderable: false, render: (data, type, row) => `${moneyFormat(data)}` },
        {
            data: 'status', render: function (data, type, row) {
                let badge = '<span class="badge badge-secondary">Cancelada</span>'
                if (data === 'paid') {
                    badge = '<span class="badge badge-success">Pagada</span>'
                } else if (data === 'partial') {
                    badge = '<span class="badge badge-warning">Parcial</span>'
                } else if (data === 'pending') {
                    badge = '<span class="badge badge-danger">Pendiente</span>'
                }
                return badge
            }, searchable: true, orderable: false
        },
        { data: 'created_at', searchable: true, render: (data, type, row) => dayjs(data).format('YYYY-M-D') },
        {
            data: 'id', searchable: false, orderable: false, render: function (data, type, row) {
                const buttonEye = `<button class="btn btn-sm btn-info" data-item-id="${row.id}" data-type="show"><i class="fa fa-eye fa-lg" data-type="show" data-item-id="${row.id}"></i></button>`
                const buttonPrint = `<a href="${row.url_print}" target="_blank" class="btn btn-success btn-sm"><i class="fa fa-print fa-lg" aria-hidden="true"></i></a>`
                let buttonDelete = ''

                if (['pending', 'partial'].includes(row.status)) {
                    buttonDelete = `<button class="btn btn-danger btn-sm" data-item-id="${row.id}" data-type="delete"><i class="fa fa-trash fa-lg" data-type="delete" data-item-id="${row.id}"></i></button>`
                }

                return `<div class="btn-group">${buttonEye} ${buttonPrint} ${buttonDelete}</div>`
            }
        },
    ]

    const options = {
        ...configLanguaje,
        lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
        columnDefs: [
            { responsivePriority: 1, targets: columns.length - 1 },
            { targets: [3, 4], className: 'dt-body-right' },
            { targets: [5], className: 'dt-body-center' },
            { targets: [7],  width: '1%' }
        ],
        serverSide: true,
        processing: true,
        order: [[6, 'desc']],
        ajax: async (data, callback, settings) => {
            try {
                const response = await api.get('/api/v2/invoices', { params: data }); // Adjust endpoint and method
                callback({
                    data: response.data.data, // Adjust based on your API response structure
                    recordsTotal: response.data.recordsTotal,
                    recordsFiltered: response.data.recordsFiltered,
                });
            } catch (error) {
                callback({ data: [], recordsTotal: 0, recordsFiltered: 0 });
            }
        },
        columns: columns,
        footerCallback: function(row, data, start, end, display) {
            const api = this.api();
            // Helper function to remove formatting (like currency symbols) and convert to a number
            const intVal = function(i) {
                return typeof i === 'string' ?
                    i.replace(/[\$,]/g, '') * 1 :
                    typeof i === 'number' ?
                    i :
                    0;
            };
            // Calculate total for column 4 (e.g., Quantity)
            const total = api
                .column(3)
                .data()
                .reduce((a, b) => intVal(a) + intVal(b), 0);

            const payment = api
                .column(4)
                .data()
                .reduce((a, b) => intVal(a) + intVal(b), 0);

            api.column(3).footer().innerHTML = `${moneyFormat(total)}`;
            api.column(4).footer().innerHTML = `${moneyFormat(payment)}`;
        }
    };

    const onClickRow = (e) => {
        const type = e.target.dataset.type
        const itemId = e.target.dataset.itemId
        if (!itemId || !type) {
            return
        }
        e.preventDefault()
        switch (type) {
            case 'show':
                router.push({ name: 'invoices.show', params: { id: itemId } })
                break;
            case 'delete':
                router.push({ name: 'invoices.show', params: { id: itemId } })
                break;

            default:
                break;
        }
    }

    const reloadTable = () => {
        if (invoives_table.value) {
            let dt = invoives_table.value.table.dt;
            dt.ajax.reload(null, false)
        }
    }

    onMounted(() => {
        usePageTitle('Facturas')

        if (invoives_table.value) {
            let dt = invoives_table.value.table.dt;
            const filterStatus = document.getElementById('filterStatus');
            if (filterStatus) {
                filterStatus.addEventListener('change', function () {
                    return dt.column(5).search(this.value).draw()
                });
            }
            const filterDate = document.getElementById('filterDate');
            if (filterDate) {
                filterDate.addEventListener('change', function () {
                    return dt.column(6).search(this.value).draw()
                });
            }
        }
    })

    return { options, invoives_table, onClickRow, reloadTable }
}