@extends('layouts.app')
@section('title', 'Pagos')
@section('content')
    <x-bread-crumb title="Pagos" :option="0"/>
    <x-row-card col-inside="12" >
        <input type="hidden" name="year" id="year" value="{{$year}}">
        <input type="hidden" name="training_group_id" id="training_group_id" value="{{$trainingGroup->id}}">
        <div class="card-body">
            <h4 class="card-title text-themecolor m-t-5">{{$trainingGroup->full_schedule_group}}</h4>
            @include('payments.payment.table')
        </div>
    </x-row-card>
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}';
        let table = $('#active_table');
        const initTable = () => {
            table = $('#active_table').DataTable({
                "paging": false,
                "ordering": false,
                "info": false,
                "scrollY": true,
                "columns": [
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '5%'},
                ],
                "footerCallback": function(row, data, start, end, display) {
                    let api = this.api();
                    // Remove the formatting to get integer data for summation
                    let intVal = function(i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                            i : 0;
                    };

                    // Total over all pages filtered indicate col index
                    let pageTotal = 0;
                    let total = 0;
                    $.each([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13], function(index, value) {
                        let columnas = api
                            .column(value, {
                                page: 'current'
                            })
                            .nodes();

                        let columnas_total = api
                            .column(value)
                            .nodes();

                        $.each(columnas_total, function(index, value) {
                            let a = $(value).find('input[type=text]').val();
                            pageTotal = pageTotal + intVal(a);
                        });

                        $.each(columnas, function(index, value) {
                            let a = $(value).find('input[type=text]').val();
                            total = total + intVal(a);
                        });
                    });
                    let cash = 0;
                    let consignment = 0;
                    let others = 0;

                    $.each([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13], function(index, value) {

                        let columnas_total = api
                            .column(value)
                            .nodes();

                        $.each(columnas_total, function(index, value) {
                            let select = $(value).find('select').val();
                            let inputVal = $(value).find('input[type=text]').val();
                            if (['1', '9', '12'].includes(select)) {
                                cash = cash + intVal(inputVal);
                            } else if (['10', '11'].includes(select)) {
                                consignment = consignment + intVal(inputVal);
                            } else {
                                others = others + intVal(inputVal);
                            }

                        });
                    });
                    // Update footer
                    let totalFormat = `$${formatMoney(pageTotal)}`
                    let totalCash = `$${formatMoney(cash)}`
                    let totalConsignment = `$${formatMoney(consignment)}`
                    let totalOthers = `$${formatMoney(others)}`
                    $('#total-tab').html(`Total: ${totalFormat}`)
                    $('#cash-tab').html(`Efectivo: ${totalCash}`)
                    $('#consignment-tab').html(`Consignación: ${totalConsignment}`)
                    $('#other-tab').html(`Otros: ${totalOthers}`)
                    $(api.column(1).footer()).html(sumTotal(api, 1, intVal));
                    $(api.column(2).footer()).html(sumTotal(api, 2, intVal));
                    $(api.column(3).footer()).html(sumTotal(api, 3, intVal));
                    $(api.column(4).footer()).html(sumTotal(api, 4, intVal));
                    $(api.column(5).footer()).html(sumTotal(api, 5, intVal));
                    $(api.column(6).footer()).html(sumTotal(api, 6, intVal));
                    $(api.column(7).footer()).html(sumTotal(api, 7, intVal));
                    $(api.column(8).footer()).html(sumTotal(api, 8, intVal));
                    $(api.column(9).footer()).html(sumTotal(api, 9, intVal));
                    $(api.column(10).footer()).html(sumTotal(api, 10, intVal));
                    $(api.column(11).footer()).html(sumTotal(api, 11, intVal));
                    $(api.column(12).footer()).html(sumTotal(api, 12, intVal));
                    $(api.column(13).footer()).html(sumTotal(api, 13, intVal));
                }
            });
            $('.payments_amount').inputmask("pesos");
        }

        $(document).ready(()=>{
            $("#export").attr('disabled',true);
            table = $('#active_table').DataTable();

            let data = {};
            data.training_group_id = $("#training_group_id").val();
            data.year = $("#year").val();
            $.get(url_current, data, function (response) {
                if (response.count > 0) {
                    console.log(response)
                    table.destroy();
                    $('#table_body').empty();
                    $('#table_body').append(response.rows);
                    initTable();
                    $("#export-excel").attr("href", response.url_export_excel).removeClass('disabled');
                    $("#export-pdf").attr("href", response.url_export_pdf).removeClass('disabled');
                } else {
                    $("#export-excel").attr("href","").addClass('disabled');
                    $("#export-pdf").attr("href","").addClass('disabled');
                    table.destroy();
                    $('#table_body').empty();
                    initTable();
                }
            });
        })
        function sumTotal(api, column, intVal){
            let total = 0
            let columnas_total = api
                .column(column)
                .nodes();

            $.each(columnas_total, function(index, value) {
                let a = $(value).find('input[type=text]').val();
                total = total + intVal(a);
            });

            return `$${formatMoney(total)}`
        }
    </script>
@endsection
