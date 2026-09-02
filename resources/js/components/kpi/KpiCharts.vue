<template>
    <section>
        <div class="kpi-content-heading mb-3">
            <p class="kpi-eyebrow mb-1">Comportamiento</p>
            <h4 class="mb-1">Análisis visual</h4>
            <p class="text-muted mb-0">Compara grupos, recaudo, tendencias y asistencia en el período seleccionado.</p>
        </div>

        <div class="row layout-top-spacing">
            <div class="layout-spacing col-xl-6 col-lg-12 col-sm-12 d-flex">
                <div class="panel br-6 h-100 w-100 kpi-chart-card" data-tour="kpi-payment-groups">
                    <div class="panel-body kpi-chart-card__body">
                        <template v-if="showPaymentGroupChart">
                            <div class="kpi-chart-copy kpi-chart-copy--with-meta">
                                <div>
                                    <h5>{{ paymentGroupTitle }}</h5>
                                    <p>{{ paymentGroupDescription }}</p>
                                </div>
                                <span class="kpi-chart-count">{{ formatGroupCount(paymentGroupCategories.length) }}</span>
                            </div>
                            <div class="kpi-chart-scroll kpi-chart-scroll--groups">
                                <div class="kpi-chart-scroll__canvas">
                                    <apexchart
                                        :height="paymentGroupChartHeight"
                                        type="bar"
                                        :options="paymentGroupOptions"
                                        :series="paymentGroupSeries" />
                                </div>
                            </div>
                        </template>
                        <ChartEmptyState v-else icon="fa-solid fa-chart-column" title="Sin datos de mensualidades">
                            No hay datos de mensualidades para el período seleccionado.
                        </ChartEmptyState>
                    </div>
                </div>
            </div>

            <div class="layout-spacing col-xl-6 col-lg-12 col-sm-12 d-flex">
                <KpiCollectionTable
                    class="h-100 w-100"
                    :can-view-monetary-values="canViewMonetaryValues"
                    :report="amountPaymentGroupReport" />
            </div>

            <div class="layout-spacing col-xl-7 col-lg-12 col-sm-12">
                <div class="panel br-6 h-100 kpi-chart-card" data-tour="kpi-monthly-trend">
                    <div class="panel-body kpi-chart-card__body d-flex flex-column gap-4">
                        <template v-if="showMonthlyTrendChart">
                            <template v-if="showSplitMonthlyTrend">
                                <div class="kpi-chart-copy">
                                    <h5>Recaudo mensual</h5>
                                    <p>Valor recaudado durante el año.</p>
                                </div>
                                <div class="kpi-chart-scroll">
                                    <div class="kpi-chart-scroll__canvas kpi-chart-scroll__canvas--timeline">
                                        <apexchart
                                            :height="monthlyTrendRevenueChartHeight"
                                            type="bar"
                                            :options="monthlyTrendRevenueOptions"
                                            :series="monthlyTrendRevenueSeries" />
                                    </div>
                                </div>

                                <div class="kpi-chart-divider pt-4">
                                    <div class="kpi-chart-copy">
                                        <h5>Mensualidades pagadas</h5>
                                        <p>Cantidad de mensualidades pagadas por mes.</p>
                                    </div>
                                    <div class="kpi-chart-scroll">
                                        <div class="kpi-chart-scroll__canvas kpi-chart-scroll__canvas--timeline">
                                            <apexchart
                                                :height="monthlyTrendPaymentsChartHeight"
                                                type="line"
                                                :options="monthlyTrendPaymentsOptions"
                                                :series="monthlyTrendPaymentsSeries" />
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template v-else>
                                <div class="kpi-chart-copy">
                                    <h5>{{ monthlyTrendTitle }}</h5>
                                    <p>{{ monthlyTrendDescription }}</p>
                                </div>
                                <apexchart
                                    :height="monthlyTrendChartHeight"
                                    type="line"
                                    :options="monthlyTrendOptions"
                                    :series="monthlyTrendSeries" />
                            </template>
                        </template>

                        <ChartEmptyState v-else icon="fa-solid fa-arrow-trend-up" title="Sin tendencia disponible">
                            No hay tendencia mensual disponible para el año seleccionado.
                        </ChartEmptyState>
                    </div>
                </div>
            </div>

            <div class="layout-spacing col-xl-5 col-lg-12 col-sm-12">
                <div class="panel br-6 h-100 kpi-chart-card kpi-chart-card--attendance" data-tour="kpi-attendance">
                    <div class="panel-body kpi-chart-card__body">
                        <template v-if="showAttendanceMixChart">
                            <div class="kpi-chart-copy">
                                <h5>Composición de asistencia del mes</h5>
                                <p>Distribución de asistencias, excusas, ausencias, retiros e incapacidades.</p>
                            </div>
                            <apexchart
                                :height="attendanceMixChartHeight"
                                type="donut"
                                :options="attendanceMixOptions"
                                :series="attendanceMixSeries" />
                        </template>
                        <ChartEmptyState v-else icon="fa-solid fa-chart-pie" title="Sin registros de asistencia">
                            No hay registros de asistencia para el mes seleccionado.
                        </ChartEmptyState>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, defineComponent, h, onBeforeUnmount, onMounted, ref } from 'vue'

import apexchart from 'vue3-apexcharts'

import { useAppState } from '@/store/app-state'

import KpiCollectionTable from './KpiCollectionTable.vue'
import { currencyFormatter, formatCompactCurrency, numberFormatter } from './kpiFormatters'

const props = defineProps({
    amountPaymentGroupReport: { type: Object, default: () => ({}) },
    attendanceMixReport: { type: Object, default: () => ({}) },
    canViewMonetaryValues: { type: Boolean, required: true },
    monthlyTrendReport: { type: Object, default: () => ({}) },
    paymentGroupReport: { type: Object, default: () => ({}) },
})

const ChartEmptyState = defineComponent({
    props: {
        icon: { type: String, required: true },
        title: { type: String, required: true },
    },
    setup(componentProps, { slots }) {
        return () => h('div', { class: 'kpi-empty-state text-center' }, [
            h('span', { class: 'kpi-empty-state__icon', 'aria-hidden': 'true' }, [
                h('i', { class: componentProps.icon }),
            ]),
            h('h6', { class: 'mb-1' }, componentProps.title),
            h('p', { class: 'text-muted mb-0' }, slots.default?.()),
        ])
    },
})

const appState = useAppState()
const COMPACT_CHART_BREAKPOINT = 992
const GROUP_CHART_BREAKPOINT = 1600
const GROUP_DENSITY_THRESHOLD = 8
const viewportWidth = ref(GROUP_CHART_BREAKPOINT + 1)
const isCompactChartLayout = computed(() => viewportWidth.value < COMPACT_CHART_BREAKPOINT)
const chartTheme = computed(() => (appState.is_dark_mode ? 'dark' : 'light'))

const syncCompactChartLayout = () => {
    if (typeof window !== 'undefined') {
        viewportWidth.value = window.innerWidth
    }
}

const abbreviateCategoryLabel = (label) => {
    const normalizedLabel = String(label ?? '')
    return normalizedLabel.length <= 4 ? normalizedLabel : normalizedLabel.slice(0, 3)
}
const formatGroupCount = (count) => `${count} ${count === 1 ? 'grupo' : 'grupos'}`

const hasMultiSeriesData = (series) => Array.isArray(series)
    && series.length > 0
    && series.some((item) => Array.isArray(item.data))
const hasSimpleSeriesData = (series) => Array.isArray(series)
    && series.some((value) => Number(value || 0) > 0)
const buildHorizontalChartHeight = (count, rowHeight = 72, minHeight = 320, maxHeight = 680) =>
    Math.min(maxHeight, Math.max(minHeight, count * rowHeight))

const paymentGroupSeries = computed(() => props.paymentGroupReport?.data ?? [])
const monthlyTrendSeries = computed(() => props.monthlyTrendReport?.data ?? [])
const attendanceMixSeries = computed(() => props.attendanceMixReport?.data ?? [])
const paymentGroupCategories = computed(() => props.paymentGroupReport?.categories ?? [])
const useHorizontalGroupLayout = computed(() => (
    viewportWidth.value <= GROUP_CHART_BREAKPOINT
    || paymentGroupCategories.value.length > GROUP_DENSITY_THRESHOLD
))
const monthlyTrendMode = computed(() => props.monthlyTrendReport?.mode ?? 'default')
const monthlyTrendCategories = computed(() => (
    isCompactChartLayout.value
        ? (props.monthlyTrendReport?.categories ?? []).map(abbreviateCategoryLabel)
        : (props.monthlyTrendReport?.categories ?? [])
))

const showPaymentGroupChart = computed(() => hasMultiSeriesData(paymentGroupSeries.value) && paymentGroupCategories.value.length > 0)
const showMonthlyTrendChart = computed(() => hasMultiSeriesData(monthlyTrendSeries.value) && (props.monthlyTrendReport?.categories?.length ?? 0) > 0)
const showAttendanceMixChart = computed(() => hasSimpleSeriesData(attendanceMixSeries.value))
const isPaymentsOnlyTrendChart = computed(() => monthlyTrendMode.value === 'payments_only')
const showSplitMonthlyTrend = computed(() => isCompactChartLayout.value && !isPaymentsOnlyTrendChart.value)
const paymentGroupTitle = computed(() => useHorizontalGroupLayout.value
    ? 'Mensualidades por grupo'
    : 'Mensualidades x grupo en el año')
const paymentGroupDescription = computed(() => useHorizontalGroupLayout.value
    ? 'Estados de las mensualidades por cada grupo.'
    : 'Contrasta mensualidades pagadas, deuda, becas y otros estados por grupo.')
const monthlyTrendTitle = computed(() => isPaymentsOnlyTrendChart.value
    ? 'Mensualidades pagadas del año'
    : 'Tendencia mensual del año')
const monthlyTrendDescription = computed(() => isPaymentsOnlyTrendChart.value
    ? 'Evolución de las mensualidades pagadas a lo largo del año.'
    : 'Evolución del valor recaudado y las mensualidades pagadas a lo largo del año.')

const paymentGroupChartHeight = computed(() => useHorizontalGroupLayout.value
    ? buildHorizontalChartHeight(paymentGroupCategories.value.length, 56, 340, 2400)
    : 320)
const monthlyTrendChartHeight = computed(() => isCompactChartLayout.value ? 360 : 320)
const monthlyTrendRevenueChartHeight = computed(() => isCompactChartLayout.value ? 300 : 320)
const monthlyTrendPaymentsChartHeight = computed(() => isCompactChartLayout.value ? 280 : 320)
const attendanceMixChartHeight = computed(() => isCompactChartLayout.value ? 380 : 320)

const monthlyTrendRevenueSeries = computed(() => monthlyTrendSeries.value
    .filter((series) => series.name !== 'Mensualidades pagadas')
    .map((series) => ({ ...series, type: 'bar' })))
const monthlyTrendPaymentsSeries = computed(() => monthlyTrendSeries.value
    .filter((series) => series.name === 'Mensualidades pagadas')
    .map((series) => ({ ...series, type: 'line' })))

const baseChart = computed(() => ({
    toolbar: { show: false },
    zoom: { enabled: false, allowMouseWheelZoom: false },
}))
const baseTheme = computed(() => ({ mode: chartTheme.value }))

const paymentGroupOptions = computed(() => ({
    chart: { ...baseChart.value, stacked: true },
    theme: baseTheme.value,
    plotOptions: {
        bar: {
            horizontal: useHorizontalGroupLayout.value,
            barHeight: useHorizontalGroupLayout.value ? '58%' : undefined,
            dataLabels: {
                total: {
                    enabled: !useHorizontalGroupLayout.value,
                    style: { fontSize: '13px', fontWeight: 900, color: '#8A8A8A' },
                },
            },
        },
    },
    xaxis: {
        categories: paymentGroupCategories.value,
        labels: { rotate: useHorizontalGroupLayout.value ? 0 : -35, trim: true, hideOverlappingLabels: true },
    },
    yaxis: useHorizontalGroupLayout.value
        ? { labels: { maxWidth: isCompactChartLayout.value ? 140 : 220, trim: false } }
        : undefined,
    fill: { opacity: 1 },
    legend: {
        position: useHorizontalGroupLayout.value ? 'bottom' : 'top',
        horizontalAlign: 'center',
        offsetY: 0,
        fontSize: useHorizontalGroupLayout.value ? '11px' : '12px',
    },
    grid: { padding: { left: useHorizontalGroupLayout.value ? 12 : 0, right: 8 } },
    tooltip: { theme: chartTheme.value },
    stroke: { width: 1 },
    colors: ['#00E396', '#FF4560', '#FEB019', '#546E7A'],
}))

const monthlyTrendOptions = computed(() => ({
    chart: baseChart.value,
    theme: baseTheme.value,
    plotOptions: { bar: { columnWidth: isCompactChartLayout.value ? '36%' : '52%' } },
    dataLabels: { enabled: false },
    stroke: {
        width: isPaymentsOnlyTrendChart.value
            ? [3]
            : (isCompactChartLayout.value ? [1, 3] : [1, 4]),
    },
    markers: { size: isCompactChartLayout.value ? 4 : 5 },
    xaxis: {
        categories: monthlyTrendCategories.value,
        labels: {
            rotate: 0,
            trim: true,
            hideOverlappingLabels: false,
            style: { fontSize: isCompactChartLayout.value ? '10px' : '12px' },
        },
    },
    yaxis: isPaymentsOnlyTrendChart.value
        ? {
            labels: { formatter: (value) => numberFormatter.format(Number(value || 0)) },
            title: { text: isCompactChartLayout.value ? '' : 'Mensualidades pagadas' },
        }
        : [
            {
                seriesName: 'Valor',
                labels: {
                    formatter: (value) => isCompactChartLayout.value
                        ? formatCompactCurrency(value)
                        : currencyFormatter.format(Number(value || 0)),
                },
                title: { text: isCompactChartLayout.value ? '' : 'Valor' },
            },
            {
                seriesName: 'Mensualidades pagadas',
                opposite: true,
                labels: { formatter: (value) => numberFormatter.format(Number(value || 0)) },
                title: { text: isCompactChartLayout.value ? '' : 'Mensualidades pagadas' },
            },
        ],
    legend: {
        position: isCompactChartLayout.value ? 'bottom' : 'top',
        horizontalAlign: 'center',
        fontSize: isCompactChartLayout.value ? '11px' : '12px',
    },
    grid: { padding: { left: 8, right: 8 } },
    tooltip: {
        theme: chartTheme.value,
        y: isPaymentsOnlyTrendChart.value
            ? { formatter: (value) => numberFormatter.format(Number(value || 0)) }
            : [
                { formatter: (value) => currencyFormatter.format(Number(value || 0)) },
                { formatter: (value) => numberFormatter.format(Number(value || 0)) },
            ],
    },
}))

const compactMonthlyTrendBase = () => ({
    chart: baseChart.value,
    theme: baseTheme.value,
    dataLabels: { enabled: false },
    xaxis: {
        categories: monthlyTrendCategories.value,
        labels: {
            rotate: 0,
            trim: false,
            hideOverlappingLabels: false,
            style: { fontSize: '10px' },
        },
        axisTicks: { show: false },
    },
    legend: { show: false },
    grid: { padding: { left: 0, right: 6 } },
})

const monthlyTrendRevenueOptions = computed(() => ({
    ...compactMonthlyTrendBase(),
    plotOptions: { bar: { columnWidth: '48%', borderRadius: 3 } },
    yaxis: {
        labels: { formatter: (value) => formatCompactCurrency(value) },
    },
    tooltip: {
        theme: chartTheme.value,
        y: { formatter: (value) => currencyFormatter.format(Number(value || 0)) },
    },
    colors: ['#4361EE'],
}))

const monthlyTrendPaymentsOptions = computed(() => ({
    ...compactMonthlyTrendBase(),
    stroke: { curve: 'smooth', width: 3 },
    markers: { size: 4, strokeWidth: 0 },
    yaxis: {
        min: 0,
        forceNiceScale: true,
        labels: { formatter: (value) => numberFormatter.format(Number(value || 0)) },
    },
    tooltip: {
        theme: chartTheme.value,
        y: { formatter: (value) => numberFormatter.format(Number(value || 0)) },
    },
    colors: ['#00A978'],
}))

const attendanceMixOptions = computed(() => ({
    chart: { toolbar: { show: false } },
    theme: baseTheme.value,
    labels: props.attendanceMixReport?.categories ?? [],
    legend: { position: 'bottom' },
    tooltip: {
        theme: chartTheme.value,
        y: { formatter: (value) => numberFormatter.format(Number(value || 0)) },
    },
    colors: ['#00E396', '#775DD0', '#FF4560', '#FEB019', '#546E7A'],
}))

onMounted(() => {
    syncCompactChartLayout()
    window.addEventListener('resize', syncCompactChartLayout)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', syncCompactChartLayout)
})
</script>
