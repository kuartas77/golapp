const addSchedule = () => {
    if (schedule_current >= schedule_max) {
        swal({
            type: 'warning',
            title: 'Oops...',
            text: 'No Se Pueden Agregar Más De 10 Horarios.',
        })
    } else {
        schedule_current++;
        let element = template(schedule_current);
        $("#create .schedules").append(element)
    }
}

const template = (item, id = "", schedule = "") => {
    let button = item === 1 ? '<button class="btn btn-outline-success" onclick="addSchedule()" type="button"><i class="fa fa-plus" aria-hidden="true"></i></button>' : '';
    return '<div class="col-md-8">\n' +
        '       <div class="form-group">\n' +
        '       <label for="schedule[' + item + ']" class="text-themecolor">Horario ' + item + '</label>\n' +
        '       <span class="bar"></span>\n' +
        '       <input type="hidden" name="schedule[' + item + '][id]" value="' + id + '">\n' +
        '       <input type="text" name="schedule[' + item + '][value]" class="form-control form-control-sm schedule" value="' + schedule + '" oninput="this.value = this.value.toUpperCase()">\n' +
        '       <span class="text-muted">Ej: 04:30 pm - 06:00 pm</span>' +
        '       </div>\n' +
        '   </div>' +
        '<div class="col-md-4">\n' +
        '     <div class="form-group">\n' +
        button +
        '     </div>' +
        '</div>';
}

const resetModalForm = (create = true, id) => {
    let form = $("#form_create");
    let title = $("#modal_title");
    if (create) {
        title.html("Agregar Días de Entrenamiento");
        form.prop("action", url_current)
        form.prop("method", 'POST');
    } else {
        title.html("Actualizar Días de Entrenamiento.");
        form.prop("action", url_current + id)
        form.append("<input name='_method' value='PUT' type='hidden'>");
    }
}

jQuery.propHooks.disabled = {
    set: (el, value) => {
        if (el.disabled !== value) {
            el.disabled = value;
            value && $(el).trigger('disabledSet');
            !value && $(el).trigger('enabledSet');
        }
    }
};
let schedule_min = 1;
let schedule_max = 10;
let schedule_current = 1;

$(document).ready(() => {
    let class_one = $("#class_one");
    let class_two = $("#class_two");
    let class_three = $("#class_three");
    let class_four = $("#class_four");
    let class_five = $("#class_five");

    let day_one = $("#day_one");
    let day_two = $('#day_two');
    let day_three = $('#day_three');
    let day_four = $('#day_four');
    let day_five = $('#day_five');

    $(".schedule").inputmask({
        mask: ["hh:mm t", "hh:mm t / hh:mm t"],
    });

    day_two.attr('disabled', true);
    day_three.attr('disabled', true);
    day_four.attr('disabled', true);
    day_five.attr('disabled', true);

    $('input').on('change', function (event) {
        switch ($(this).val()) {
            case '1':
                day_one.val('');
                day_two.attr('disabled', true).val('');
                day_three.attr('disabled', true).val('');
                day_four.attr('disabled', true).val('');
                day_five.attr('disabled', true).val('');
                $.each($('#day_one option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                break;
            case '2':
                day_one.val('');
                day_two.attr('disabled', false).val('');
                day_three.attr('disabled', true).val('');
                day_four.attr('disabled', true).val('');
                day_five.attr('disabled', true).val('');
                $.each($('#day_one option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                $.each($('#day_two option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                break;
            case '3':
                day_one.val('');
                day_two.attr('disabled', false).val('');
                day_three.attr('disabled', false).val('');
                day_four.attr('disabled', true).val('');
                day_five.attr('disabled', true).val('');
                $.each($('#day_one option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                $.each($('#day_two option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                $.each($('#day_three option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                break;
            case '4':
                day_one.val('');
                day_two.attr('disabled', false).val('');
                day_three.attr('disabled', false).val('');
                day_four.attr('disabled', false).val('');
                day_five.attr('disabled', true).val('');
                $.each($('#day_one option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                $.each($('#day_two option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                $.each($('#day_three option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                $.each($('#day_four option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                break;
            case '5':
                day_one.val('');
                day_two.attr('disabled', false).val('');
                day_three.attr('disabled', false).val('');
                day_four.attr('disabled', false).val('');
                day_five.attr('disabled', false).val('');
                $.each($('#day_one option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                $.each($('#day_two option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                $.each($('#day_three option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                $.each($('#day_four option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                $.each($('#day_five option[disabled]'), (index, option) => {
                    $(option).prop('disabled', false);
                });
                break;
        }
    });

    $('.classes').on('change', function (event) {
        let elementChange = $(this);
        let value = elementChange.val();
        $("select.classes").each(function (i, element) {
            let el = $(element);
            if (elementChange.attr('id') !== el.attr('id') && value !== '') {
                el.find('option[value="' + value + '"]:not(:selected)').prop("disabled", true);
            }
        });
    });

    $('#active_table').DataTable({
        "lengthMenu": [[15, 30, 50, 70, 100], [15, 30, 50, 70, 100]],
        "scrollX": true,
        "processing": true,
        "serverSide": true,
        "ajax": $.fn.dataTable.pipeline({
            url: url_enabled,
            pages: 5 // number of pages to cache
        }),
        "columns": [
            {data: 'days'},
            {data: 'schedul'},
            {
                data: 'id',
                "render": (data, type, row, meta) => {
                    return '<form method="POST" action="' + row.url_destroy + '" accept-charset="UTF-8">' +
                        '<input name="_method" type="hidden" value="DELETE">' +
                        '<input name="_token" type="hidden" value="' + window.token.csrfToken + '">' +
                        '<div class="btn-group">' +
                        '<a href="javascript:void(0)" class="edit btn btn-default btn-xs" data-id="' + data + '">' +
                        '<i class="fas fa-pencil-alt"></i>' +
                        '</a>' +
                        '</div>' +
                        '</form>'

                }
            },
        ],
        "order": [[0, "desc"]],
    });

    $("#form_create").validate({
        rules: {
            day_one: {required: true},
            day_two: {required: true},
            day_three: {required: true},
            'schedule[1][value]': {required: true}
        }
    });

    $("#btn-add").on('click', function () {
        schedule_current = 1;
        $("#method").val('')
        class_one.prop('checked', true).trigger('change');
        $("#create .schedules").empty().append(template(1))
        $("#create #num_class").empty().html('')
    })

    $('#active_table tbody').on('click', 'a.edit', function () {
        let id = $(this).data('id');

        $.get(`${url_current}${id}/edit`, function (response) {
            if (response.data != null) {
                schedule_current = 1;
                resetModalForm(false, id);
                let days = response.data.days.split(',');

                switch (days.length) {
                    case 1:
                        class_one.prop('checked', true).trigger('change');
                        day_one.val(days[0]).trigger('change');
                        break;
                    case 2:
                        class_two.prop('checked', true).trigger('change');
                        day_one.val(days[0]).trigger('change');
                        day_two.val(days[1]).trigger('change');
                        break;
                    case 3:
                        class_three.prop('checked', true).trigger('change');
                        day_one.val(days[0]).trigger('change');
                        day_two.val(days[1]).trigger('change');
                        day_three.val(days[2]).trigger('change');
                        break;
                    case 4:
                        class_four.prop('checked', true).trigger('change');
                        day_one.val(days[0]).trigger('change');
                        day_two.val(days[1]).trigger('change');
                        day_three.val(days[2]).trigger('change');
                        day_four.val(days[3]).trigger('change');
                        break;
                    case 5:
                        class_five.prop('checked', true).trigger('change');
                        day_one.val(days[0]).trigger('change');
                        day_two.val(days[1]).trigger('change');
                        day_three.val(days[2]).trigger('change');
                        day_four.val(days[3]).trigger('change');
                        day_five.val(days[4]).trigger('change');
                        break;
                }
                $("#create #num_class").empty().html(response.data.schedules_count)
                $("#create .schedules").empty()
                $.each(response.data.schedules, (index, schedule) => {
                    let element = template(index + 1, schedule.id, schedule.schedule);
                    $("#create .schedules").append(element);
                    schedule_current++;
                });
                if (response.data.schedules.length === 0) {
                    let element = template(1);
                    $("#create .schedules").append(element)
                }
                $("#create").modal('show');
            }
        });
    });
});
