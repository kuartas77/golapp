let yearsInput = $('#years');
let usersInput = $('#user_id');
let daysInput = $('#days');
let schedulesInput = $('#schedules');
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
            {data: 'full_schedule_group', name: 'full_schedule_group'},
            {data: 'instructors_names', name: 'instructors_names'},
            {
                data: 'id',
                "render": (data, type, row, meta) => {
                    if(row.name.includes('Provisional')){
                        button_edit = ''
                        button_disable = ''
                    }else{
                        button_edit = '<a href="javascript:void(0)" class="edit btn btn-info btn-xs" data-id="' + row.id + '"><i class="fas fa-pencil-alt"></i></a>'
                        button_disable = '<button class="btn btn-danger btn-xs" type="submit"><i class="fas fa-trash-alt"></i></button>'
                    }
                    return '<form method="POST" action="' + row.url_destroy + '" accept-charset="UTF-8"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="' + window.token.csrfToken + '"><div class="btn-group">'
                        + button_edit
                        + button_disable
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
            {data: 'instructors_names', name: 'instructors_names'},
        ],
        "order": [[0, "desc"]]
    });

    yearsInput.multiSelect();
    usersInput.multiSelect();
    daysInput.multiSelect()
    schedulesInput.multiSelect()

    $("#form_create").validate({
        rules: {
            name: {required: true},
            'user_id[]': {required: true, minlength: 1, maxlength: 2},
            'days[]': {required: true, minlength: 1, maxlength: 3},
            'schedules[]': {required: true, minlength: 1, maxlength: 2},
            'years[]': {required: true, minlength: 1, maxlength: 12}
        },
        messages: {
            'years[]': {maxlength: "No seleccione más de 12 opciones."},
            'user_id[]':{maxlength: "No seleccione más de 2 opciones."},
            'days[]':{maxlength: "No seleccione más de 3 opciones."},
            'schedules[]':{maxlength: "No seleccione más de 2 opciones."}
        }, submitHandler(form) {
            form.submit();
        }
    });

    $('#active_table tbody').on('click', 'a.edit', function() {
        let id = $(this).data('id');
        $.get(url_current + id + '/edit', ({data}) => {
            if (data != null) {
                ids = data.instructors_ids.map((element) => element.toString())
                resetModalForm(false, id);
                $("#name").val(data.name);
                $("#stage").val(data.stage);
                daysInput.multiSelect('deselect_all');
                daysInput.multiSelect('select', data.explode_days);
                schedulesInput.multiSelect('deselect_all');
                schedulesInput.multiSelect('select', data.explode_schedules);
                usersInput.multiSelect('deselect_all');
                usersInput.multiSelect('select', ids);
                yearsInput.multiSelect('deselect_all');
                yearsInput.multiSelect('select', data.years);
                $("#create").modal('show');
            }
        });
    });

    $(".btn-create").on('click', function() {
        $("#name").val('')
        usersInput.multiSelect('deselect_all');
        daysInput.multiSelect('deselect_all');
        yearsInput.multiSelect('deselect_all');
        schedulesInput.multiSelect('deselect_all');
        $("#method").val('')
    });
});
