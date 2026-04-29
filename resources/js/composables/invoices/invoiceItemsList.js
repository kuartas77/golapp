import configLanguaje from '@/utils/datatableUtils';
import api from '@/utils/axios';
import dayjs from '@/utils/dayjs';
import { usePageTitle } from '@/composables/use-meta';
import { onMounted, useTemplateRef } from 'vue';

const TYPE_LABELS = {
    monthly: 'Mensualidad',
    enrollment: 'Inscripción',
    additional: 'Item',
};

const PAYMENT_METHOD_LABELS = {
    cash: 'Efectivo',
    card: 'Tarjeta',
    transfer: 'Transferencia',
    check: 'Cheque',
    other: 'Otro',
};

const PAYMENT_METHOD_OPTIONS = [
    { value: '', label: 'Método Pago' },
    { value: 'cash', label: 'Efectivo' },
    { value: 'card', label: 'Tarjeta' },
    { value: 'transfer', label: 'Transferencia' },
    { value: 'check', label: 'Cheque' },
    { value: 'other', label: 'Otro' },
];

const STATUS_OPTIONS = [
    { value: '', label: 'Estado' },
    { value: '1', label: 'Pagado' },
    { value: '0', label: 'Pendiente' },
];

function replaceHeaderContent(header, element) {
    header.replaceChildren(element);
}

function createTextFilter(column, placeholder, type = 'search') {
    const input = document.createElement('input');
    input.type = type;
    input.placeholder = placeholder;
    input.className = 'form-control form-control-sm';

    const handler = function () {
        if (column.search() !== this.value) {
            column.search(this.value).draw();
        }
    };

    if (type === 'date') {
        input.addEventListener('change', handler);
    } else {
        input.addEventListener('input', handler);
        input.addEventListener('search', handler);
    }

    replaceHeaderContent(column.header(), input);
}

function createSelectFilter(column, options) {
    const select = document.createElement('select');
    select.className = 'form-select form-select-sm';

    for (const optionData of options) {
        const option = document.createElement('option');
        option.value = optionData.value;
        option.textContent = optionData.label;
        select.append(option);
    }

    select.addEventListener('change', function () {
        if (column.search() !== this.value) {
            column.search(this.value).draw();
        }
    });

    replaceHeaderContent(column.header(), select);
}

export default function useInvoiceItemsList() {
    const invoiceItemsTable = useTemplateRef('invoice_items_table');

    const columns = [
        {
            data: 'invoice.invoice_number',
            name: 'invoice.invoice_number',
            searchable: true,
            orderable: false,
        },
        {
            data: 'created_at',
            name: 'invoice_items.created_at',
            searchable: true,
            orderable: false,
            render: data => dayjs(data).format('DD/MM/YYYY'),
        },
        {
            data: 'invoice.student_name',
            name: 'invoice.student_name',
            searchable: true,
            orderable: false,
        },
        {
            data: 'type',
            name: 'type',
            searchable: false,
            orderable: false,
            render: data => TYPE_LABELS[data] ?? 'Item',
        },
        {
            data: 'description',
            name: 'description',
            searchable: true,
            orderable: false,
        },
        {
            data: 'payment_method',
            name: 'payment_method',
            searchable: true,
            orderable: false,
            render: data => {
                if (!data) {
                    return '<span class="text-muted">-</span>';
                }

                return `<span class="badge">${PAYMENT_METHOD_LABELS[data] ?? data}</span>`;
            },
        },
        {
            data: 'quantity',
            name: 'quantity',
            searchable: false,
            orderable: false,
        },
        {
            data: 'unit_price',
            name: 'unit_price',
            searchable: false,
            orderable: false,
            render: data => moneyFormat(Number(data) || 0),
        },
        {
            data: 'total',
            name: 'total',
            searchable: false,
            orderable: false,
            render: data => moneyFormat(Number(data) || 0),
        },
        {
            data: 'is_paid',
            name: 'is_paid',
            searchable: true,
            orderable: false,
            render: data => {
                if (data === true || data === 1 || data === '1') {
                    return '<span class="badge badge-success">Pagada</span>';
                }

                return '<span class="badge badge-warning">Pendiente</span>';
            },
        },
    ];

    const options = {
        ...configLanguaje,
        dom: 'litp',
        lengthMenu: [[10, 30, 50, 70, 100], [10, 30, 50, 70, 100]],
        order: [[0, 'desc']],
        serverSide: true,
        processing: true,
        deferRender: true,
        ajax: async (data, callback) => {
            try {
                const response = await api.get('/api/v2/invoices/items/invoices', { params: data });

                callback({
                    draw: data.draw,
                    data: response.data.data ?? [],
                    recordsTotal: response.data.recordsTotal ?? 0,
                    recordsFiltered: response.data.recordsFiltered ?? 0,
                });
            } catch (error) {
                callback({
                    draw: data.draw,
                    data: [],
                    recordsTotal: 0,
                    recordsFiltered: 0,
                });
            }
        },
        columnDefs: [
            { targets: [7, 8], className: 'dt-body-right' },
            { targets: [3, 9], className: 'dt-body-center dt-head-center' },
        ],
        columns,
        initComplete: function () {
            const api = this.api();

            createTextFilter(api.column(0), 'Factura');
            createTextFilter(api.column(1), 'Fecha', 'date');
            createTextFilter(api.column(2), 'Deportista');
            createTextFilter(api.column(4), 'Descripción');
            createSelectFilter(api.column(5), PAYMENT_METHOD_OPTIONS);
            createSelectFilter(api.column(9), STATUS_OPTIONS);

            api.columns.adjust();
        },
        footerCallback: function () {
            const api = this.api();
            const currentRows = api.rows({ page: 'current' }).data().toArray();

            const unitPriceTotal = currentRows.reduce((sum, row) => sum + (Number(row.unit_price) || 0), 0);
            const itemsTotal = currentRows.reduce((sum, row) => sum + (Number(row.total) || 0), 0);

            api.column(7).footer().innerHTML = moneyFormat(unitPriceTotal);
            api.column(8).footer().innerHTML = moneyFormat(itemsTotal);
        },
    };

    onMounted(() => {
        usePageTitle('Items Facturas');
    });

    return {
        invoiceItemsTable,
        options,
    };
}
