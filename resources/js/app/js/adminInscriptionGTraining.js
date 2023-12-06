$("#training_group_origin").select2({allowClear: true});
$("#training_group_destiny").select2({allowClear: true});
$('.space').slimScroll({
    size: "8px",
    height: '100%',
    color: '#FBCC01'
});

$('#training_group_origin').on('change', function () {
    let destiny = $('#training_group_destiny').val();
    if ($(this).val() === destiny){
        alertSwal('Los grupos seleccionados son los mismos!');
        $("#origin").empty();
        return;
    }
    changeSelect($(this).val(), "#origin");
});

$('#training_group_destiny').on('change', function () {
    let origin = $('#training_group_origin').val();
    if ($(this).val() === origin){
        alertSwal('Los grupos seleccionados son los mismos!');
        $("#destiny").empty();
        return;
    }
    changeSelect($(this).val(), "#destiny");
});

const changeSelect = (element_id, container) => {
    if (element_id !== '') {
        $.get(urlCurrent + `/${element_id}`, ({rows}) => {
            $(container).empty().append(rows);
        });
    } else {
        $(container).empty();
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
        document.querySelector('#origin'),
        document.querySelector('#destiny'),
        {removeOnSpill: true}
    ]).on('drop', function (el, target, source, sibling) {
        let origin = $('#training_group_origin').val();
        let destiny = $('#training_group_destiny').val();
        let id = $(el).attr('data-id');
        let assignment = {};

        if (origin === destiny) {
            alertSwal('Los grupos seleccionados son los mismos!');
            source.appendChild(el);
            return;
        }
        if (origin === '') {
            alertSwal('Debes de seleccionar un grupo de origen!');
            source.appendChild(el);
            return;
        }
        if (destiny === '') {
            alertSwal('Debes de seleccionar un grupo de destino!');
            source.appendChild(el);
            return;
        }

        if ($(target).attr('id') === 'destiny') {
            assignment.origin_group = origin;
            assignment.target_group = destiny;
        } else {
            assignment.origin_group = destiny;
            assignment.target_group = origin;
        }
        $.post(urlCurrent + `/${id}`, assignment, ({data}) =>{
            if (data){
                alertSwal("Se Agregó Al Grupo Correctamente.", "success");
            }else{
                alertSwal("No Se Ha Realizado El Cambio De Grupo.", "error");
                source.appendChild(el);
            }
        });
    });
});
