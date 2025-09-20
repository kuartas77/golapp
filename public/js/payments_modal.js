$('#training_group_id').select2({ placeholder: 'Seleccione...', allowClear: true });
let table = $('#active_table');
$(document).ready(() => {
    $("#export").attr('disabled', true);
    table = $('#active_table').DataTable({
        "paging": false,
        "ordering": false,
        "info": true,
        dom: 'it',//lftip
    });

    $('#active_table tbody').on('click', 'tr', function () {
        let rowData = table.row(this).data(); // Get the data for the clicked row

        if (rowData !== undefined) {
            let paymentId = $(rowData[0]).find('input').val();
            $.get(url_current + '/' + paymentId, ({ data }) => {
                if (data) {
                    loadDataModal(data)
                }
            });
        }
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
                    $("#export-excel").attr("href", "").addClass('disabled');
                    $("#export-pdf").attr("href", "").addClass('disabled');
                    table.destroy();
                    $('#table_body').empty();
                    initTable();
                }
            });
        }
    })

    $('#form_payments_modal').submit(function (e) {
        e.preventDefault();
    });
});


// Evento click en los select de la tabla
$('body').on('change', 'select.payments', function () {
    let select = $(this)

    if (select.val() == "") { return }

    checkValue(select)

    let data = $('#form_payments_modal').serializeArray();
    let id = $('#form_payments_modal').find('input[id="payment_id"]').val();
    data.push({ name: '_method', value: 'PUT' });

    select.blur()

    $.post(url_current + '/' + id, data, (response) => {
        if (response.data) {
            table.draw()
            $("#form_payments").trigger('submit')
        }
    });
});

function checkValue(select) {
    let name = select.attr('name')
    let input = select.parent().find('input');
    let valueInput = input.val().replace(/[\$,]/g, '') * 1

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

    switch (select.val()) {
        case '0':
            valueInput != 0 ? input.val(0) : null
            break;
        case '6':
            verifyInputs(select)
            break;
        case '1':
        case '9':
        case '10':
            if (name.includes('enrollment') && valueInput == 0) {
                input.val(inscription_amount)
            } else if(valueInput == 0){
                input.val(monthly_payment)
            }
            break;
        case '11':
        case '12':
            if(name.includes('enrollment')) {
                if (valueInput != annuity) {
                    input.val(annuity)
                    verifyInputs(select, annuity)
                }
                else if(valueInput == annuity){
                    input.val(annuity)
                    verifyInputs(select, annuity)
                }
            }
        case '13':
            input.val(annuity)
            break;

        default:
            break;
    }

    // if(valueInput == 0 && ['1','9','10'].includes(select.val())){
    //     if(name.includes('enrollment')){
    //         input.val(inscription_amount)
    //     }else{
    //         input.val(monthly_payment)
    //     }
    // }else if(valueInput != annuity && ['11','12'].includes(select.val())){
    //     input.val(annuity)
    //     verifyInputs(select, annuity)
    // }else if(valueInput == annuity && ['11','12'].includes(select.val())){
    //     input.val(annuity)
    //     verifyInputs(select, annuity)
    // }else if(valueInput != 0 && ['0'].includes(select.val())){
    //     input.val(0)
    // }else if(['13'].includes(select.val())){
    //     input.val(annuity)
    // }else if(['6'].includes(select.val())){
    //     verifyInputs(select)
    // }
}

function verifyInputs(select, value = 0) {
    let fields = $('#form_payments_modal').find('input.payments_amount, select.payments')

    $.each(fields, function (_, domElement) {
        let domInput = $(domElement)
        let input_val = domInput.val().replace(/[\$,]/g, '') * 1

        if (!domInput.attr('name').includes('enrollment')) {
            if (domInput.is('select')) {
                domInput.val(select.val())
            }
            else if (input_val == 0) {
                domInput.val(value);
            }
        }
    })
}

//inicia la tabla con datatables
function initTable() {
    table = $('#active_table').DataTable({
        "paging": false,
        "ordering": false,
        dom: 'it',//lftip
        "scrollX": true,
        "scrollCollapse": true,
        columnDefs: [
            { targets: [0, 1], width: '5%' },
            { targets: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14], width: 'auto' }
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
            $.each([2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14], function (index, value) {
                let columnas = api
                    .column(value, {
                        page: 'current'
                    })
                    .nodes();

                let columnas_total = api
                    .column(value)
                    .nodes();

                $.each(columnas_total, function (index, value) {
                    let a = $(value).find('input.payments_amount').val();
                    pageTotal = pageTotal + intVal(a);
                });

                $.each(columnas, function (index, value) {
                    let a = $(value).find('input.payments_amount').val();
                    total = total + intVal(a);
                });
            });
            let cash = 0;
            let consignment = 0;
            let others = 0;

            $.each([2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14], function (index, value) {

                let columnas_total = api
                    .column(value)
                    .nodes();

                $.each(columnas_total, function (index, value) {
                    let select = $(value).find('input.payments').val();
                    let inputVal = $(value).find('input.payments_amount').val();
                    if (['9', '12'].includes(select)) {
                        cash = cash + intVal(inputVal);
                    }
                    else if (['10', '11'].includes(select)) {
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
            $(api.column(14).footer()).html(sumTotal(api, 14, intVal));
        }
    });
    $('.payments_amount').inputmask("pesos");
}

function sumTotal(api, column, intVal) {
    let total = 0
    let columnas_total = api
        .column(column)
        .nodes();

    $.each(columnas_total, function (index, value) {
        let a = $(value).find('input[type=text]').val();
        total = total + intVal(a);
    });

    return `$${formatMoney(total)}`
}

function loadDataModal(data) {

    $('#player_img').attr("src", data.player.photo_url);
    $('#player_unique_code').val(data.unique_code)
    $('#player_document').val(data.player.identification_document)
    $('#player').val(data.player.full_names)
    $('#enrollment').val(data.enrollment)
    $('#enrollment_amount').val(data.enrollment_amount)

    $('#payment_id').val(data.id)

    $('#january').val(data.january)
    $('#january_amount').val(data.january_amount)
    $('#february').val(data.february)
    $('#february_amount').val(data.february_amount)
    $('#march').val(data.march)
    $('#march_amount').val(data.march_amount)
    $('#april').val(data.april)
    $('#april_amount').val(data.april_amount)
    $('#may').val(data.may)
    $('#may_amount').val(data.may_amount)
    $('#june').val(data.june)
    $('#june_amount').val(data.june_amount)
    $('#july').val(data.july)
    $('#july_amount').val(data.july_amount)
    $('#august').val(data.august)
    $('#august_amount').val(data.august_amount)
    $('#september').val(data.september)
    $('#september_amount').val(data.september_amount)
    $('#october').val(data.october)
    $('#october_amount').val(data.october_amount)
    $('#november').val(data.november)
    $('#november_amount').val(data.november_amount)
    $('#december').val(data.december)
    $('#december_amount').val(data.december_amount)

    let fields = $('#form_payments_modal').find('input.payments_amount, select.payments')

    if (data.deleted_at !== null) {
        fields.each((_, element) => $(element).attr('disabled', 'disabled'))
        // $('.payments_amount').each((_, element) => $(element).attr('disabled', 'disabled'))
        // $('.payments').each((_, element) => $(element).attr('disabled', 'disabled'))
    } else {
        fields.each((_, element) => $(element).removeAttr('disabled'))
        // $('.payments_amount').each((_, element) => $(element).removeAttr('disabled'))
        // $('.payments').each((_, element) => $(element).removeAttr('disabled'))
    }
    $("#modify_payment").modal('show');
}
