$("#training_group_destination").select2({allowClear: true});
$('.space').slimScroll({
    size: "8px",
    height: '100%',
    color: '#FBCC01'
});

const changeSelect = (element_id, container) => {
    if (element_id !== '') {
        $.get(`${urlCurrent}/${element_id}`, ({rows, count}) => {
            $(container).empty().append(rows);
            $("#destination_count").empty().html(`Cantidad De Integrantes: ${count}`)

        });
    } else {
        $(container).empty();
        $("#destination_count").empty();
    }
}

const alertSwal = (message, type = 'warning') => {
    Swal.fire(app_name,
        message,
        type
    );
}

$(document).ready(() => {
    dragula([
        document.querySelector('#inscriptions'),
        document.querySelector('#destination'),
        {removeOnSpill: true}
    ]).on('drop', function (el, target, source, sibling) {
        let id = $(el).attr('data-id');
        let container = $(target).attr('id');
        let destination = $("#training_group_destination").val();
        let assignment = {};

        if (destination === '') {
            alertSwal('Se Debe Seleccionar Un Grupo!');
            source.appendChild(el);
            return;
        }

        if (container === 'inscriptions'){
            assignment.destination_group = null;
        }else {
            assignment.destination_group = destination;
        }

        $.post(urlCurrent + `/${id}`, assignment, ({response}) => {
            switch (response){
                case 1:
                    alertSwal("Se Agregó Al Grupo Correctamente.", "success");
                    break;
                case 2:
                    alertSwal("Ahora No Pertenece A Ningún Grupo.", "success");
                    break;
                case 3:
                    alertSwal("No Se Ha Realizado El Cambio De Grupo. Está Al Final De La Lista", "error");
                    source.appendChild(el);
                    break;
            }
        }).fail(()=>{
            alertSwal("No Se Ha Realizado El Cambio De Grupo. Está Al Final De La Lista", "error");
            source.appendChild(el);
        });
    });

    $('#training_group_destination').on('change', function () {
        let groupName = $("#training_group_destination option:selected").text();
        $("#group_selected").empty().html(groupName);
        changeSelect($(this).val(), "#destination");
    });

    $("#form_admin").validate({
        submitHandler(form) {
            let data = {};
            data.competition_group_id = $("#training_group_destination").val();
            $.get(urlCurrent, data, ({rows, groups}) => {
                $("#inscriptions").empty().append(rows);
            });
        }
    })
});
