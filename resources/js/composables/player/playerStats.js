import { computed, onMounted, ref, watch } from 'vue'
import { usePageTitle } from '@/composables/use-meta'
import { useRoute, useRouter } from 'vue-router'
import api from '@/utils/axios'
import dayjs from '@/utils/dayjs'
import { useAppState } from '@/store/app-state'

const toNumber = (value, fallback = 0) => {
    const parsed = Number(value)

    return Number.isFinite(parsed) ? parsed : fallback
}

const buildQuery = (payload = {}) =>
    Object.fromEntries(
        Object.entries(payload).filter(([, value]) => value !== null && value !== undefined && value !== '')
    )

const numberFormatter = new Intl.NumberFormat('es-CO')

const decimalFormatter = (digits = 2) =>
    new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    })

const formatNumber = (value) => numberFormatter.format(toNumber(value))
const formatDecimal = (value, digits = 2) => decimalFormatter(digits).format(toNumber(value))
const formatDate = (value, format = 'DD/MM/YYYY') => (value ? dayjs(value).format(format) : '-')
const formatDateTime = (value) => (value ? dayjs(value).format('DD/MM/YYYY HH:mm') : '-')
const formatAge = (value) => (value ? dayjs().diff(dayjs(value), 'year') : null)

const safeDivide = (dividend, divisor) => {
    const normalizedDivisor = toNumber(divisor)

    if (!normalizedDivisor) {
        return 0
    }

    return toNumber(dividend) / normalizedDivisor
}

const ratingVariant = (value) => {
    const rating = toNumber(value)

    if (rating >= 4) {
        return 'success'
    }

    if (rating >= 3) {
        return 'warning'
    }

    return 'danger'
}

const scoringRules = [
    { label: 'Gol', points: '10 pts' },
    { label: 'Asistencia de gol', points: '7 pts' },
    { label: 'Atajada', points: '5 pts' },
    { label: 'Calificación', points: 'x3' },
    { label: 'Minuto jugado', points: '0.1 pts' },
    { label: 'Titular', points: '3 pts' },
    { label: 'Amarilla', points: '-2 pts' },
    { label: 'Roja', points: '-5 pts' },
]

export function usePlayerStatsRanking() {
    const route = useRoute()
    const router = useRouter()

    const players = ref([])
    const positions = ref([])
    const categories = ref([])
    const school = ref(null)
    const filters = ref({
        position: null,
        category: null,
    })
    const isLoading = ref(false)
    const globalError = ref(null)

    usePageTitle('Estadísticas de jugadores')

    const syncFiltersFromRoute = () => {
        filters.value = {
            position: typeof route.query.position === 'string' ? route.query.position : null,
            category: typeof route.query.category === 'string' ? route.query.category : null,
        }
    }

    const loadRanking = async (params = buildQuery(route.query)) => {
        isLoading.value = true
        globalError.value = null

        try {
            const response = await api.get('/api/v2/player-stats', { params })

            players.value = response.data.players ?? []
            positions.value = response.data.positions ?? []
            categories.value = response.data.categories ?? []
            school.value = response.data.school ?? null
        } catch (error) {
            globalError.value = error.response?.data?.message || 'No fue posible cargar el escalafón de jugadores.'
            players.value = []
        } finally {
            isLoading.value = false
        }
    }

    const applyFilters = async () => {
        const nextQuery = buildQuery(filters.value)
        const currentQuery = buildQuery(route.query)

        if (JSON.stringify(nextQuery) === JSON.stringify(currentQuery)) {
            await loadRanking(nextQuery)
            return
        }

        await router.push({ name: 'player-stats.index', query: nextQuery })
    }

    const resetFilters = async () => {
        await router.push({ name: 'player-stats.index', query: {} })
    }

    const reload = async () => {
        await loadRanking(buildQuery(route.query))
    }

    const hasActiveFilters = computed(() => Object.keys(buildQuery(filters.value)).length > 0)
    const leader = computed(() => players.value[0] ?? null)

    watch(
        () => route.query,
        async () => {
            syncFiltersFromRoute()
            await loadRanking(buildQuery(route.query))
        },
        { immediate: true }
    )

    return {
        categories,
        filters,
        formatDecimal,
        formatNumber,
        globalError,
        hasActiveFilters,
        isLoading,
        leader,
        players,
        positions,
        reload,
        resetFilters,
        school,
        scoringRules,
        applyFilters,
        ratingVariant,
        safeDivide,
    }
}

export function useTopPlayers() {
    const topScorers = ref([])
    const topAssists = ref([])
    const topGoalSaves = ref([])
    const topRated = ref([])
    const updatedAt = ref(null)
    const season = ref(null)
    const isLoading = ref(false)
    const globalError = ref(null)

    usePageTitle('Jugadores destacados')

    const loadTopPlayers = async () => {
        isLoading.value = true
        globalError.value = null

        try {
            const response = await api.get('/api/v2/top-players')

            topScorers.value = response.data.top_scorers ?? []
            topAssists.value = response.data.top_assists ?? []
            topGoalSaves.value = response.data.top_goal_saves ?? []
            topRated.value = response.data.top_rated ?? []
            updatedAt.value = response.data.updated_at ?? null
            season.value = response.data.season ?? null
        } catch (error) {
            globalError.value = error.response?.data?.message || 'No fue posible cargar los jugadores destacados.'
            topScorers.value = []
            topAssists.value = []
            topGoalSaves.value = []
            topRated.value = []
        } finally {
            isLoading.value = false
        }
    }

    const scorersWithEfficiency = computed(() =>
        topScorers.value.map((player) => ({
            ...player,
            goals_per_90: safeDivide(player.total_goles, player.minutos_jugados) * 90,
        }))
    )

    const spotlightPlayer = computed(() => topRated.value[0] ?? topScorers.value[0] ?? null)

    onMounted(async () => {
        await loadTopPlayers()
    })

    return {
        formatDate,
        formatDecimal,
        formatNumber,
        globalError,
        isLoading,
        scorersWithEfficiency,
        season,
        spotlightPlayer,
        topAssists,
        topGoalSaves,
        topRated,
        updatedAt,
        loadTopPlayers,
        ratingVariant,
    }
}

export function usePlayerStatsDetail() {
    const route = useRoute()
    const router = useRouter()
    const appState = useAppState()

    const player = ref(null)
    const positionsHistory = ref([])
    const recentMatches = ref([])
    const globalError = ref(null)
    const isLoading = ref(false)

    const chartTheme = computed(() => (appState.is_dark_mode ? 'dark' : 'light'))
    const chartTextColor = computed(() => (appState.is_dark_mode ? '#e0e6ed' : '#3b3f5c'))
    const chartMutedColor = computed(() => (appState.is_dark_mode ? '#888ea8' : '#888ea8'))
    const chartBorderColor = computed(() => (appState.is_dark_mode ? '#1b2e4b' : '#e0e6ed'))
    const chartSurfaceColor = computed(() => (appState.is_dark_mode ? '#191e3a' : '#ffffff'))

    const title = computed(() =>
        player.value?.player_name ? `Estadísticas de ${player.value.player_name}` : 'Detalle del jugador'
    )

    usePageTitle(title)

    const loadPlayerDetail = async (playerId = route.params.id) => {
        if (!playerId) {
            return
        }

        isLoading.value = true
        globalError.value = null

        try {
            const response = await api.get(`/api/v2/player/${playerId}/detail`)

            player.value = response.data.player ?? null
            positionsHistory.value = response.data.positions_history ?? []
            recentMatches.value = response.data.recent_matches ?? []
        } catch (error) {
            globalError.value = error.response?.data?.message || 'No fue posible cargar el detalle del jugador.'
            player.value = null
            positionsHistory.value = []
            recentMatches.value = []
        } finally {
            isLoading.value = false
        }
    }

    watch(
        () => route.params.id,
        async (playerId) => {
            await loadPlayerDetail(playerId)
        },
        { immediate: true }
    )

    const orderedMatches = computed(() =>
        [...recentMatches.value].sort(
            (left, right) => dayjs(left.fecha_partido).valueOf() - dayjs(right.fecha_partido).valueOf()
        )
    )

    const positionsChartSeries = computed(() => positionsHistory.value.map((item) => toNumber(item.veces_jugada)))

    const positionsChartOptions = computed(() => ({
        chart: {
            type: 'donut',
            toolbar: { show: false },
        },
        theme: {
            mode: chartTheme.value,
        },
        labels: positionsHistory.value.map((item) => item.position),
        colors: ['#0d6efd', '#20c997', '#fd7e14', '#6610f2', '#dc3545', '#ffc107', '#198754', '#6f42c1'],
        legend: {
            position: 'bottom',
            fontSize: '12px',
            labels: {
                colors: chartTextColor.value,
            },
        },
        dataLabels: {
            enabled: true,
            style: {
                fontSize: '12px',
                fontWeight: 600,
                colors: positionsHistory.value.map(() => chartSurfaceColor.value),
            },
        },
        stroke: {
            colors: [chartSurfaceColor.value],
        },
        tooltip: {
            theme: chartTheme.value,
        },
    }))

    const ratingsChartSeries = computed(() => [
        {
            name: 'Calificación',
            data: orderedMatches.value.map((match) => toNumber(match.qualification)),
        },
    ])

    const ratingsChartOptions = computed(() => ({
        chart: {
            type: 'line',
            toolbar: { show: false },
            zoom: { enabled: false },
        },
        theme: {
            mode: chartTheme.value,
        },
        colors: ['#0d6efd'],
        stroke: {
            curve: 'smooth',
            width: 3,
        },
        markers: {
            size: 4,
        },
        grid: {
            borderColor: chartBorderColor.value,
        },
        xaxis: {
            categories: orderedMatches.value.map((match) => formatDate(match.fecha_partido, 'DD MMM')),
            labels: {
                style: {
                    colors: orderedMatches.value.map(() => chartMutedColor.value),
                },
            },
            axisBorder: {
                color: chartBorderColor.value,
            },
            axisTicks: {
                color: chartBorderColor.value,
            },
        },
        yaxis: {
            min: 0,
            max: 5,
            tickAmount: 5,
            labels: {
                style: {
                    colors: [chartMutedColor.value],
                },
            },
        },
        tooltip: {
            theme: chartTheme.value,
            y: {
                formatter: (value) => `${formatDecimal(value, 1)}/5`,
            },
        },
    }))

    const age = computed(() => formatAge(player.value?.date_birth))
    const starterPercentage = computed(() => safeDivide(player.value?.veces_titular, player.value?.asistencias_partidos) * 100)
    const goalsPerMatch = computed(() => safeDivide(player.value?.total_goles, player.value?.asistencias_partidos))
    const assistsPerMatch = computed(() => safeDivide(player.value?.total_asistencias_gol, player.value?.asistencias_partidos))
    const savesPerMatch = computed(() => safeDivide(player.value?.total_atajadas, player.value?.asistencias_partidos))

    const getLastMatchForPosition = (position) =>
        recentMatches.value.find((match) => match.position === position)?.fecha_partido ?? null

    const goBack = async () => {
        if (window.history.length > 1) {
            router.back()
            return
        }

        await router.push({ name: 'player-stats.index' })
    }

    return {
        age,
        assistsPerMatch,
        formatDate,
        formatDateTime,
        formatDecimal,
        formatNumber,
        getLastMatchForPosition,
        globalError,
        goBack,
        goalsPerMatch,
        isLoading,
        loadPlayerDetail,
        orderedMatches,
        player,
        positionsChartOptions,
        positionsChartSeries,
        positionsHistory,
        ratingVariant,
        ratingsChartOptions,
        ratingsChartSeries,
        recentMatches,
        savesPerMatch,
        starterPercentage,
    }
}
