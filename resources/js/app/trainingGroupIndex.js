let yearsInput = $('#years');
let scheduleSelect = 0;

const resetModalForm = (create = true, id) => {
    let form = $("#form_create");
    let title = $("#modal_title");
    if (create) {
        title.html("Agregar Nuevo Grupo");
        form.prop("action", url_current)
        form.prop("method", 'POST');
    } else {
        title.html("Actualizar Grupo De Entrenamiento.");
        form.prop("action", url_current + id)
        form.append("<input name='_method' id='method' value='PUT' type='hidden'>");
    }
}

const getSchedule = (value, schedule_id = 0) => {
    $.get(url_days + value, ({data}) => {
        $('#schedule_id').empty();
        $('#schedule_id').append(new Option("Seleccione...", ""));
        $.each(data, (index, object) => {
            $('#schedule_id').append(new Option(object.name, object.id));
        });
        if (schedule_id !== 0) {
            $("#schedule_id").val(schedule_id)
        }
    });
}

$(document).ready(() => {

    $('#active_table').DataTable({
        "lengthMenu": [[15, 30, 50, 70, 100], [15, 30, 50, 70, 100]],
        "processing": true,
        "serverSide": true,
        "ajax": $.fn.dataTable.pipeline({
            url: url_enabled,
            pages: 5 // number of pages to cache
        }),
        "columns": [
            {data: 'full_schedule_group'},
            {data: 'professor.name'},
            {
                data: 'id',
                "render": (data, type, row, meta) => {
                    return '<form method="POST" action="' + row.url_destroy + '" accept-charset="UTF-8"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="' + window.token.csrfToken + '"><div class="btn-group">'
                        + '<a href="javascript:void(0)" class="edit btn btn-info btn-xs" data-id="' + row.id + '"><i class="fas fa-pencil-alt"></i></a>'
                        + '<button class="btn btn-danger btn-xs" type="submit"><i class="fas fa-trash-alt"></i></button>'
                        + '</div></form>'
                }
            },
        ],
        "order": [[0, "desc"]],
    });

    $('#disabled_table').DataTable({
        "lengthMenu": [[5, 10, 30, 50, 70, 100], [5, 10, 30, 50, 70, 100]],
        "processing": true,
        "serverSide": true,
        "ajax": $.fn.dataTable.pipeline({
            url: url_disabled,
            pages: 5 // number of pages to cache
        }),
        "columns": [
            {data: 'full_schedule_group'},
            {data: 'professor.name'},
        ],
        "order": [[0, "desc"]]
    });

    yearsInput.multiSelect();

    $("#form_create").validate({
        rules: {
            name: {required: true},
            user_id: {required: true},
            day_id: {required: true},
            schedule_id: {required: true},
            'years[]': {required: true, minlength: 1, maxlength: 12}
        },
        messages: {
            'years[]': {maxlength: "No Seleccione Más De 12 Opciones."}
        }, submitHandler(form) {
            form.submit();
        }
    });

    $('#active_table tbody').on('click', 'a.edit', function() {
        let id = $(this).data('id');
        $.get(url_current + id + '/edit', ({data}) => {
            if (data != null) {
                scheduleSelect = data.schedule_id;
                resetModalForm(false, id);
                getSchedule(data.schedule.day_id, scheduleSelect);
                $("#name").val(data.name);
                $("#user_id").val(data.user_id);
                $("#day_id").val(data.schedule.day_id).trigger('change');
                yearsInput.multiSelect('deselect_all');
                yearsInput.multiSelect('select', data.years);
                $("#create").modal('show');
            }
        });
    });

    $(".btn-create").on('click', function() {
        $("#name").val('')
        $("#user_id").val('')
        $("#day_id").val('');
        yearsInput.multiSelect('deselect_all');
        $("#method").val('')
        $('#schedule_id').empty();
    });

    $("#day_id").on('change', function() {
        getSchedule($(this).val(), scheduleSelect);
    });
});
