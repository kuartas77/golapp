import configLanguaje from '@/utils/datatableUtils'
import api from '@/utils/axios'
import { formatAppDate, formatAppMoney, renderAppStatus, resolveAppStatus } from '@/utils/appFormatters'
import { ref, useTemplateRef } from 'vue'

const STATUS_OPTIONS = [
    { value: '', label: 'Estado' },
    { value: 'pending', label: 'Pendiente' },
    { value: 'due', label: 'Debe' },
    { value: 'paid', label: 'Pagado' },
]

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

export const statusLabel = (status) => resolveAppStatus(status, { context: 'custom-charge' }).label

export const statusClass = (status) => `badge-${resolveAppStatus(status, { context: 'custom-charge' }).variant}`

const replaceHeaderContent = (header, element) => {
    header.replaceChildren(element)
}

const drawFilteredColumn = (column, value) => {
    if (column.search() === value) {
        return
    }

    column.search(value).draw()
}

const createTextFilter = (column, placeholder, type = 'search') => {
    const input = document.createElement('input')
    input.type = type
    input.placeholder = placeholder
    input.className = 'form-control form-control-sm'
    input.autocomplete = 'off'
    input.setAttribute('aria-label', `Filtrar por ${placeholder.toLowerCase()}`)
    input.addEventListener(type === 'date' ? 'change' : 'input', function () {
        drawFilteredColumn(column, this.value)
    })

    if (type !== 'date') {
        input.addEventListener('search', function () {
            drawFilteredColumn(column, this.value)
        })
    }

    replaceHeaderContent(column.header(), input)
}

const createSelectFilter = (column, options) => {
    const select = document.createElement('select')
    select.className = 'form-select form-select-sm'
    select.setAttribute('aria-label', `Filtrar por ${options[0].label.toLowerCase()}`)

    options.forEach((optionData) => {
        const option = document.createElement('option')
        option.value = optionData.value
        option.textContent = optionData.label
        select.append(option)
    })

    select.addEventListener('change', function () {
        drawFilteredColumn(column, this.value)
    })

    replaceHeaderContent(column.header(), select)
}

export default function useInscriptionCustomChargesList() {
    const table = useTemplateRef('custom_charges_table')
    const globalError = ref('')

    const columns = [
        {
            data: 'player_name',
            title: 'Deportista',
            name: 'player_name',
            defaultContent: 'N/D',
            render: (data, type, row) => {
                const name = data || row.inscription?.player?.full_names || 'N/D'
                const code = row.player_unique_code || row.inscription?.player?.unique_code

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
            data: 'inscription_year',
            title: 'Año',
            name: 'inscriptions.year',
            defaultContent: 'N/D',
            render: (data, type, row) => escapeHtml(data || row.inscription?.year || 'N/D'),
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
            render: data => formatAppMoney(Number(data || 0)),
        },
        {
            data: 'status',
            title: 'Estado',
            name: 'inscription_custom_charges.status',
            render: (data, type) => renderAppStatus(data, { context: 'custom-charge', type: type ?? 'display' }),
        },
        {
            data: 'due_date',
            title: 'Vence',
            name: 'inscription_custom_charges.due_date',
            render: data => formatAppDate(data, { fallback: 'N/D' }),
        },
        {
            data: 'invoice_number',
            title: 'Documento',
            name: 'invoice_number',
            defaultContent: 'Sin documento',
            render: (data, type, row) => escapeHtml(data || row.invoice_item?.invoice?.invoice_number || 'Sin documento'),
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
        layout: {
            topStart: { pageLength: { menu: [10, 20, 30, 50, 100] } },
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging',
        },
        lengthMenu: [[10, 20, 30, 50, 100], [10, 20, 30, 50, 100]],
        pageLength: 10,
        processing: true,
        serverSide: true,
        pipeline: { pages: 5 },
        deferRender: true,
        searchDelay: 400,
        ordering: false,
        ajax: async (data, callback) => {
            try {
                const response = await api.get('/api/v2/admin/inscription-custom-charges', { params: data })
                globalError.value = ''

                callback({
                    draw: data.draw,
                    data: response.data.data ?? [],
                    recordsTotal: response.data.recordsTotal ?? 0,
                    recordsFiltered: response.data.recordsFiltered ?? 0,
                })
            } catch (error) {
                globalError.value = error.response?.data?.message || 'Intenta nuevamente. Si el problema continúa, comunícate con soporte.'
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
        initComplete: function () {
            const api = this.api()

            createTextFilter(api.column(0), 'Deportista')
            createTextFilter(api.column(1), 'Año')
            createTextFilter(api.column(2), 'Cargo')
            createSelectFilter(api.column(4), STATUS_OPTIONS)
            createTextFilter(api.column(5), 'Vence', 'date')
            createTextFilter(api.column(6), 'Documento')
            api.columns.adjust()
        },
    }

    const reloadTable = () => {
        globalError.value = ''
        const dt = table.value?.table?.dt

        if (dt) {
            dt.clearPipeline()
            dt.ajax.reload(null, false)
        }
    }

    return {
        options,
        table,
        reloadTable,
        globalError,
    }
}
