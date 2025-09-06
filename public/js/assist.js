$('#training_group_id').select2({allowClear: true});
$('#year').select2();
$('#month').select2();
let btnPrint = $('#print');
let btnPrintExcel = $("#print_excel");
let tableActive = $('#active_table');
let form_assist = $("#form_assist");
let selectRow = null
jQuery(function() {
    tableActive = $('#active_table').DataTable({
            "paging": false,
            "ordering": false,
            "info": false,
            dom: 'it',//lftip
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

// $('body').on('change', 'select.assist', function()  {
//     let element = $(this)
//     let data = element.parent().parent().find('input, select').serializeArray();
//     let id = element.parent().parent().find('input').val();
//     if (this.value === '') {return;}
//     changeColorAssist(element)
//     data.push({name: '_method', value: 'PUT'});
//     $.post(url_current + `/${id}`, data);

// });

$('body').on('click', 'a.assist', function()  {
    selectRow = $(this)

    let id = $(this).data('id');
    let column = $(this).data('column')
    let date = $(this).data('date')
    let name = $(this).data('name')
    let day = $(this).data('day')
    let number = $(this).data('number')

    $.get(url_current + `/${id}`,{ column: column, date: date, action:'assist' }, ({id, observation, value}) => {
        $('#attendance_id').val(id)
        $('#attendance_day').val(day)
        $('#attendance_date').val(date)
        $('#attendance_number').val(number)
        $('#attendance_name').val(name.toUpperCase())
        $('#select_attendance').attr('name', column)
        $('#select_attendance').val(value).trigger('change')
        $('#single_observation').val(observation)
    });
})

$("#form_attendance").validate({
    submitHandler: (form) => {
        let id = $('#attendance_id').val()
        let day = $('#attendance_day').val()
        let value = $("#select_attendance").val()
        let data = $(form).serializeArray()
        let element = `#${id}${day}`
        changeColorAssist(element, value)
        data.push({name: 'id', value: id})
        data.push({name: '_method', value: 'PUT'})
        $.post(url_current + `/${id}`, data)

        $(selectRow[0]).attr('data-value', value)
        $(selectRow[0]).attr('data-observation', $('#single_observation').val())

        $('#modal_attendance').modal('hide')
        $('#form_attendance')[0].reset()
    }
})

$('body').on('click', 'button.observation', function() {
    let button = $(this);
    let id = button.data('id');
    let title = $("#modal_title");
    title.empty();
    $('#id_row').val(id);
    $('#observations').val('');

    $.get(url_current + `/${id}`, {action: 'observation'}, ({id, observations, player}) => {
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
        tableActive.clear().draw();
        $('#group_name').empty().append(group_name);
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
        dom: 'it',//lftip
        "scrollX": true,
        columnDefs: [
            { targets: [0, 1], width: '5%'},
            { targets: '_all', width: 'auto'}
        ],
    });
}

function changeColorAssist(domelement, value = null){
    let element = $(domelement)
    let val = ''
    if(value){
        element.html(options[value])
        val = value.replace(/[\$,]/g, '')
    }else{
        val = element.val().replace(/[\$,]/g, '')
    }
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