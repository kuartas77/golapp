<template>
    <div class="layout-px-spacing">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="panel br-6" data-tour="kpi-toolbar">
                    <div class="panel-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <p class="text-primary text-uppercase fw-semibold small mb-2">
                                Panel ejecutivo
                            </p>
                            <h4 class="mb-1">Indicadores del backoffice</h4>
                            <p class="text-muted mb-0">
                                Consolida recaudo, cumplimiento, asistencia y alertas operativas sin salir de la portada.
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <span v-if="isLoading && isReady" class="text-muted small">
                                Actualizando indicadores...
                            </span>
                            <button type="button" class="btn btn-info btn-sm" @click="tutorial.start()">
                                <i class="fa-regular fa-circle-question me-2"></i>
                                Guía
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="panel br-6" data-tour="kpi-filters">
                    <div class="panel-body">
                        <div v-if="isLoading && !isReady" class="py-5 text-center text-muted">
                            Cargando configuración del tablero...
                        </div>

                        <template v-else-if="isReady">
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-md-3">
                                    <label class="form-label" for="kpi-year">Año</label>
                                    <CustomSelect2
                                        id="kpi-year"
                                        v-model="filters.year"
                                        :options="years"
                                        placeholder="Selecciona un año" />
                                </div>
                                <div class="col-12 col-md-3">
                                    <label class="form-label" for="kpi-month">Mes</label>
                                    <CustomSelect2
                                        id="kpi-month"
                                        v-model="filters.month"
                                        :options="months"
                                        placeholder="Selecciona un mes" />
                                </div>
                                <div class="col-12 col-md-4">
                                    <label class="form-label" for="kpi-group">Grupo</label>
                                    <CustomSelect2
                                        id="kpi-group"
                                        v-model="filters.training_group_id"
                                        :options="groupOptions"
                                        placeholder="Todos los grupos" />
                                </div>
                                <div class="col-12 col-md-2">
                                    <button
                                        type="button"
                                        class="btn btn-primary w-100"
                                        :disabled="isLoading"
                                        @click="applyFilters">
                                        {{ isLoading ? 'Actualizando...' : 'Actualizar' }}
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mt-3">
                                <small class="text-muted">
                                    Corte actual: {{ selectedMonthLabel }} de {{ filters.year || '—' }}
                                </small>
                                <button
                                    v-if="hasActiveFilters"
                                    type="button"
                                    class="btn btn-outline-secondary btn-sm"
                                    @click="resetFilters">
                                    Limpiar filtros
                                </button>
                            </div>
                        </template>

                        <div v-else class="alert alert-danger mb-0">
                            {{ loadError || 'No fue posible cargar la configuración del tablero.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="loadError && isReady" class="row layout-top-spacing">
            <div class="col-12">
                <div class="alert alert-warning mb-0">
                    {{ loadError }}
                </div>
            </div>
        </div>

        <div v-if="isReady" class="row layout-top-spacing" data-tour="kpi-summary">
            <div v-for="card in summaryCards" :key="card.key" class="col-12 col-sm-6 col-xl-4">
                <div class="panel br-6 h-100">
                    <div class="panel-body d-flex flex-column h-100">
                        <span class="text-muted small text-uppercase fw-semibold mb-2">
                            {{ card.label }}
                        </span>
                        <h4 class="mb-2">{{ formatMetricValue(card.value, card.format) }}</h4>
                        <small class="text-muted mt-auto">{{ card.helper }}</small>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="isReady && canOpenReports" class="row layout-top-spacing">
            <div class="col-12">
                <div class="panel br-6">
                    <div class="panel-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h5 class="mb-1">Bajar al detalle</h5>
                            <p class="text-muted mb-0">
                                Abre los reportes relacionados con este mismo corte para investigar variaciones y alertas.
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <router-link
                                v-if="reportLinks.assists"
                                :to="reportLinks.assists"
                                class="btn btn-outline-primary btn-sm">
                                Asistencias
                            </router-link>
                            <router-link
                                v-if="reportLinks.payments"
                                :to="reportLinks.payments"
                                class="btn btn-outline-primary btn-sm">
                                Pagos
                            </router-link>
                            <router-link
                                v-if="reportLinks.attendance_payment"
                                :to="reportLinks.attendance_payment"
                                class="btn btn-outline-primary btn-sm">
                                Mensualidades vs asistencias
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template v-if="isReady">
            <div class="row layout-top-spacing">
                <div class="layout-spacing col-xl-6 col-lg-12 col-sm-12">
                    <div class="panel br-6 h-100" data-tour="kpi-payment-groups">
                        <div class="panel-body">
                            <apexchart
                                v-if="showPaymentGroupChart"
                                height="320"
                                type="bar"
                                :options="paymentGroupOptions"
                                :series="paymentGroupSeries" />
                            <div v-else class="py-5 text-center text-muted">
                                No hay datos de mensualidades para el período seleccionado.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="layout-spacing col-xl-6 col-lg-12 col-sm-12">
                    <div class="panel br-6 h-100" data-tour="kpi-collection">
                        <div class="panel-body">
                            <apexchart
                                v-if="showAmountCollectionChart"
                                height="320"
                                type="line"
                                :options="amountCollectionOptions"
                                :series="amountCollectionSeries" />
                            <div v-else class="py-5 text-center text-muted">
                                No hay datos de recaudo para el período seleccionado.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="layout-spacing col-xl-7 col-lg-12 col-sm-12">
                    <div class="panel br-6 h-100" data-tour="kpi-monthly-trend">
                        <div class="panel-body">
                            <apexchart
                                v-if="showMonthlyTrendChart"
                                height="320"
                                type="line"
                                :options="monthlyTrendOptions"
                                :series="monthlyTrendSeries" />
                            <div v-else class="py-5 text-center text-muted">
                                No hay tendencia mensual disponible para el año seleccionado.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="layout-spacing col-xl-5 col-lg-12 col-sm-12">
                    <div class="panel br-6 h-100" data-tour="kpi-attendance">
                        <div class="panel-body">
                            <apexchart
                                v-if="showAttendanceMixChart"
                                height="320"
                                type="donut"
                                :options="attendanceMixOptions"
                                :series="attendanceMixSeries" />
                            <div v-else class="py-5 text-center text-muted">
                                No hay registros de asistencia para el mes seleccionado.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row layout-top-spacing" data-tour="kpi-rankings">
                <div
                    v-for="section in rankingSections"
                    :key="section.key"
                    class="col-12 col-xl-3 col-md-6">
                    <div class="panel br-6 h-100">
                        <div class="panel-body">
                            <h5 class="mb-1">{{ section.title }}</h5>
                            <p class="text-muted small mb-3">{{ section.description }}</p>

                            <div v-if="section.items.length === 0" class="text-muted small py-3">
                                No hay grupos suficientes para rankear este indicador.
                            </div>

                            <div v-else class="d-flex flex-column gap-3">
                                <div
                                    v-for="(item, index) in section.items"
                                    :key="`${section.key}-${item.training_group_id}`"
                                    :class="[
                                        'd-flex justify-content-between align-items-start gap-3',
                                        index === 0 ? '' : 'border-top pt-3',
                                    ]">
                                    <span class="small">{{ item.label }}</span>
                                    <strong class="text-nowrap">{{ formatMetricValue(item.value, item.format) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <PageTutorialOverlay :tutorial="tutorial" />
</template>

<script>
export default {
    name: 'kpi-dashboard-index',
}
</script>

<script setup>
import { computed } from 'vue'

import apexchart from 'vue3-apexcharts'

import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import useKpiDashboard from '@/composables/kpi/useKpiDashboard'

import '@/assets/sass/widgets/widgets.scss'

const {
    amountPaymentGroupReport,
    applyFilters,
    attendanceMixReport,
    canOpenReports,
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
} = useKpiDashboard()

const currencyFormatter = new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
})

const numberFormatter = new Intl.NumberFormat('es-CO')

const formatMetricValue = (value, format = 'number') => {
    const normalizedValue = Number(value ?? 0)

    if (format === 'currency') {
        return currencyFormatter.format(normalizedValue)
    }

    if (format === 'percentage') {
        return `${normalizedValue.toFixed(2)}%`
    }

    return numberFormatter.format(normalizedValue)
}

const hasMultiSeriesData = (series) => Array.isArray(series) && series.length > 0 && series.some((item) => Array.isArray(item.data))
const hasSimpleSeriesData = (series) => Array.isArray(series) && series.some((value) => Number(value || 0) > 0)

const selectedMonthLabel = computed(() =>
    months.value.find((monthOption) => monthOption.value === filters.month)?.label || 'Mes'
)

const paymentGroupSeries = computed(() => paymentGroupReport.value?.data ?? [])
const amountCollectionSeries = computed(() => amountPaymentGroupReport.value?.data ?? [])
const monthlyTrendSeries = computed(() => monthlyTrendReport.value?.data ?? [])
const attendanceMixSeries = computed(() => attendanceMixReport.value?.data ?? [])

const showPaymentGroupChart = computed(() => hasMultiSeriesData(paymentGroupSeries.value) && (paymentGroupReport.value?.categories?.length ?? 0) > 0)
const showAmountCollectionChart = computed(() => hasMultiSeriesData(amountCollectionSeries.value) && (amountPaymentGroupReport.value?.categories?.length ?? 0) > 0)
const showMonthlyTrendChart = computed(() => hasMultiSeriesData(monthlyTrendSeries.value) && (monthlyTrendReport.value?.categories?.length ?? 0) > 0)
const showAttendanceMixChart = computed(() => hasSimpleSeriesData(attendanceMixSeries.value))

const paymentGroupOptions = computed(() => ({
    chart: {
        stacked: true,
        toolbar: { show: false },
        zoom: { enabled: false, allowMouseWheelZoom: false },
    },
    responsive: [{ breakpoint: 480, options: { legend: { position: 'bottom', offsetX: -10, offsetY: 5 } } }],
    title: { text: 'Mensualidades x grupo en el año', align: 'left' },
    subtitle: { text: 'Contrasta pagos, deuda, becados y otros estados por grupo.', align: 'left' },
    plotOptions: {
        bar: {
            horizontal: false,
            dataLabels: {
                total: {
                    enabled: true,
                    style: { fontSize: '13px', fontWeight: 900, color: '#8A8A8A' },
                },
            },
        },
    },
    xaxis: {
        categories: paymentGroupReport.value?.categories ?? [],
    },
    fill: { opacity: 1 },
    legend: { position: 'top', horizontalAlign: 'center', offsetY: 0 },
    tooltip: {},
    stroke: { width: 1 },
    colors: ['#00E396', '#FF4560', '#FEB019', '#546E7A'],
}))

const amountCollectionOptions = computed(() => ({
    chart: {
        stacked: false,
        toolbar: { show: false },
        zoom: { enabled: false, allowMouseWheelZoom: false },
    },
    dataLabels: { enabled: false },
    stroke: { width: [1, 1, 4] },
    title: { text: 'Recaudo y cumplimiento por grupo', align: 'left' },
    subtitle: { text: 'Cruza recaudo de mensualidades, inscripciones y % de cumplimiento.', align: 'left' },
    xaxis: {
        categories: amountPaymentGroupReport.value?.categories ?? [],
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
    tooltip: {},
    legend: { position: 'top', horizontalAlign: 'center', offsetY: 0 },
}))

const monthlyTrendOptions = computed(() => ({
    chart: {
        toolbar: { show: false },
        zoom: { enabled: false, allowMouseWheelZoom: false },
    },
    dataLabels: { enabled: false },
    stroke: { width: [1, 4] },
    title: { text: 'Tendencia mensual del año', align: 'left' },
    subtitle: { text: 'Sigue la evolución del valor recaudado y los pagos efectivos a lo largo del año.', align: 'left' },
    xaxis: {
        categories: monthlyTrendReport.value?.categories ?? [],
    },
    yaxis: [
        {
            seriesName: 'Valor',
            labels: {
                formatter: (value) => currencyFormatter.format(Number(value || 0)),
            },
            title: { text: 'Valor' },
        },
        {
            seriesName: 'Pagos',
            opposite: true,
            labels: {
                formatter: (value) => numberFormatter.format(Number(value || 0)),
            },
            title: { text: 'Pagos' },
        },
    ],
    legend: { position: 'top', horizontalAlign: 'center' },
    tooltip: {},
}))

const attendanceMixOptions = computed(() => ({
    chart: {
        toolbar: { show: false },
    },
    labels: attendanceMixReport.value?.categories ?? [],
    title: { text: 'Composición de asistencia del mes', align: 'left' },
    subtitle: { text: 'Resume cómo se distribuyen asistencias, excusas, ausencias, retiros e incapacidades.', align: 'left' },
    legend: {
        position: 'bottom',
    },
    tooltip: {
        y: {
            formatter: (value) => numberFormatter.format(Number(value || 0)),
        },
    },
    colors: ['#00E396', '#775DD0', '#FF4560', '#FEB019', '#546E7A'],
}))

const rankingSections = computed(() => ([
    {
        key: 'compliance',
        title: 'Mejor cumplimiento',
        description: 'Grupos con mejor porcentaje acumulado de cumplimiento.',
        items: rankings.value?.compliance ?? [],
    },
    {
        key: 'low_attendance',
        title: 'Menor asistencia',
        description: 'Grupos que requieren atención por su porcentaje del mes.',
        items: rankings.value?.low_attendance ?? [],
    },
    {
        key: 'debt',
        title: 'Mayor deuda',
        description: 'Grupos con más mensualidades en estado de deuda.',
        items: rankings.value?.debt ?? [],
    },
    {
        key: 'flagged',
        title: 'Más observados',
        description: 'Grupos con más deportistas observados en el cruce pago vs asistencia.',
        items: rankings.value?.flagged ?? [],
    },
]))
</script>
