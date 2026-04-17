import { computed, onMounted, reactive, ref, useTemplateRef } from 'vue'
import configLanguaje from '@/utils/datatableUtils'
import { usePageTitle } from '@/composables/use-meta'
import api from '@/utils/axios'
import { useSetting } from '@/store/settings-store'

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
    return query
        ? `/export/${reportKey}/${format}?${query}`
        : `/export/${reportKey}/${format}`
}

export default function useAssistReports() {
    usePageTitle('Reportes de asistencia')

    const settings = useSetting()

    const monthlyPlayerTable = useTemplateRef('monthlyPlayerTable')
    const monthlyGroupTable = useTemplateRef('monthlyGroupTable')
    const annualConsolidatedTable = useTemplateRef('annualConsolidatedTable')

    const isReady = ref(false)
    const isLoading = ref(false)
    const loadError = ref(null)
    const years = ref([])
    const months = ref([])

    const monthlyPlayerFilters = reactive({
        year: null,
        month: null,
        training_group_id: null,
    })

    const monthlyGroupFilters = reactive({
        year: null,
        month: null,
        training_group_id: null,
    })

    const annualConsolidatedFilters = reactive({
        year: null,
        training_group_id: null,
    })

    const groupOptions = computed(() =>
        settings.groups
            .filter((group) => group.name !== 'Provisional')
            .map((group) => ({
                value: group.id,
                label: group.full_group ?? group.full_schedule_group ?? group.name,
            }))
    )

    const monthlyPlayerColumns = [
        { data: 'unique_code', name: 'p.unique_code', title: 'Código' },
        { data: 'player_name', name: 'player_name', title: 'Jugador' },
        { data: 'training_group_name', name: 'training_group_name', title: 'Grupo' },
        { data: 'year', name: 'd.year', title: 'Año' },
        { data: 'month', name: 'd.month', title: 'Mes' },
        { data: 'total_asistencias', name: 'total_asistencias', title: 'Asistencias' },
        { data: 'total_faltas', name: 'total_faltas', title: 'Faltas' },
        { data: 'total_excusas', name: 'total_excusas', title: 'Excusas' },
        { data: 'total_retiros', name: 'total_retiros', title: 'Retiros' },
        { data: 'total_incapacidades', name: 'total_incapacidades', title: 'Incapacidades' },
        { data: 'total_sesiones_registradas', name: 'total_sesiones_registradas', title: 'Sesiones' },
        { data: 'porcentaje_asistencia', name: 'porcentaje_asistencia', title: '% Asistencia' },
    ]

    const monthlyGroupColumns = [
        { data: 'training_group_name', name: 'training_group_name', title: 'Grupo' },
        { data: 'year', name: 'd.year', title: 'Año' },
        { data: 'month', name: 'd.month', title: 'Mes' },
        { data: 'total_jugadores', name: 'total_jugadores', title: 'Jugadores' },
        { data: 'total_asistencias', name: 'total_asistencias', title: 'Asistencias' },
        { data: 'total_faltas', name: 'total_faltas', title: 'Faltas' },
        { data: 'total_excusas', name: 'total_excusas', title: 'Excusas' },
        { data: 'total_retiros', name: 'total_retiros', title: 'Retiros' },
        { data: 'total_incapacidades', name: 'total_incapacidades', title: 'Incapacidades' },
        { data: 'total_sesiones_registradas', name: 'total_sesiones_registradas', title: 'Sesiones' },
        { data: 'porcentaje_asistencia', name: 'porcentaje_asistencia', title: '% Asistencia' },
    ]

    const annualConsolidatedColumns = [
        { data: 'unique_code', name: 'p.unique_code', title: 'Código' },
        { data: 'player_name', name: 'player_name', title: 'Jugador' },
        { data: 'training_group_name', name: 'training_group_name', title: 'Grupo' },
        { data: 'year', name: 'd.year', title: 'Año' },
        { data: 'total_asistencias', name: 'total_asistencias', title: 'Asistencias' },
        { data: 'total_faltas', name: 'total_faltas', title: 'Faltas' },
        { data: 'total_excusas', name: 'total_excusas', title: 'Excusas' },
        { data: 'total_retiros', name: 'total_retiros', title: 'Retiros' },
        { data: 'total_incapacidades', name: 'total_incapacidades', title: 'Incapacidades' },
        { data: 'total_sesiones_registradas', name: 'total_sesiones_registradas', title: 'Sesiones' },
        { data: 'porcentaje_asistencia', name: 'porcentaje_asistencia', title: '% Asistencia' },
    ]

    const createTableOptions = (endpoint, filters, columns, order = [[0, 'asc']]) => ({
        ...configLanguaje,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        pageLength: 10,
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: true,
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

    const monthlyPlayerOptions = createTableOptions(
        '/api/v2/reports/attendance/monthly-by-player',
        monthlyPlayerFilters,
        monthlyPlayerColumns
    )

    const monthlyGroupOptions = createTableOptions(
        '/api/v2/reports/attendance/monthly-by-group',
        monthlyGroupFilters,
        monthlyGroupColumns
    )

    const annualConsolidatedOptions = createTableOptions(
        '/api/v2/reports/attendance/annual-consolidated',
        annualConsolidatedFilters,
        annualConsolidatedColumns
    )

    const reloadTable = (tableRef) => {
        const dt = tableRef.value?.table?.dt

        if (dt) {
            dt.ajax.reload(null, false)
        }
    }

    const searchMonthlyPlayer = () => reloadTable(monthlyPlayerTable)
    const searchMonthlyGroup = () => reloadTable(monthlyGroupTable)
    const searchAnnualConsolidated = () => reloadTable(annualConsolidatedTable)

    const monthlyPlayerExcelUrl = computed(() =>
        buildExportUrl('monthly-player', 'xlsx', monthlyPlayerFilters)
    )
    const monthlyPlayerPdfUrl = computed(() =>
        buildExportUrl('monthly-player', 'pdf', monthlyPlayerFilters)
    )

    const monthlyGroupExcelUrl = computed(() =>
        buildExportUrl('monthly-group', 'xlsx', monthlyGroupFilters)
    )
    const monthlyGroupPdfUrl = computed(() =>
        buildExportUrl('monthly-group', 'pdf', monthlyGroupFilters)
    )

    const annualConsolidatedExcelUrl = computed(() =>
        buildExportUrl('annual-consolidated', 'xlsx', annualConsolidatedFilters)
    )
    const annualConsolidatedPdfUrl = computed(() =>
        buildExportUrl('annual-consolidated', 'pdf', annualConsolidatedFilters)
    )

    const loadMetadata = async () => {
        isLoading.value = true
        loadError.value = null

        try {
            if (!settings.groups.length) {
                await settings.getSettings()
            }

            const response = await api.get('/api/v2/reports/assists')

            years.value = normalizeOptions(response.data.years)
            months.value = normalizeOptions(response.data.months)

            const defaultYear = response.data.defaultYear ?? years.value[years.value.length - 1]?.value ?? null
            const defaultMonth = response.data.defaultMonth ?? months.value[0]?.value ?? null

            monthlyPlayerFilters.year = defaultYear
            monthlyPlayerFilters.month = defaultMonth

            monthlyGroupFilters.year = defaultYear
            monthlyGroupFilters.month = defaultMonth

            annualConsolidatedFilters.year = defaultYear

            isReady.value = true
        } catch (error) {
            loadError.value = error.response?.data?.message || 'No fue posible cargar los filtros de reportes.'
        } finally {
            isLoading.value = false
        }
    }

    onMounted(() => {
        loadMetadata()
    })

    return {
        annualConsolidatedColumns,
        annualConsolidatedExcelUrl,
        annualConsolidatedFilters,
        annualConsolidatedOptions,
        annualConsolidatedPdfUrl,
        groupOptions,
        isLoading,
        isReady,
        loadError,
        monthlyGroupColumns,
        monthlyGroupExcelUrl,
        monthlyGroupFilters,
        monthlyGroupOptions,
        monthlyGroupPdfUrl,
        monthlyGroupTable,
        monthlyPlayerColumns,
        monthlyPlayerExcelUrl,
        monthlyPlayerFilters,
        monthlyPlayerOptions,
        monthlyPlayerPdfUrl,
        monthlyPlayerTable,
        months,
        searchAnnualConsolidated,
        searchMonthlyGroup,
        searchMonthlyPlayer,
        years,
        annualConsolidatedTable,
    }
}
