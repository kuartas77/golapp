<template>
    <section>
        <div class="kpi-content-heading mb-3">
            <p class="kpi-eyebrow mb-1">Comportamiento</p>
            <h4 class="mb-1">Análisis visual</h4>
            <p class="text-muted mb-0">Compara grupos, recaudo, tendencias y asistencia en el período seleccionado.</p>
        </div>

        <div class="row layout-top-spacing">
            <div class="layout-spacing col-xl-6 col-lg-12 col-sm-12">
                <div class="panel br-6 h-100 kpi-chart-card" data-tour="kpi-payment-groups">
                    <div class="panel-body kpi-chart-card__body">
                        <template v-if="showPaymentGroupChart">
                            <div class="kpi-chart-copy">
                                <h5>{{ paymentGroupTitle }}</h5>
                                <p>{{ paymentGroupDescription }}</p>
                            </div>
                            <div class="kpi-chart-scroll">
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

            <div class="layout-spacing col-xl-6 col-lg-12 col-sm-12">
                <div class="panel br-6 h-100 kpi-chart-card" data-tour="kpi-collection">
                    <div class="panel-body kpi-chart-card__body d-flex flex-column gap-4">
                        <template v-if="showAmountCollectionChart">
                            <template v-if="isComplianceOnlyCollectionChart">
                                <div class="kpi-chart-copy">
                                    <h5>Cumplimiento por grupo</h5>
                                    <p>Lectura aislada del cumplimiento para que se entienda rápido.</p>
                                </div>
                                <div class="kpi-chart-scroll">
                                    <div class="kpi-chart-scroll__canvas">
                                        <apexchart
                                            :height="amountCollectionSingleChartHeight"
                                            type="bar"
                                            :options="amountCollectionComplianceOptions"
                                            :series="amountCollectionSeries" />
                                    </div>
                                </div>
                            </template>

                            <template v-else-if="isCompactChartLayout">
                                <div class="kpi-chart-copy">
                                    <h5>Recaudo por grupo</h5>
                                    <p>Montos de mensualidades e inscripciones por cada grupo.</p>
                                </div>
                                <div class="kpi-chart-scroll">
                                    <div class="kpi-chart-scroll__canvas">
                                        <apexchart
                                            :height="amountCollectionRevenueChartHeight"
                                            type="bar"
                                            :options="amountCollectionRevenueOptions"
                                            :series="amountCollectionRevenueSeries" />
                                    </div>
                                </div>

                                <div class="kpi-chart-divider pt-4">
                                    <div class="kpi-chart-copy">
                                        <h5>% cumplimiento por grupo</h5>
                                        <p>Porcentaje de cumplimiento separado de los montos.</p>
                                    </div>
                                    <div class="kpi-chart-scroll">
                                        <div class="kpi-chart-scroll__canvas">
                                            <apexchart
                                                :height="amountCollectionComplianceChartHeight"
                                                type="bar"
                                                :options="amountCollectionComplianceOptions"
                                                :series="amountCollectionComplianceSeries" />
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template v-else>
                                <div class="kpi-chart-copy">
                                    <h5>Recaudo y cumplimiento por grupo</h5>
                                    <p>Cruza recaudo de mensualidades, inscripciones y porcentaje de cumplimiento.</p>
                                </div>
                                <apexchart
                                    height="320"
                                    type="line"
                                    :options="amountCollectionOptions"
                                    :series="amountCollectionSeries" />
                            </template>
                        </template>

                        <ChartEmptyState v-else icon="fa-solid fa-chart-simple" title="Sin datos de recaudo">
                            No hay datos de recaudo para el período seleccionado.
                        </ChartEmptyState>
                    </div>
                </div>
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
                <div class="panel br-6 h-100 kpi-chart-card" data-tour="kpi-attendance">
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
const isCompactChartLayout = ref(false)
const chartTheme = computed(() => (appState.is_dark_mode ? 'dark' : 'light'))

const syncCompactChartLayout = () => {
    if (typeof window !== 'undefined') {
        isCompactChartLayout.value = window.innerWidth < COMPACT_CHART_BREAKPOINT
    }
}

const abbreviateCategoryLabel = (label) => {
    const normalizedLabel = String(label ?? '')
    return normalizedLabel.length <= 4 ? normalizedLabel : normalizedLabel.slice(0, 3)
}

const hasMultiSeriesData = (series) => Array.isArray(series)
    && series.length > 0
    && series.some((item) => Array.isArray(item.data))
const hasSimpleSeriesData = (series) => Array.isArray(series)
    && series.some((value) => Number(value || 0) > 0)
const buildHorizontalChartHeight = (count, rowHeight = 72, minHeight = 320, maxHeight = 680) =>
    Math.min(maxHeight, Math.max(minHeight, count * rowHeight))

const paymentGroupSeries = computed(() => props.paymentGroupReport?.data ?? [])
const amountCollectionSeries = computed(() => props.amountPaymentGroupReport?.data ?? [])
const monthlyTrendSeries = computed(() => props.monthlyTrendReport?.data ?? [])
const attendanceMixSeries = computed(() => props.attendanceMixReport?.data ?? [])
const paymentGroupCategories = computed(() => props.paymentGroupReport?.categories ?? [])
const amountCollectionCategories = computed(() => props.amountPaymentGroupReport?.categories ?? [])
const amountCollectionMode = computed(() => props.amountPaymentGroupReport?.mode ?? 'default')
const monthlyTrendMode = computed(() => props.monthlyTrendReport?.mode ?? 'default')
const monthlyTrendCategories = computed(() => (
    isCompactChartLayout.value
        ? (props.monthlyTrendReport?.categories ?? []).map(abbreviateCategoryLabel)
        : (props.monthlyTrendReport?.categories ?? [])
))

const showPaymentGroupChart = computed(() => hasMultiSeriesData(paymentGroupSeries.value) && paymentGroupCategories.value.length > 0)
const showAmountCollectionChart = computed(() => hasMultiSeriesData(amountCollectionSeries.value) && amountCollectionCategories.value.length > 0)
const showMonthlyTrendChart = computed(() => hasMultiSeriesData(monthlyTrendSeries.value) && (props.monthlyTrendReport?.categories?.length ?? 0) > 0)
const showAttendanceMixChart = computed(() => hasSimpleSeriesData(attendanceMixSeries.value))
const isComplianceOnlyCollectionChart = computed(() => amountCollectionMode.value === 'compliance_only')
const isPaymentsOnlyTrendChart = computed(() => monthlyTrendMode.value === 'payments_only')
const showSplitMonthlyTrend = computed(() => isCompactChartLayout.value && !isPaymentsOnlyTrendChart.value)
const paymentGroupTitle = computed(() => isCompactChartLayout.value
    ? 'Mensualidades por grupo'
    : 'Mensualidades x grupo en el año')
const paymentGroupDescription = computed(() => isCompactChartLayout.value
    ? 'Estados de las mensualidades por cada grupo.'
    : 'Contrasta mensualidades pagadas, deuda, becas y otros estados por grupo.')
const monthlyTrendTitle = computed(() => isPaymentsOnlyTrendChart.value
    ? 'Mensualidades pagadas del año'
    : 'Tendencia mensual del año')
const monthlyTrendDescription = computed(() => isPaymentsOnlyTrendChart.value
    ? 'Evolución de las mensualidades pagadas a lo largo del año.'
    : 'Evolución del valor recaudado y las mensualidades pagadas a lo largo del año.')

const paymentGroupChartHeight = computed(() => isCompactChartLayout.value
    ? buildHorizontalChartHeight(paymentGroupCategories.value.length, 76, 340, 720)
    : 320)
const amountCollectionRevenueChartHeight = computed(() =>
    buildHorizontalChartHeight(amountCollectionCategories.value.length, 74, 300, 680))
const amountCollectionComplianceChartHeight = computed(() =>
    buildHorizontalChartHeight(amountCollectionCategories.value.length, 66, 260, 620))
const amountCollectionSingleChartHeight = computed(() => isCompactChartLayout.value
    ? buildHorizontalChartHeight(amountCollectionCategories.value.length, 66, 260, 620)
    : 320)
const monthlyTrendChartHeight = computed(() => isCompactChartLayout.value ? 360 : 320)
const monthlyTrendRevenueChartHeight = computed(() => isCompactChartLayout.value ? 300 : 320)
const monthlyTrendPaymentsChartHeight = computed(() => isCompactChartLayout.value ? 280 : 320)
const attendanceMixChartHeight = computed(() => isCompactChartLayout.value ? 380 : 320)

const amountCollectionRevenueSeries = computed(() => amountCollectionSeries.value
    .filter((series) => series.name !== '% de cumplimiento')
    .map((series) => ({ ...series, type: 'bar' })))
const amountCollectionComplianceSeries = computed(() => {
    const series = amountCollectionSeries.value.find((item) => item.name === '% de cumplimiento')
    return series ? [{ ...series, type: 'bar' }] : []
})
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
            horizontal: isCompactChartLayout.value,
            barHeight: isCompactChartLayout.value ? '58%' : undefined,
            dataLabels: {
                total: {
                    enabled: !isCompactChartLayout.value,
                    style: { fontSize: '13px', fontWeight: 900, color: '#8A8A8A' },
                },
            },
        },
    },
    xaxis: {
        categories: paymentGroupCategories.value,
        labels: { rotate: isCompactChartLayout.value ? 0 : -35, trim: true, hideOverlappingLabels: true },
    },
    yaxis: isCompactChartLayout.value ? { labels: { maxWidth: 150, trim: true } } : undefined,
    fill: { opacity: 1 },
    legend: {
        position: isCompactChartLayout.value ? 'bottom' : 'top',
        horizontalAlign: 'center',
        offsetY: 0,
        fontSize: isCompactChartLayout.value ? '11px' : '12px',
    },
    grid: { padding: { left: isCompactChartLayout.value ? 12 : 0, right: 8 } },
    tooltip: { theme: chartTheme.value },
    stroke: { width: 1 },
    colors: ['#00E396', '#FF4560', '#FEB019', '#546E7A'],
}))

const amountCollectionOptions = computed(() => ({
    chart: { ...baseChart.value, stacked: false },
    theme: baseTheme.value,
    dataLabels: { enabled: false },
    stroke: { width: [1, 1, 4] },
    xaxis: {
        categories: amountCollectionCategories.value,
        labels: { rotate: -35, trim: true, hideOverlappingLabels: true },
    },
    yaxis: [
        {
            seriesName: 'Mensualidades',
            axisTicks: { show: true },
            axisBorder: { show: true, color: '#008FFB' },
            labels: { style: { colors: '#008FFB' }, formatter: (value) => currencyFormatter.format(Number(value || 0)) },
            title: { text: 'Mensualidades', style: { color: '#008FFB' } },
        },
        {
            seriesName: 'Inscripciones',
            opposite: true,
            axisTicks: { show: true },
            axisBorder: { show: true, color: '#00E396' },
            labels: { style: { colors: '#00E396' }, formatter: (value) => currencyFormatter.format(Number(value || 0)) },
            title: { text: 'Inscripciones', style: { color: '#00E396' } },
        },
        {
            seriesName: '% de cumplimiento',
            opposite: true,
            axisTicks: { show: true },
            axisBorder: { show: true, color: '#FEB019' },
            labels: { style: { colors: '#FEB019' }, formatter: (value) => `${Number(value || 0).toFixed(2)}%` },
            title: { text: '% cumplimiento', style: { color: '#FEB019' } },
        },
    ],
    tooltip: { theme: chartTheme.value },
    legend: { position: 'top', horizontalAlign: 'center', offsetY: 0 },
}))

const horizontalBarOptions = (isPercentage = false) => ({
    chart: baseChart.value,
    theme: baseTheme.value,
    plotOptions: {
        bar: {
            horizontal: true,
            barHeight: isPercentage ? '45%' : '55%',
            distributed: isPercentage,
        },
    },
    dataLabels: isPercentage
        ? { enabled: true, formatter: (value) => `${Number(value || 0).toFixed(0)}%` }
        : { enabled: false },
    xaxis: {
        categories: amountCollectionCategories.value,
        ...(isPercentage ? { min: 0, max: 100 } : {}),
        labels: {
            formatter: (value) => isPercentage
                ? `${Number(value || 0).toFixed(0)}%`
                : currencyFormatter.format(Number(value || 0)),
            rotate: -45,
            trim: true,
            hideOverlappingLabels: true,
        },
    },
    yaxis: { labels: { maxWidth: 150, trim: true } },
    legend: isPercentage
        ? { show: false }
        : { position: 'bottom', horizontalAlign: 'center', fontSize: '11px' },
    tooltip: {
        theme: chartTheme.value,
        y: {
            formatter: (value) => isPercentage
                ? `${Number(value || 0).toFixed(2)}%`
                : currencyFormatter.format(Number(value || 0)),
        },
    },
    grid: { padding: { left: 12, right: 8 } },
    colors: isPercentage ? ['#FEB019'] : ['#008FFB', '#00E396'],
})

const amountCollectionRevenueOptions = computed(() => horizontalBarOptions())
const amountCollectionComplianceOptions = computed(() => horizontalBarOptions(true))

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
