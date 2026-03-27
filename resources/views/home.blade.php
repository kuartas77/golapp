@extends('layouts.app')
@section('title', 'Inicio')
@section('content')
<x-bread-crumb title="Inicio" :option="1" :birthdays="$birthdays" />
<div class="row no-gutters">

    {{--<div class="col-lg-6 col-md-12 col-sm-12">
        <div class="card ">
            <div class="card-body ">
                <h4 class="text-themecolor card-subtitle">Pagos Año {{now()->format('Y')}}</h4>
                <div class="col-md-12">
                    <div class="chart-payments amp-pxl chartist-chart"></div>
                    <div class="text-center">
                        <ul class="list-inline">
                            <li>
                                <a href="{{route('payments.status', ['status' => '1'])}}" class="" style="color: #1e88e5;">
                                    <i class="fa fa-circle font-10 m-r-10 "></i>Pagaron (<span id="now_year_payment"></span>)
                                </a>
                            </li>
                            <li>
                                <a href="{{route('payments.status', ['status' => '2'])}}" class="" style="color: red;">
                                    <i class="fa fa-circle font-10 m-r-10"></i>Deben (<span id="now_year_due"></span>)
                                </a>
                            </li>
                            <li>
                                <a href="{{route('payments.status', ['status' => '8'])}}" class="" style="color: #ffb22b;">
                                    <i class="fa fa-circle font-10 m-r-10"></i>Becados (<span id="now_year_scholarship"></span>)
                                </a>
                            </li>
                            <li>
                                <a href="{{route('payments.status', ['status' => '0'])}}" class="" style="color: #26c6da;">
                                    <i class="fa fa-circle font-10 m-r-10"></i>Pendientes (<span id="now_year_pending"></span>)
                                </a>
                            </li>
                        </ul>
                        <h4 class="text-themecolor card-subtitle">Totales, sumando todos los meses</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>--}}

    @hasanyrole('super-admin|school')
    <div class="col-lg-6 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    <div id="chart"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    <div id="chart_two"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-3 col-sm-12">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    <div id="chart_three"></div>
                </div>
            </div>
        </div>
    </div>
    @endhasanyrole

</div>
@endsection
@section('modals')
@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script type="text/javascript">
    let urlCurrent = "{{URL::current()}}";
    $(document).ready(function() {

        let optionsOne = {
            chart: { stacked: false, toolbar: { show: false }, zoom: {enabled: false, allowMouseWheelZoom: false}, height: 400 },
            responsive: [{ breakpoint: 480, options: { legend: { position: "bottom", offsetX: -10, offsetY: 5 } } }],
            dataLabels: { enabled: false },
            stroke: { width: [1, 1, 4] },
            title: { text: 'Recaudo de dinero', align: 'left', offsetX: 110 },
            xaxis: { categories: [] },
            yaxis: [
                {
                    seriesName: 'Mensualidad',
                    axisTicks: { show: true, },
                    axisBorder: { show: true, color: '#008FFB' },
                    labels: { style: { colors: '#008FFB', }, formatter: formatMoney },
                    title: { text: "Mensualidades", style: { color: '#008FFB', } },
                    tooltip: { enabled: true }
                },
                {
                    seriesName: 'Inscripción',
                    opposite: true,
                    axisTicks: { show: true, },
                    axisBorder: { show: true, color: '#00E396' },
                    labels: { style: { colors: '#00E396', }, formatter: formatMoney },
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
            tooltip: { theme: "dark",
                fixed: {
                    enabled: true,
                    position: 'topLeft', // topRight, topLeft, bottomRight, bottomLeft
                    offsetY: 30,
                    offsetX: 60
                },
            },
            legend: { position: "top", horizontalAlign: 'center', offsetY: 0 },
        }

        let optionsTwo = {
            chart: { type: 'bar', stacked: true, toolbar: { show: false }, zoom: {enabled: false, allowMouseWheelZoom: false}, height: 400 },
            responsive: [{ breakpoint: 480, options: { legend: { position: "bottom", offsetX: -10, offsetY: 5 } } }],
            title: { text: 'Mensualidades x Grupo en el año.', align: 'left' },
            subtitle: { text: 'Sólo se muestran cantidades de pagos, deben y becados', align: 'left' },
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
            tooltip: { theme: "dark",
                fixed: {
                    enabled: true,
                    position: 'topLeft', // topRight, topLeft, bottomRight, bottomLeft
                    offsetY: 30,
                    offsetX: 60
                },
            },
            legend: { position: "top", horizontalAlign: 'center', offsetY: 0 },
            stroke: { width: 2 },
            colors: ['#00E396', '#FF4560', '#FEB019', '#546E7A'],
        }

        let optionsThree = {
            chart: {type: 'radialBar', stacked: false, toolbar: { show: false }, zoom: {enabled: false, allowMouseWheelZoom: false}, height: 250 },
            title: { text: 'Asistencias en el mes actual', align: 'left', style: { fontWeight: 'normal' } },
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
            }

        $.get(urlCurrent, function({
            current,
            past,
            other
        }) {
            const chartOne = other.amount_payment_group_report
            const chartTwo = other.payment_group_report
            const chartThree = other.assist_report
            const chartFour = other.monthly_report

            optionsOne = {
                ...optionsOne,
                xaxis: {
                    categories: chartOne.categories,
                },
                series: chartOne.data
            }

            optionsTwo = {
                ...optionsTwo,
                xaxis: {
                    categories: chartTwo.categories,
                },
                series: chartTwo.data
            }

            optionsThree = {
                ...optionsThree,
                labels: chartThree.categories,
                series: chartThree.data
            }

            var chartyOne = new ApexCharts(document.querySelector("#chart"), optionsOne)
            chartyOne.render()

            var chartyTwo = new ApexCharts(document.querySelector("#chart_two"), optionsTwo)
            chartyTwo.render()
            var chartyThree = new ApexCharts(document.querySelector("#chart_three"), optionsThree)
            chartyThree.render()

        });
    });
</script>
@endsection