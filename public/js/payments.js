$('#training_group_id').select2({placeholder:'Seleccione...',allowClear: true});
let table = $('#active_table');
$(document).ready(() => {
    $("#export").attr('disabled',true);
    table = $('#active_table').DataTable({
        "paging": false,
        "ordering": false,
        "info": false,
        "scrollX": true,
        "scrollY": true,
        "columns": [
            {'width': '3%'},
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
                    $("#export-excel").attr("href", response.url_export_excel);
                    $("#export-pdf").attr("href", response.url_export_pdf);
                } else {
                    $("#export-excel").attr('disabled',true);
                    $("#export-pdf").attr('disabled',true);
                    $("#export-excel").attr("href","javascript:void(0)");
                    $("#export-pdf").attr("href","javascript:void(0)");
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
            switch (element.val()) {
                case '1':
                    element.removeClass('form-error').removeClass('form-warning').removeClass('form-info')
                    .removeClass('form-brown').removeClass('form-purple')
                    element.addClass('form-success')
                    break;
                case '2':
                    element.removeClass('form-success').removeClass('form-warning').removeClass('form-info')
                    .removeClass('form-brown').removeClass('form-purple')
                    element.addClass('form-error')
                    break;
                case '9':
                    element.removeClass('form-success').removeClass('form-error').removeClass('form-info')
                    .removeClass('form-brown').removeClass('form-purple')
                    element.addClass('form-warning')
                    break;
                case '10':
                    element.removeClass('form-success').removeClass('form-warning').removeClass('form-error')
                    .removeClass('form-brown').removeClass('form-purple')
                    element.addClass('form-info')
                    break;
                case '11':
                    element.removeClass('form-success').removeClass('form-warning').removeClass('form-error')
                        .removeClass('form-info').removeClass('form-brown')
                    element.addClass('form-purple')
                    break;
                case '12':
                    element.removeClass('form-success').removeClass('form-warning').removeClass('form-error')
                        .removeClass('form-info').removeClass('form-purple')
                    element.addClass('form-brown')
                    break;

                default:
                    element.removeClass('form-success').removeClass('form-warning').removeClass('form-error')
                    .removeClass('form-info').removeClass('form-purple').removeClass('form-brown')
                    break
            }
        }
    });

});

//inicia la tabla con datatables
function initTable() {
    table = $('#active_table').DataTable({
        "paging": false,
        "ordering": false,
        "info": false,
        "scrollX": true,
        "scrollY": true,
        "columns": [
            {'width': '3%'},
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
}
