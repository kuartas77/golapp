@extends('layouts.app')
@section('title', 'Inicio')
@section('content')
<x-bread-crumb title="Inicio" :option="1" :birthdays="$birthdays"/>
<div class="row no-gutters">

    <div class="col-lg-6 col-md-12 col-sm-12">
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
    </div>

    <div class="col-lg-6 col-md-12 col-sm-12">
        <div class="card">
            <div class="card-body">
                <h4 class="text-themecolor card-subtitle">Pagos Año {{now()->subYear()->format('Y')}}</h4>
                <div class="col-md-12">
                    <div class="chart-payments-old amp-pxl chartist-chart"></div>
                    <div class="text-center">
                        <ul class="list-inline">
                            <li>
                                <a href="javascript:void(0);" class="" style="color: #1e88e5;">
                                    <i class="fa fa-circle font-10 m-r-10 "></i>Pagaron (<span id="past_year_payment"></span>)
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="" style="color: red;">
                                    <i class="fa fa-circle font-10 m-r-10"></i>Deben (<span id="past_year_due"></span>)
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="" style="color: #ffb22b;">
                                    <i class="fa fa-circle font-10 m-r-10"></i>Becados (<span id="past_year_scholarship"></span>)
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0);" class="" style="color: #26c6da;">
                                    <i class="fa fa-circle font-10 m-r-10"></i>Pendientes (<span id="past_year_pending"></span>)
                                </a>
                            </li>
                        </ul>
                        <h4 class="text-themecolor card-subtitle">Totales, sumando todos los meses</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
@section('modals')
@endsection
@section('scripts')
<script type="text/javascript">
    let urlCurrent = "{{URL::current()}}";
    let options = {
        seriesBarDistance: 10,
        scaleMinSpace: 15,
        axisX: {
            // On the x-axis start means top and end means bottom
            position: 'end',
            showGrid: true
        },
        axisY: {
            // On the y-axis start means left and end means right
            position: 'start',
            showGrid: true
        },
        low: '0',
        plugins: [
            Chartist.plugins.tooltip()
        ]
    }
    $(document).ready(function() {
        $.get(urlCurrent, function({
            current,
            past
        }) {
            let labels_current = current.labels;
            let series_current = current.series;
            let labels_past = past.labels;
            let series_past = past.series;

            let chart1 = new Chartist.Bar('.chart-payments', {
                labels: labels_current,
                series: series_current
            }, options);

            let chart2 = new Chartist.Bar('.chart-payments-old', {
                labels: labels_past,
                series: series_past
            }, options);

            $("#now_year_payment").html(series_current[0]['data'].reduce((a, b) => a + b, 0));
            $("#now_year_due").html(series_current[1]['data'].reduce((a, b) => a + b, 0));
            $("#now_year_scholarship").html(series_current[2]['data'].reduce((a, b) => a + b, 0));
            $("#now_year_pending").html(series_current[3]['data'].reduce((a, b) => a + b, 0));

            $("#past_year_payment").html(series_past[0]['data'].reduce((a, b) => a + b, 0));
            $("#past_year_due").html(series_past[1]['data'].reduce((a, b) => a + b, 0));
            $("#past_year_scholarship").html(series_past[2]['data'].reduce((a, b) => a + b, 0));
            $("#past_year_pending").html(series_past[3]['data'].reduce((a, b) => a + b, 0));

            let chart = [chart1, chart2];
            animateChart(chart);
        });
    });

    function animateChart(chart) {

        // ==============================================================
        // This is for the animation
        // ==============================================================
        for (let i = 0; i < chart.length; i++) {
            chart[i].on('draw', function(data) {
                if (data.type === 'line' || data.type === 'area') {
                    data.element.animate({
                        d: {
                            begin: 500 * data.index,
                            dur: 500,
                            from: data.path.clone().scale(1, 0).translate(0, data.chartRect.height()).stringify(),
                            to: data.path.clone().stringify(),
                            easing: Chartist.Svg.Easing.easeInOutElastic
                        }
                    });
                }
                if (data.type === 'bar') {
                    data.element.animate({
                        y2: {
                            dur: 500,
                            from: data.y1,
                            to: data.y2,
                            easing: Chartist.Svg.Easing.easeInOutElastic
                        },
                        opacity: {
                            dur: 500,
                            from: 0,
                            to: 1,
                            easing: Chartist.Svg.Easing.easeInOutElastic
                        }
                    });
                }
            });
        }
    }
</script>
@endsection