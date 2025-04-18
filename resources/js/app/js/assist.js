$('#training_group_id').select2({allowClear: true});
$('#year').select2();
$('#month').select2();
let btnPrint = $('#print');
let btnPrintExcel = $("#print_excel");
let tableActive = $('#active_table');
let form_assist = $("#form_assist");
jQuery(function() {
    tableActive = $('#active_table').DataTable({
            "paging": false,
            "ordering": false,
            "info": false,
            "scrollX": true,
            "scrollY": true,
        });

    form_assist.validate({
        submitHandler: (form) => {
            let data = $(form).serializeArray();
            $.get(url_current, data, (response) => {
                validateData(response, true);
            });
        }
    });

    $("#createAssist").on('click', function() {
        if (form_assist.valid()) {
            let data = $("#form_assist").serializeArray();
            $.post(url_current, data, (response) => {
                validateData(response, false);
            });
        }
    });
});

$('body').on('change', 'select.assist', function()  {
    let element = $(this)
    let data = element.parent().parent().find('input, select').serializeArray();
    let id = element.parent().parent().find('input').val();
    if (this.value === '') {return;}
    changeColorAssist(element)
    data.push({name: '_method', value: 'PUT'});
    $.post(url_current + `/${id}`, data);

});


$('body').on('click', 'button.observation', function() {
    let button = $(this);
    let id = button.data('id');
    let title = $("#modal_title");
    title.empty();
    $('#id_row').val(id);
    $('#observations').val('');

    $.get(url_current + `/${id}`, ({id, observations, player}) => {
        title.html('Observación para: ' + player.full_names);
        $('#id_row').val(id);
        $('#observations').val(observations);
    });
});


$("#form_observation").validate({
    submitHandler: (form) => {
        let id = $('#id_row').val();
        let data = $(form).serializeArray();
        data.push({name: '_method', value: 'PUT'});
        $.post(url_current + `/${id}`, data);
        $('#modal_observation').modal('hide');
    }
});

const validateData = ({table, group_name, count, url_print, url_print_excel}, search) => {
    if (count > 0) {
        let message = search ? `Se Han Encontrado ${count} Asistencia(s).` : `Se Han Creado ${count} Asistencia(s).`;
        tableActive.destroy();
        $("#enabled").empty().append(table);
        $('#group_name').empty().append(group_name);
        initTable();
        btnPrint.prop("href", url_print);
        btnPrint.removeClass('hide');
        btnPrintExcel.prop("href", url_print_excel);
        btnPrintExcel.removeClass('hide');
        swal.fire({
            title: 'Atención!!',
            text: message,
            type: 'info',
            showCancelButton: false,
            timer: 1500
        });
    } else {
        let message = search ? "Se Deben Crear Las Asistencias." : "El Grupo No Cuenta Con Integrantes."
        tableActive.destroy();
        $("#enabled").empty().append(table);
        $('#group_name').empty().append(group_name);
        initTable();
        btnPrint.prop("href", "");
        btnPrint.addClass('hide');
        btnPrintExcel.prop("href", "");
        btnPrintExcel.addClass('hide');
        swal.fire({
            title: 'Atención!!',
            text: message,
            type: 'warning',
            showCancelButton: false,
            timer: 1500
        });
    }
}

const initTable = () => {
    tableActive = $('#active_table').DataTable({
        "paging": false,
        "ordering": false,
        "info": false,
        "scrollX": true,
        "scrollY": true,
    });
}

function changeColorAssist(domelement){
    let element = $(domelement)
    let val = element.val().replace(/[\$,]/g, '')
    switch (val) {
        case 'as':
            element.removeClass(removeAllClass)
            element.addClass('color-success')
            break;
        case 'fa':
            element.removeClass(removeAllClass)
            element.addClass('color-error')
            break;
        case 'ex':
            element.removeClass(removeAllClass)
            element.addClass('color-orange')
            break;
        case 're':
            element.removeClass(removeAllClass)
            element.addClass('color-grey')
            break;
        case 'in':
            element.removeClass(removeAllClass)
            element.addClass('color-warning')
            break;
        default:
            element.removeClass(removeAllClass)
            break
    }
    element.blur()
}