$('#training_group_id').select2({allowClear: true});
$('#year').select2();
$('#month').select2();
let btnPrint = $('#print');
let btnPrintExcel = $("#print_excel");
let tableActive = $('#active_table');
let form_assist = $("#form_assist");
const containerClassdays = $("#classdays");
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
            $.get(url_classDays, data, (response) => {
                loadClassdays(response)
            });
        }
    });

    $("#createAssist").on('click', function() {
        if (form_assist.valid()) {
            let data = $("#form_assist").serializeArray();
            $.post(url_current, data, (response) => {
                swal.fire({
                    title: 'Atención!!',
                    text: `Se Han Creado ${response.count} Asistencia(s).`,
                    type: 'info',
                    showCancelButton: false,
                    timer: 1500
                });
            });
        }
    });
});

$('body').on('change', 'select.assist', function()  {
    let element = $(this)
    let data = element.parent().parent().find('input, select').serializeArray();
    let id = element.parent().parent().find('input').val();
    if (this.value === '') {return;}
    changeColorAssist(element, this.value)
    data.push({name: '_method', value: 'PUT'});
    $.post(url_current + `/${id}`, data);

});

$('body').on('click', 'a.assist', function()  {
    selectRow = $(this)

    let id = $(this).data('id');
    let column = $(this).data('column')
    let date = $(this).data('date')
    let name = $(this).data('name')
    let day = $(this).data('day')
    let number = $(this).data('number')

    $.get(url_current + `/${id}`,{ column: column, date: date, action:'assist' }, ({id, observation, value, player_name}) => {
        $('#attendance_id').val(id)
        $('#attendance_day').val(day)
        $('#attendance_date').val(date)
        $('#attendance_number').val(number)
        $('#attendance_name').val(name.toUpperCase())
        $('#select_attendance').attr('name', column)
        $('#select_attendance').val(value).trigger('change')
        $('#single_observation').val(observation)
        $('#player_name').empty().append(player_name)
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
        $('#player_name').empty()
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
    });
}

function changeColorAssist(domelement, value = null){
    let element = $(domelement)
    // element.html(options[value])
    switch (value) {
        case "1":
            element.removeClass(removeAllClass)
            element.addClass('color-success')
            console.log('color-success')
            break;
        case "2":
            element.removeClass(removeAllClass)
            element.addClass('color-error')
            console.log('color-error')
            break;
        case "3":
            element.removeClass(removeAllClass)
            element.addClass('color-orange')
            console.log('color-orange')
            break;
        case "4":
            element.removeClass(removeAllClass)
            element.addClass('color-grey')
            console.log('color-grey')
            break;
        case "5":
            element.removeClass(removeAllClass)
            element.addClass('color-warning')
            console.log('color-warning')
            break;
        default:
            element.removeClass(removeAllClass)
            console.log('default')
            break
    }
    element.blur()
}

const loadClassdays = (classDays) => {
    let Htmlbuttons = ''
    tableActive.clear().draw()
    $('#group_name').empty()
    $("#ClassCount").empty().append(`# ${classDays.length}`)
    classDays.forEach((classDay, index) => {
        Htmlbuttons += `<button class="btn btn-info m-1 class-day"
            data-group="${classDay.group_id}"
            data-month="${classDay.month}"
            data-column="${classDay.column}"># ${index+1} | ${classDay.day}: ${classDay.date}</button>`
    })
    containerClassdays.empty().append(Htmlbuttons)
}

$('body').on('click', 'button.class-day', function(){
    let button = $(this);
    let group = button.data('group');
    let month = button.data('month');
    let column = button.data('column');

    let data = {'training_group_id':group, 'month':month, 'column': column }

    $.get(url_current, data, (response) => {
        validateData(response, true);

        let classText = button.html()

        $('#class_name').empty().append(`Entrenamiento: ${classText}`)
    });
})