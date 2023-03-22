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
                    $("#export").attr("href", response.url_export);
                } else {
                    $("#export").attr('disabled',true);
                    $("#export").attr("href","javascript:void(0)");
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
                    element.removeClass('form-error')
                    element.addClass('form-success')
                    break;
                case '2':
                    element.removeClass('form-success')
                    element.addClass('form-error')
                    break;
                default:
                    element.removeClass('form-error')
                    element.removeClass('form-success')
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
        ]
    });
}
