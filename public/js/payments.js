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
    let element = $(this);
    let data = element.parent().parent().find('input, select').serializeArray();
    let id = element.parent().parent().find('input').val();
    data.push({name: '_method', value: 'PUT'});

    $.post(url_current + '/' + id, data, (response) =>{
        if (response.data) {
            changeColors(element)
            checkValue(element)
            table.draw()
        }
    });
});

function checkValue(element){
    let name = element.attr('name')
    let input = element.parent().find('input');
    let input_val = input.val().replace(/[\$,]/g, '') * 1

    if(input_val == 0 && ['1','9','10'].includes(element.val())){
        console.log(['1','9','10'])
        if(name.includes('enrollment')){
            input.val(inscription_amount)
        }else{
            input.val(monthly_payment)
        }
    }else if(input_val != annuity && ['11','12'].includes(element.val())){
        verifyInputs(element, annuity)
    }else if(input_val != 0 && ['0'].includes(element.val())){
        input.val(0)
        changeColors(element)
    }else if(['13'].includes(element.val())){
        input.val(annuity)
        changeColors(element)
    }
    element.blur()
}

function verifyInputs(element, value = 0){
    let inputs = element.parent().parent().find('input.payments_amount, select.payments')
    let lastElement = inputs[inputs.length -1]
    $.each(inputs, function(_, domElement){
        let domInput = $(domElement)
        if(!domInput.attr('name').includes('enrollment')){
            if(domInput.is('select')){
                domInput.val(element.val())
                changeColors(domInput)
            }else{
                domInput.val(value);
            }
        }
    })
    $(lastElement).trigger("change")
}

function changeColors(domelement){
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
            break;
        case '3':
            element.removeClass('form-success').removeClass('form-warning').removeClass('form-info')
                .removeClass('form-brown').removeClass('form-purple')
                .removeClass('form-orange').removeClass('form-grey').removeClass('form-error').removeClass('form-agua')
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
//inicia la tabla con datatables
function initTable() {
    table = $('#active_table').DataTable({
        "paging": false,
        "ordering": false,
        "info": true,
        "scrollX": true,
        "scrollY":"450px",
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
            // Update footer
            let totalFormat = `$${formatMoney(pageTotal)}`
            // $(api.column(10).footer()).html(totalFormat);
            $('#total-tab').html(`Total: ${totalFormat}`)
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
