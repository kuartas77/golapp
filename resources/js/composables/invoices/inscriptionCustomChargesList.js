import configLanguaje from '@/utils/datatableUtils'
import api from '@/utils/axios'
import dayjs from '@/utils/dayjs'
import { useTemplateRef } from 'vue'

const STATUS_LABELS = {
    pending: 'Pendiente',
    due: 'Debe',
    paid: 'Pagado',
}

const STATUS_BADGES = {
    pending: 'badge-warning',
    due: 'badge-danger',
    paid: 'badge-success',
}

const emptyDataTableResponse = (draw = 0) => ({
    draw,
    data: [],
    recordsTotal: 0,
    recordsFiltered: 0,
})

const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;')

const formatDate = (value) => {
    if (!value) {
        return 'N/D'
    }

    const formatted = dayjs(value).format('DD/MM/YYYY')
    return formatted === 'Invalid Date' ? 'N/D' : formatted
}

export const statusLabel = (status) => STATUS_LABELS[status] ?? status

export const statusClass = (status) => STATUS_BADGES[status] ?? 'badge-light'

export default function useInscriptionCustomChargesList() {
    const table = useTemplateRef('custom_charges_table')

    const columns = [
        {
            data: 'inscription.player.full_names',
            title: 'Jugador',
            name: 'player_name',
            defaultContent: 'N/D',
            render: (data, type, row) => {
                const name = data || row.inscription?.player?.full_names || 'N/D'
                const code = row.inscription?.player?.unique_code

                if (!code) {
                    return escapeHtml(name)
                }

                return `
                    <div class="fw-semibold">${escapeHtml(name)}</div>
                    <small class="text-muted">${escapeHtml(code)}</small>
                `
            },
        },
        {
            data: 'inscription.year',
            title: 'Año',
            name: 'inscriptions.year',
            defaultContent: 'N/D',
        },
        {
            data: 'name',
            title: 'Cargo',
            name: 'inscription_custom_charges.name',
            render: (data, type, row) => `<div class="fw-semibold">${escapeHtml(data || 'N/D')}</div>`,
        },
        {
            data: 'value',
            title: 'Valor',
            name: 'inscription_custom_charges.value',
            searchable: false,
            render: data => moneyFormat(Number(data || 0)),
        },
        {
            data: 'status',
            title: 'Estado',
            name: 'inscription_custom_charges.status',
            render: data => `<span class="badge ${statusClass(data)}">${escapeHtml(statusLabel(data))}</span>`,
        },
        {
            data: 'due_date',
            title: 'Vence',
            name: 'inscription_custom_charges.due_date',
            render: data => formatDate(data),
        },
        {
            data: 'invoice_item.invoice.invoice_number',
            title: 'Factura',
            name: 'invoice_number',
            defaultContent: 'Sin facturar',
            render: data => escapeHtml(data || 'Sin facturar'),
        },
        {
            data: 'id',
            title: 'Acciones',
            name: 'inscription_custom_charges.id',
            searchable: false,
            orderable: false,
            render: '#actions',
        },
    ]

    const options = {
        ...configLanguaje,
        lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
        pageLength: 10,
        processing: true,
        serverSide: true,
        deferRender: true,
        searchDelay: 400,
        order: [[7, 'desc']],
        ajax: async (data, callback) => {
            try {
                const response = await api.get('/api/v2/admin/inscription-custom-charges', { params: data })

                callback({
                    draw: data.draw,
                    data: response.data.data ?? [],
                    recordsTotal: response.data.recordsTotal ?? 0,
                    recordsFiltered: response.data.recordsFiltered ?? 0,
                })
            } catch {
                callback(emptyDataTableResponse(data.draw))
            }
        },
        columns,
        columnDefs: [
            { responsivePriority: 1, targets: 0 },
            { responsivePriority: 2, targets: columns.length - 1 },
            { targets: [1, 4, 5, 6, 7], className: 'dt-head-center dt-body-center' },
            { targets: [3], className: 'dt-head-right dt-body-right' },
            { targets: [7], width: '1%' },
        ],
    }

    const reloadTable = () => {
        const dt = table.value?.table?.dt

        if (dt) {
            dt.ajax.reload(null, false)
        }
    }

    return {
        options,
        table,
        reloadTable,
    }
}
