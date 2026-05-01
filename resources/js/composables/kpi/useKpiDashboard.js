import { computed, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

import { usePageTitle } from '@/composables/use-meta'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { useBackofficeAccess } from '@/composables/useBackofficeAccess'
import { kpiTutorial } from '@/tutorials/dashboard'
import api from '@/utils/axios'

const buildQuery = (filters) => {
    const query = {}

    if (filters.year) {
        query.year = filters.year
    }

    if (filters.month) {
        query.month = filters.month
    }

    if (filters.training_group_id) {
        query.training_group_id = filters.training_group_id
    }

    return query
}

const normalizeOptions = (options) => {
    if (Array.isArray(options)) {
        return options
    }

    if (options && typeof options === 'object') {
        return Object.values(options)
    }

    return []
}

const parseQueryNumber = (value) => {
    const normalizedValue = Array.isArray(value) ? value[0] : value

    if (normalizedValue === null || normalizedValue === undefined || normalizedValue === '') {
        return null
    }

    const parsed = Number(normalizedValue)

    return Number.isInteger(parsed) && parsed > 0 ? parsed : null
}

export default function useKpiDashboard() {
    const route = useRoute()
    const router = useRouter()
    const tutorial = usePageTutorial(kpiTutorial)
    const { access } = useBackofficeAccess()

    usePageTitle('Indicadores del backoffice')

    const isLoading = ref(false)
    const isReady = ref(false)
    const loadError = ref(null)
    const years = ref([])
    const months = ref([])
    const groupOptions = ref([])
    const summaryCards = ref([])
    const paymentGroupReport = ref(null)
    const amountPaymentGroupReport = ref(null)
    const monthlyTrendReport = ref(null)
    const attendanceMixReport = ref(null)
    const canViewMonetaryValues = ref(true)
    const rankings = ref({
        compliance: [],
        low_attendance: [],
        debt: [],
        flagged: [],
    })
    const reportLinks = ref({
        assists: null,
        payments: null,
        attendance_payment: null,
    })

    const filters = reactive({
        year: null,
        month: null,
        training_group_id: null,
    })

    const syncFiltersFromRoute = () => {
        filters.year = parseQueryNumber(route.query.year)
        filters.month = parseQueryNumber(route.query.month)
        filters.training_group_id = parseQueryNumber(route.query.training_group_id)
    }

    const applyResponse = (payload) => {
        years.value = normalizeOptions(payload.filters?.years)
        months.value = normalizeOptions(payload.filters?.months)
        groupOptions.value = normalizeOptions(payload.group_options)
        summaryCards.value = payload.summary_cards ?? []
        paymentGroupReport.value = payload.payment_group_report ?? null
        amountPaymentGroupReport.value = payload.amount_payment_group_report ?? null
        monthlyTrendReport.value = payload.monthly_trend_report ?? null
        attendanceMixReport.value = payload.attendance_mix_report ?? payload.assist_report ?? null
        canViewMonetaryValues.value = payload.permissions?.can_view_monetary_values ?? true
        rankings.value = payload.rankings ?? {
            compliance: [],
            low_attendance: [],
            debt: [],
            flagged: [],
        }
        reportLinks.value = payload.report_links ?? {
            assists: null,
            payments: null,
            attendance_payment: null,
        }

        filters.year = payload.filters?.selectedYear
            ?? filters.year
            ?? payload.filters?.defaultYear
            ?? years.value[years.value.length - 1]?.value
            ?? new Date().getFullYear()
        filters.month = payload.filters?.selectedMonth
            ?? filters.month
            ?? payload.filters?.defaultMonth
            ?? new Date().getMonth() + 1

        const selectedGroupId = payload.filters?.selectedGroupId ?? null
        filters.training_group_id = groupOptions.value.some((group) => group.value === selectedGroupId)
            ? selectedGroupId
            : null
    }

    const loadDashboard = async (params = buildQuery(route.query)) => {
        isLoading.value = true
        loadError.value = null

        try {
            const response = await api.get('/api/v2/kpis', {
                params,
                skipGlobalLoader: true,
            })

            applyResponse(response.data ?? {})
            isReady.value = true
        } catch (error) {
            loadError.value = error.response?.data?.message || 'No fue posible cargar los indicadores.'
        } finally {
            isLoading.value = false
        }
    }

    const applyFilters = async () => {
        const nextQuery = buildQuery(filters)
        const currentQuery = buildQuery(route.query)

        if (JSON.stringify(nextQuery) === JSON.stringify(currentQuery)) {
            await loadDashboard(nextQuery)
            return
        }

        await router.push({
            name: 'kpi',
            query: nextQuery,
        })
    }

    const resetFilters = async () => {
        await router.push({
            name: 'kpi',
            query: {},
        })
    }

    const hasActiveFilters = computed(() => Object.keys(buildQuery(route.query)).length > 0)
    const canOpenReports = computed(() => Boolean(access.reports.value))

    watch(
        () => route.query,
        async () => {
            syncFiltersFromRoute()
            await loadDashboard(buildQuery(route.query))
        },
        { immediate: true }
    )

    return {
        amountPaymentGroupReport,
        applyFilters,
        attendanceMixReport,
        canOpenReports,
        canViewMonetaryValues,
        filters,
        groupOptions,
        hasActiveFilters,
        isLoading,
        isReady,
        loadError,
        monthlyTrendReport,
        months,
        paymentGroupReport,
        rankings,
        reportLinks,
        resetFilters,
        summaryCards,
        tutorial,
        years,
    }
}
