import { computed, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { usePageTitle } from '@/composables/use-meta'
import { useAppState } from '@/store/app-state'
import api from '@/utils/axios'

export const compactQuery = (values) => Object.fromEntries(
    Object.entries(values).filter(([, value]) => value !== null && value !== undefined && value !== '')
)

export const formatNumber = (value) => new Intl.NumberFormat('es-CO').format(Number(value) || 0)
export const formatDecimal = (value) => new Intl.NumberFormat('es-CO', { minimumFractionDigits: 1, maximumFractionDigits: 2 }).format(Number(value) || 0)

const useChartTheme = () => {
    const appState = useAppState()
    const mode = computed(() => appState.is_dark_mode ? 'dark' : 'light')
    const text = computed(() => appState.is_dark_mode ? '#e0e6ed' : '#3b3f5c')
    const grid = computed(() => appState.is_dark_mode ? '#1b2e4b' : '#e0e6ed')

    return { mode, text, grid }
}

export function useCompetitionStatsRanking() {
    const route = useRoute()
    const router = useRouter()
    const theme = useChartTheme()
    const payload = ref({ summary: {}, groups: [], options: {}, data_quality: {} })
    const filters = ref({ year: null, tournament_id: null, category: null })
    const isLoading = ref(false)
    const globalError = ref(null)

    usePageTitle('Estadísticas de competencias')

    const syncFilters = () => {
        filters.value = {
            year: route.query.year || null,
            tournament_id: route.query.tournament_id || null,
            category: route.query.category || null,
        }
    }

    const load = async () => {
        isLoading.value = true
        globalError.value = null
        try {
            const response = await api.get('/api/v2/competition-stats', { params: compactQuery(route.query) })
            payload.value = response.data
            filters.value.year ||= String(response.data.filters?.year || '')
        } catch (error) {
            globalError.value = error.response?.data?.message || 'No fue posible cargar las estadísticas de competencias.'
            payload.value = { summary: {}, groups: [], options: {}, data_quality: {} }
        } finally {
            isLoading.value = false
        }
    }

    const applyFilters = () => router.push({ name: 'competition-stats.index', query: compactQuery(filters.value) })
    const resetFilters = () => router.push({ name: 'competition-stats.index', query: {} })
    const resultSeries = computed(() => [payload.value.summary?.wins || 0, payload.value.summary?.draws || 0, payload.value.summary?.losses || 0])
    const resultOptions = computed(() => ({
        chart: { type: 'donut' }, theme: { mode: theme.mode.value }, labels: ['Victorias', 'Empates', 'Derrotas'],
        colors: ['#28a745', '#ffc107', '#dc3545'], legend: { labels: { colors: theme.text.value } },
    }))
    const goalsSeries = computed(() => [
        { name: 'GF', data: payload.value.groups?.slice(0, 10).map((group) => group.goals_for) || [] },
        { name: 'GC', data: payload.value.groups?.slice(0, 10).map((group) => group.goals_against) || [] },
    ])
    const goalsOptions = computed(() => ({
        chart: { type: 'bar', toolbar: { show: false } }, theme: { mode: theme.mode.value },
        colors: ['#0d6efd', '#dc3545'], grid: { borderColor: theme.grid.value },
        xaxis: { categories: payload.value.groups?.slice(0, 10).map((group) => group.name) || [], labels: { style: { colors: theme.text.value } } },
        yaxis: { labels: { style: { colors: [theme.text.value] } } }, legend: { labels: { colors: theme.text.value } },
    }))

    watch(() => route.query, async () => { syncFilters(); await load() }, { immediate: true })

    return { payload, filters, isLoading, globalError, applyFilters, resetFilters, load, resultSeries, resultOptions, goalsSeries, goalsOptions }
}

export function useCompetitionStatsDetail() {
    const route = useRoute()
    const router = useRouter()
    const theme = useChartTheme()
    const payload = ref({ group: null, summary: {}, recent_matches: [], goal_trend: [], options: {}, data_quality: {} })
    const filters = ref({ year: null, tournament_id: null })
    const isLoading = ref(false)
    const globalError = ref(null)

    usePageTitle(computed(() => payload.value.group?.name ? `Estadísticas de ${payload.value.group.name}` : 'Detalle de competencia'))

    const syncFilters = () => {
        filters.value = { year: route.query.year || null, tournament_id: route.query.tournament_id || null }
    }
    const load = async () => {
        isLoading.value = true
        globalError.value = null
        try {
            const response = await api.get(`/api/v2/competition-stats/groups/${route.params.id}`, { params: compactQuery(route.query) })
            payload.value = response.data
            filters.value.year ||= String(response.data.filters?.year || '')
        } catch (error) {
            globalError.value = error.response?.data?.message || 'No fue posible cargar el detalle del grupo.'
        } finally {
            isLoading.value = false
        }
    }
    const applyFilters = () => router.push({ name: 'competition-stats.detail', params: { id: route.params.id }, query: compactQuery(filters.value) })
    const resultSeries = computed(() => [payload.value.summary?.wins || 0, payload.value.summary?.draws || 0, payload.value.summary?.losses || 0])
    const resultOptions = computed(() => ({
        chart: { type: 'donut' }, theme: { mode: theme.mode.value }, labels: ['Victorias', 'Empates', 'Derrotas'],
        colors: ['#28a745', '#ffc107', '#dc3545'], legend: { labels: { colors: theme.text.value } },
    }))
    const trendSeries = computed(() => [
        { name: 'GF', data: payload.value.goal_trend?.map((match) => match.goals_for) || [] },
        { name: 'GC', data: payload.value.goal_trend?.map((match) => match.goals_against) || [] },
    ])
    const trendOptions = computed(() => ({
        chart: { type: 'line', toolbar: { show: false } }, theme: { mode: theme.mode.value }, colors: ['#0d6efd', '#dc3545'],
        stroke: { curve: 'smooth', width: 3 }, grid: { borderColor: theme.grid.value },
        xaxis: { categories: payload.value.goal_trend?.map((match) => match.date) || [], labels: { style: { colors: theme.text.value } } },
        yaxis: { min: 0, forceNiceScale: true, labels: { style: { colors: [theme.text.value] } } },
        legend: { labels: { colors: theme.text.value } },
    }))

    watch(() => [route.params.id, route.query], async () => { syncFilters(); await load() }, { immediate: true, deep: true })

    return { payload, filters, isLoading, globalError, applyFilters, load, resultSeries, resultOptions, trendSeries, trendOptions }
}
