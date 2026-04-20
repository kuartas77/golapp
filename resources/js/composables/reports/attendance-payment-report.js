import { computed, onMounted, reactive, ref, useTemplateRef, watch } from 'vue'
import configLanguaje from '@/utils/datatableUtils'
import { usePageTitle } from '@/composables/use-meta'
import api from '@/utils/axios'

const emptyDataTableResponse = (draw = 0) => ({
    draw,
    data: [],
    recordsTotal: 0,
    recordsFiltered: 0,
})

const normalizeFilters = (filters) =>
    Object.entries(filters).reduce((accumulator, [key, value]) => {
        if (value !== null && value !== '' && value !== undefined) {
            accumulator[key] = value
        }

        return accumulator
    }, {})

const normalizeOptions = (options) => {
    if (Array.isArray(options)) {
        return options
    }

    if (options && typeof options === 'object') {
        return Object.values(options)
    }

    return []
}

const buildExportUrl = (reportKey, format, filters) => {
    const query = new URLSearchParams(normalizeFilters(filters)).toString()
    const baseUrl = `/export/attendance-payment/${reportKey}/${format}`

    return query ? `${baseUrl}?${query}` : baseUrl
}

export default function useAttendancePaymentReport() {
    usePageTitle('Mensualidades vs asistencias')

    const summaryTable = useTemplateRef('summaryTable')
    const playerTable = useTemplateRef('playerTable')

    const isReady = ref(false)
    const isLoading = ref(false)
    const loadError = ref(null)
    const hasBootstrapped = ref(false)
    const years = ref([])
    const months = ref([])
    const groups = ref([])

    const filters = reactive({
        year: null,
        month: null,
        training_group_id: null,
    })

    const summaryColumns = [
        { data: 'training_group_name', name: 'training_group_name', title: 'Grupo' },
        { data: 'year', name: 'year', title: 'Año' },
        { data: 'month', name: 'month', title: 'Mes' },
        { data: 'players_with_attendance', name: 'players_with_attendance', title: 'Jugadores con asistencia' },
        { data: 'flagged_players', name: 'flagged_players', title: 'Jugadores observados' },
        { data: 'total_attendances', name: 'total_attendances', title: 'Asistencias' },
        { data: 'flagged_percentage', name: 'flagged_percentage', title: '% Observados' },
    ]

    const playerColumns = [
        { data: 'unique_code', name: 'unique_code', title: 'Código' },
        { data: 'player_name', name: 'player_name', title: 'Jugador' },
        { data: 'training_group_name', name: 'training_group_name', title: 'Grupo' },
        { data: 'year', name: 'year', title: 'Año' },
        { data: 'month', name: 'month', title: 'Mes' },
        { data: 'total_attendances', name: 'total_attendances', title: 'Asistencias' },
        { data: 'total_sessions_registered', name: 'total_sessions_registered', title: 'Sesiones registradas' },
        // { data: 'payment_status_label', name: 'payment_status_label', title: 'Estado mensualidad' },
        { data: 'flag_reason', name: 'flag_reason', title: 'Motivo' },
    ]

    const createTableOptions = (endpoint, columns, order = [[0, 'asc']]) => ({
        ...configLanguaje,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        pageLength: 10,
        processing: true,
        serverSide: true,
        searchDelay: 400,
        order,
        ajax: async (data, callback) => {
            try {
                const response = await api.get(endpoint, {
                    params: {
                        ...data,
                        ...normalizeFilters(filters),
                    },
                })

                callback(response.data)
            } catch {
                callback(emptyDataTableResponse(data.draw))
            }
        },
        columns,
        columnDefs: [
            {
                targets: '_all',
                className: 'dt-head-center dt-body-center',
            },
        ],
    })

    const summaryOptions = createTableOptions(
        '/api/v2/reports/attendance-payment/monthly-by-group',
        summaryColumns,
        [[4, 'desc']]
    )

    const playerOptions = createTableOptions(
        '/api/v2/reports/attendance-payment/monthly-by-player',
        playerColumns,
        [[1, 'asc']]
    )

    const reloadTable = (tableRef) => {
        const dt = tableRef.value?.table?.dt

        if (dt) {
            dt.ajax.reload(null, false)
        }
    }

    const searchReports = () => {
        reloadTable(summaryTable)
        reloadTable(playerTable)
    }

    const summaryExcelUrl = computed(() =>
        buildExportUrl('monthly-group', 'xlsx', filters)
    )
    const summaryPdfUrl = computed(() =>
        buildExportUrl('monthly-group', 'pdf', filters)
    )
    const playerExcelUrl = computed(() =>
        buildExportUrl('monthly-player', 'xlsx', filters)
    )
    const playerPdfUrl = computed(() =>
        buildExportUrl('monthly-player', 'pdf', filters)
    )

    const loadMetadata = async (params = {}) => {
        isLoading.value = true
        loadError.value = null

        try {
            const response = await api.get('/api/v2/reports/attendance-payment', {
                params,
                skipGlobalLoader: true,
            })

            years.value = normalizeOptions(response.data.years)
            months.value = normalizeOptions(response.data.months)
            groups.value = normalizeOptions(response.data.groups)

            const defaultYear = params.year
                ?? filters.year
                ?? response.data.defaultYear
                ?? years.value[years.value.length - 1]?.value
                ?? new Date().getFullYear()
            const defaultMonth = params.month
                ?? filters.month
                ?? response.data.defaultMonth
                ?? months.value[0]?.value
                ?? 1

            filters.year = defaultYear
            filters.month = defaultMonth

            if (!groups.value.some((group) => group.value === filters.training_group_id)) {
                filters.training_group_id = null
            }

            isReady.value = true
        } catch (error) {
            loadError.value = error.response?.data?.message || 'No fue posible cargar el reporte.'
        } finally {
            isLoading.value = false
        }
    }

    watch(
        () => [filters.year, filters.month],
        async ([year, month], [previousYear, previousMonth]) => {
            if (!hasBootstrapped.value || !year || !month || (year === previousYear && month === previousMonth)) {
                return
            }

            await loadMetadata({ year, month })
        }
    )

    onMounted(async () => {
        await loadMetadata()
        hasBootstrapped.value = true
    })

    return {
        filters,
        isLoading,
        isReady,
        loadError,
        months,
        playerColumns,
        playerExcelUrl,
        playerOptions,
        playerPdfUrl,
        playerTable,
        searchReports,
        summaryColumns,
        summaryExcelUrl,
        summaryOptions,
        summaryPdfUrl,
        summaryTable,
        groups,
        years,
    }
}
