@extends('layouts.app')
@section('content')
    <x-bread-crumb title="Pagos Torneos" :option="0"/>
    <x-row-card col-inside="12" >
        @include('payments.tournaments.form')
        @include('payments.tournaments.table')
    </x-row-card >
@endsection
@section('modals')
@endsection
@section('scripts')
    <script>
        let url_current = '{{URL::current()}}';
        let url_competition_groups = "{{route('autocomplete.competition_groups')}}";
        $(function () {
            $(".preloader").fadeOut()
        })
        $('#tournament_id').select2({placeholder:'Seleccione...',allowClear: true});
        $('#competition_group_id').select2({placeholder:'Seleccione...',allowClear: true});
        let table = $('#active_table');
        let form_payments = $("#form_payments");
        $(document).ready(() => {
            // $("#export").attr('disabled',true);
            initTable()

            $('body').on('change', '#tournament_id', function () {
                let element = $(this);
                if(element.val() == null || element.val() == '') return
                let data = {tournament_id: element.val()}
                $.get(url_competition_groups, data, function ({data}) {
                    $("#competition_group_id").empty();
                    $("#competition_group_id").select2({data: data});
                });
            })

            form_payments.validate({
                submitHandler: function (form) {
                    let data = $(form).serializeArray();
                    $.get(url_current, data, function (response) {
                        validateData(response, true)
                    });
                }
            })

            $("#createTournamentPay").on('click', function() {
                if (form_payments.valid()) {
                    let data = $("#form_payments").serializeArray();
                    $.post(url_current, data, (response) => {
                        validateData(response, false);
                    });
                }
            });

            $('body').on('change', 'select.payments', function()  {
                let element = $(this)
                let data = element.parent().parent().find('input, select').serializeArray();
                let id = element.parent().parent().find('input[name="id"]').val();
                if (this.value === '') {
                    return;
                }
                data.push({name: '_method', value: 'PUT'});
                $.post(url_current + `/${id}`, data);
                changeColors(element)
                element.blur()
            });
        });

        const initTable = () => {
            table = $('#active_table').DataTable({
                "paging": false,
                "ordering": false,
                "info": true,
                "scrollX": true,
                "scrollY": true,
                "columns": [
                    {'width': '5%'},
                    {'width': '5%'},
                    {'width': '20%'},
                ],
                "footerCallback": function (row, data, start, end, display) {
                    let api = this.api();
                    // Remove the formatting to get integer data for summation
                    let intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    // Total over all pages filtered indicate col index
                    let pageTotal = 0;
                    let total = 0;
                    $.each([2], function(index, value) {
                        let columnas = api
                            .column(value, {
                                page: 'current'
                            })
                            .nodes();

                        let columnas_total = api
                            .column(value)
                            .nodes();

                        $.each(columnas_total, function(index, value) {
                            let a = $(value).find('.payments_amount').val();
                            pageTotal = pageTotal + intVal(a);
                        });

                        $.each(columnas, function(index, value) {
                            let a = $(value).find('.payments_amount').val();
                            total = total + intVal(a);
                        });
                    });
                    let cash = 0;
                    let consignment = 0;

                    $.each([2], function(index, value) {

                        let columnas_total = api
                            .column(value)
                            .nodes();

                        $.each(columnas_total, function(index, value) {
                            let select = $(value).find('select').val();
                            let inputVal = $(value).find('.payments_amount').val();
                            if(['9', '12'].includes(select)){
                                cash = cash + intVal(inputVal);
                            }
                            else if(['10', '11'].includes(select)){
                                consignment = consignment + intVal(inputVal);
                            }
                            
                        });
                    });
                    // Update footer
                    let totalFormat = `$${formatMoney(pageTotal)}`
                    let totalCash = `$${formatMoney(cash)}`
                    let totalConsignment = `$${formatMoney(consignment)}`
                    $('#total-tab').html(`Total: ${totalFormat}`)
                    $('#cash-tab').html(`Efectivo: ${totalCash}`)
                    $('#consignment-tab').html(`Consignación: ${totalConsignment}`)
                }
            });
            $('.payments_amount').inputmask("pesos");
        }

        const validateData = ({rows, group_name, count, url_print, url_print_excel}, search) => {
            if (count > 0) {
                let message = search ? `Se Han Encontrado ${count} Registro(s).` : `Se Han Creado ${count} Registro(s).`;
                table.destroy();
                $('#table_body').empty();
                $('#table_body').append(rows);
                $("#export-excel").attr("href", url_print_excel).removeClass('disabled');
                $("#export-pdf").attr("href", url_print).removeClass('disabled');
                initTable();
                swal.fire({
                    title: 'Atención!!',
                    text: message,
                    type: 'info',
                    showCancelButton: false,
                    timer: 2000
                });
            } else {
                let message = search ? "Se Deben Crear Los Registros." : "El Grupo No Cuenta Con Integrantes."
                table.destroy();
                $('#table_body').empty();
                $("#export-excel").attr("href", '').addClass('disabled');
                $("#export-pdf").attr("href", '').addClass('disabled');
                initTable();
                swal.fire({
                    title: 'Atención!!',
                    text: message,
                    type: 'warning',
                    showCancelButton: false,
                    timer: 2000
                });
            }
        }

        const changeColors = (domelement) => {
            let element = $(domelement)
            let val = element.val().replace(/[\$,]/g, '')
            switch (val) {
                case '1':
                    element.removeClass('form-error').removeClass('form-warning').removeClass('form-info')
                        .removeClass('form-brown').removeClass('form-purple')
                        .removeClass('form-orange').removeClass('form-grey').removeClass('form-agua')
                    element.addClass('form-success')
                    break;
                case '2':
                    element.removeClass('form-success').removeClass('form-warning').removeClass('form-info')
                        .removeClass('form-brown').removeClass('form-purple')
                        .removeClass('form-orange').removeClass('form-grey').removeClass('form-agua')
                    element.addClass('form-error')
                case '3':
                    element.removeClass('form-success').removeClass('form-warning').removeClass('form-info')
                        .removeClass('form-brown').removeClass('form-purple')
                        .removeClass('form-orange').removeClass('form-grey').removeClass('form-agua')
                    element.addClass('form-agua')
                    break;
                case '5':
                    element.removeClass('form-success').removeClass('form-warning').removeClass('form-info')
                        .removeClass('form-brown').removeClass('form-purple')
                        .removeClass('form-grey').removeClass('form-agua')
                    element.addClass('form-orange')
                    break;
                case '6':
                    element.removeClass('form-success').removeClass('form-error').removeClass('form-info')
                        .removeClass('form-brown').removeClass('form-purple')
                        .removeClass('form-orange').removeClass('form-agua')
                    element.addClass('form-grey')
                    break;
                case '9':
                    element.removeClass('form-success').removeClass('form-error').removeClass('form-info')
                        .removeClass('form-brown').removeClass('form-purple')
                        .removeClass('form-orange').removeClass('form-grey').removeClass('form-agua')
                    element.addClass('form-warning')
                    break;
                case '10':
                    element.removeClass('form-success').removeClass('form-warning').removeClass('form-error')
                        .removeClass('form-brown').removeClass('form-purple')
                        .removeClass('form-orange').removeClass('form-grey').removeClass('form-agua')
                    element.addClass('form-info')
                    break;
                case '11':
                    element.removeClass('form-success').removeClass('form-warning').removeClass('form-error')
                        .removeClass('form-info').removeClass('form-brown')
                        .removeClass('form-orange').removeClass('form-grey').removeClass('form-agua')
                    element.addClass('form-purple')
                    break;
                case '12':
                    element.removeClass('form-success').removeClass('form-warning').removeClass('form-error')
                        .removeClass('form-info').removeClass('form-purple')
                        .removeClass('form-orange').removeClass('form-grey').removeClass('form-agua')
                    element.addClass('form-brown')
                    break;
                case '0':
                default:
                    element.removeClass('form-success').removeClass('form-warning').removeClass('form-error')
                    .removeClass('form-info').removeClass('form-purple').removeClass('form-brown')
                    .removeClass('form-orange').removeClass('form-grey').removeClass('form-agua')
                    break
            }
        }
    </script>
@endsection
