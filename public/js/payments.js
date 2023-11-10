$('#training_group_id').select2({placeholder:'Seleccione...',allowClear: true});
let table = $('#active_table');
$(document).ready(() => {
    $("#export").attr('disabled',true);
    table = $('#active_table').DataTable({
        "paging": false,
        "ordering": false,
        "info": true,
        "scrollX": true,
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
        ]
    });

    $("#form_payments").validate({
        submitHandler: function (form) {
            let data = $(form).serializeArray();
            $.get(url_current, data, function (response) {
                if (response.count > 0) {
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
        }
    })
});

// Evento click en los select de la tabla
$('body').on('change', 'select.payments', function () {
    let element = $(this)

    if(element.val() == "") {return}
    checkValue(element)

    let data = element.parent().parent().find('input, select').serializeArray();
    let id = element.parent().parent().find('input').val();
    data.push({name: '_method', value: 'PUT'});

    element.blur()
    $.post(url_current + '/' + id, data, (response) =>{
        if (response.data) {
            changeColors(element)
            // checkValue(element)
            table.draw()
        }
    });
});

function checkValue(element){
    let name = element.attr('name')
    let dataId = element.data('id')
    let input = element.parent().find('input');
    let input_val = input.val().replace(/[\$,]/g, '') * 1

    // '0' => "Pendiente",
    // '1' => "Pagó",
    // '9' => "Pagó - Efectivo",
    // '10' => "Pagó - Consignación",
    // '11' => "Pago Anualidad Consignación",
    // '12' => "Pago Anualidad Efectivo",
    // '13' => "Acuerdo de Pago",
    // '14' => "No Aplica",
    // '2' => "Debe",
    // '3' => "Abonó",
    // '4' => "Incapacidad",
    // '5' => "Retiro Temporal",
    // '6' => "Retiro Definitivo",
    // '7' => "Otro",
    // '8' => "Becado",

    if(input_val == 0 && ['1','9','10'].includes(element.val())){
        if(name.includes('enrollment')){
            input.val(inscription_amount)
        }else{
            input.val(monthly_payment)
        }
    }else if(input_val != annuity && ['11','12'].includes(element.val())){
        verifyInputs(element, annuity)
    }else if(input_val == annuity && ['11','12'].includes(element.val())){
        verifyInputs(element, annuity)
    }else if(input_val != 0 && ['0'].includes(element.val())){
        input.val(0)
        changeColors(element)
    }else if(['13'].includes(element.val())){
        input.val(annuity)
        changeColors(element)
    }else if(['6'].includes(element.val())){
        verifyInputs(element)
    }
}

function verifyInputs(element, value = 0){
    let dataId = element.data('id')
    let inputs = element.parent().parent().find('input.payments_amount, select.payments')

    $.each(inputs, function(_, domElement){
        let domInput = $(domElement)
        let domInputId = $(domElement).data('id')
        let input_val = domInput.val().replace(/[\$,]/g, '') * 1

        if(!domInput.attr('name').includes('enrollment')){
            if(domInput.is('select') && domInputId >= dataId){
                domInput.val(element.val())
                changeColors(domInput)
            }
            else {
                if(input_val != 0 ){
                    changeColors(domInput)
                }
                else{
                    domInput.val(value);
                }
            }
        }
    })
}

//inicia la tabla con datatables
function initTable() {
    table = $('#active_table').DataTable({
        "paging": false,
        "ordering": false,
        "info": true,
        // "scrollX": true,
        // "scrollY":"450px",
        "scrollCollapse":true,
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
            $.each([1,2,3,4,5,6,7,8,9,10,11,12,13], function(index, value) {
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

            $.each([1,2,3,4,5,6,7,8,9,10,11,12,13], function(index, value) {

                let columnas_total = api
                    .column(value)
                    .nodes();

                $.each(columnas_total, function(index, value) {
                    let select = $(value).find('select').val();
                    let inputVal = $(value).find('input[type=text]').val();
                    if(['1','9', '12'].includes(select)){
                        cash = cash + intVal(inputVal);
                    }
                    else if(['10', '11'].includes(select)){
                        consignment = consignment + intVal(inputVal);
                    }else{
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
            $( api.column( 1 ).footer() ).html(sumTotal(api, 1, intVal));
            $( api.column( 2 ).footer() ).html(sumTotal(api, 2, intVal));
            $( api.column( 3 ).footer() ).html(sumTotal(api, 3, intVal));
            $( api.column( 4 ).footer() ).html(sumTotal(api, 4, intVal));
            $( api.column( 5 ).footer() ).html(sumTotal(api, 5, intVal));
            $( api.column( 6 ).footer() ).html(sumTotal(api, 6, intVal));
            $( api.column( 7 ).footer() ).html(sumTotal(api, 7, intVal));
            $( api.column( 8 ).footer() ).html(sumTotal(api, 8, intVal));
            $( api.column( 9 ).footer() ).html(sumTotal(api, 9, intVal));
            $( api.column( 10 ).footer() ).html(sumTotal(api, 10, intVal));
            $( api.column( 11 ).footer() ).html(sumTotal(api, 11, intVal));
            $( api.column( 12 ).footer() ).html(sumTotal(api, 12, intVal));
            $( api.column( 13 ).footer() ).html(sumTotal(api, 13, intVal));
        }
    });
    $('.payments_amount').inputmask("pesos");
}

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
