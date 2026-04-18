<template>

    <div class="layout-px-spacing ">
        <div class="row layout-top-spacing">
            <div class="col-12">
                <div class="panel br-6" data-tour="kpi-toolbar">
                    <div class="panel-body d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h4 class="mb-1">Indicadores del backoffice</h4>
                            <p class="text-muted mb-0">
                                Este tablero resume pagos, recaudo y asistencia para lectura ejecutiva.
                            </p>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" @click="tutorial.start()">
                            Guia
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row layout-top-spacing">
            <div class="layout-spacing col-xl-6 col-lg-12 col-sm-12">
                <div class="panel br-6" data-tour="kpi-payment-groups">
                    <div class="panel-body">
                        <apexchart v-if="options_1" height="300" type="bar" :options="options_1" :series="series_1" />
                    </div>
                </div>
            </div>
            <div class="layout-spacing col-xl-6 col-lg-12 col-sm-12">
                <div class="panel br-6" data-tour="kpi-collection">
                    <div class="panel-body">
                        <apexchart v-if="options_2" height="300" type="line" :options="options_2" :series="series_2" />
                    </div>
                </div>
            </div>
            <div class="layout-spacing col-xl-3 col-lg-12 col-sm-12">
                <div class="panel br-6" data-tour="kpi-attendance">
                    <div class="panel-body">
                        <apexchart v-if="options_3" height="220" type="radialBar" :options="options_3"
                            :series="series_3" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <PageTutorialOverlay :tutorial="tutorial" />

</template>
<script setup>
import "@/assets/sass/widgets/widgets.scss";
import { computed, ref, onMounted } from "vue";
import apexchart from "vue3-apexcharts";
import { useAppState } from '@/store/app-state'
import api from "@/utils/axios";
import PageTutorialOverlay from '@/components/general/PageTutorialOverlay.vue'
import { usePageTutorial } from '@/composables/usePageTutorial'
import { kpiTutorial } from '@/tutorials/dashboard'

const appState = useAppState()
const is_dark = appState.is_dark_mode;
const tutorial = usePageTutorial(kpiTutorial)

const options_1 = ref({
    // chart: { stacked: true, toolbar: { show: false } },
    chart: { stacked: true, toolbar: { show: false }, zoom: {enabled: false, allowMouseWheelZoom: false} },
    responsive: [{ breakpoint: 480, options: { legend: { position: "bottom", offsetX: -10, offsetY: 5 } } }],
    title: { text: 'Mensualidades x Grupo en el año', align: 'left' },
    subtitle: { text: 'Sólo se muestran cantidades de estados pagados, deben y becados', align: 'left' },
    plotOptions: {
        bar: {
            horizontal: false,
            dataLabels: {
                total: {
                    enabled: true,
                    offsetX: 9,
                    style: { fontSize: '13px', fontWeight: 900, color: '#8A8A8A' },
                }
            }
        },
    },
    xaxis: { categories: [] },
    grid: { borderColor: "#e0e6ed" },
    fill: { opacity: 1, },
    legend: { position: "top", horizontalAlign: 'center', offsetY: 0 },
    tooltip: { theme: "dark" },
    stroke: { width: 1 },
    colors: ['#00E396', '#FF4560', '#FEB019', '#546E7A'],
})

const options_2 = ref({
    chart: { stacked: true, toolbar: { show: false }, zoom: {enabled: false, allowMouseWheelZoom: false} },
    dataLabels: { enabled: false },
    stroke: { width: [1, 1, 4] },
    title: { text: 'Recaudo de dinero', align: 'left', offsetX: 110 },
    xaxis: { categories: [] },
    yaxis: [
        {
            seriesName: 'Mensualidades',
            axisTicks: { show: true, },
            axisBorder: { show: true, color: '#008FFB' },
            labels: { style: { colors: '#008FFB', }, formatter: moneyFormat },
            title: { text: "Mensualidades", style: { color: '#008FFB', } },
            tooltip: { enabled: true }
        },
        {
            seriesName: 'Inscripción',
            opposite: true,
            axisTicks: { show: true, },
            axisBorder: { show: true, color: '#00E396' },
            labels: { style: { colors: '#00E396', }, formatter: moneyFormat },
            title: {
                text: "Inscripciónes", style: { color: '#00E396', }
            },
        },
        {
            seriesName: 'Porcentaje',
            opposite: true,
            axisTicks: { show: true, },
            axisBorder: { show: true, color: '#FEB019' },
            labels: { style: { colors: '#FEB019', }, formatter: (value) => `% ${value}` },
            title: { text: "% de cumplimiento pago", style: { color: '#FEB019', } }
        },
    ],
    tooltip: {
        theme: "dark",
        fixed: {
            enabled: true,
            position: 'topLeft', // topRight, topLeft, bottomRight, bottomLeft
            offsetY: 30,
            offsetX: 60
        },
    },
    legend: { position: "top", horizontalAlign: 'center', offsetY: 0 },
})

const options_3 = ref({
    chart: { toolbar: { show: false } },
    title: { text: 'Asistencias de los grupos en el mes actual', align: 'left', style: { fontWeight: 'normal' } },
    plotOptions: {
        radialBar: {
            offsetY: 0,
            startAngle: 0,
            endAngle: 270,
            hollow: {
                margin: 5,
                size: '30%',
                background: 'transparent',
                image: undefined,
            },
            dataLabels: {
                name: {
                    show: true,
                },
                value: {
                    show: false,
                }
            },
            barLabels: {
                enabled: true,
                useSeriesColors: true,
                offsetX: -8,
                fontSize: '12px',
                formatter: function (seriesName, opts) {
                    return seriesName + ":  " + opts.w.globals.series[opts.seriesIndex]
                },
            },
        },
    },
    labels: [],
    responsive: [{
        breakpoint: 480,
        options: {
            legend: {
                show: false
            }
        }
    }]
})

const series_1 = ref([])
const series_2 = ref([])
const series_3 = ref([])


const loadDataCharts = async () => {

    const response = await api.get('/api/v2/kpis')

    if (response?.data) {
        const chartOne = response.data.payment_group_report
        const chartTwo = response.data.amount_payment_group_report
        const chartThree = response.data.assist_report

        if (chartOne) {
            options_1.value = {
                ...options_1.value,
                xaxis: {
                    categories: chartOne.categories,
                },
            }
            series_1.value = chartOne.data
        }
        if (chartTwo) {
            options_2.value = {
                ...options_2.value,
                xaxis: {
                    categories: chartTwo.categories,
                },
            }
            series_2.value = chartTwo.data
        }
        if (chartThree) {
            options_3.value = {
                ...options_3.value,
                labels: chartThree.categories,
            }
            series_3.value = chartThree.data
        }

    }

}


onMounted(async () => {
    loadDataCharts()
})


//Radial
const series_8 = ref([135, 0, 21, 6, 0]);
//Radial
const options_8 = computed(() => {
    return {

    };
});
</script>
