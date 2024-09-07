$("#training_group_destination").select2({allowClear: true});
$('.space').slimScroll({
    size: "8px",
    height: '100%',
    color: '#FBCC01'
});

const changeSelect = (element_id, container) => {
    if (element_id !== '') {
        $.get(`${urlCurrent}/${element_id}`, ({rows, count, inscriptionRows, inscriptioncount}) => {
            $(container).empty().append(rows);
            $("#inscriptions").empty().append(inscriptionRows);
            $("#inscriptions_count").empty().html(`Cantidad: ${inscriptioncount}`)
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
const request = (id, assignment, source) => {
    $.post(urlCurrent + `/${id}`, assignment, ({response}) => {
        switch (response){
            case 1:
                alertSwal("Se agregó al grupo correctamente.", "success");
                break;
            case 2:
                alertSwal("Ahora no pertenece a ningún grupo.", "success");
                break;
            case 3:
                alertSwal("No se ha realizado el cambio de grupo. está al final de la lista", "error");
                source.appendChild(el);
                break;
            case 4:
                alertSwal("El integrante ya existe en el grupo. está al final de la lista", "error");
                source.appendChild(el);
                break;
        }
    }).fail(()=>{
        alertSwal("No se ha realizado el cambio de grupo. está al final de la lista", "error");
        source.appendChild(el);
    });
}

const search = (input, container) => {
    let query = $(input).val().toUpperCase();
    let content = $(`#${container}`).find('div.row')
    for (i = 0; i < content.length; i++) {
        let node = content[i].getElementsByTagName("small")[0];
        let node_ = content[i].getElementsByTagName("small")[1];
        txtValue = node.textContent || node.innerText
        otherValue = node_.textContent || node_.innerText
        if (txtValue.toUpperCase().indexOf(query) > -1 || otherValue.toUpperCase().indexOf(query) > -1) {
            content[i].style.display = "";
        } else {
            content[i].style.display = "none";
        }
    }
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

        if (container === 'inscriptions'){
            assignment.assign = false;
            assignment.destination_group = destination;
        }else {
            assignment.assign = true;
            assignment.destination_group = destination;
        }

        if (destination === '') {
            alertSwal('Se debe seleccionar un grupo!');
            source.appendChild(el);
            return;
        }else if($(target).find(`[data-id='${id}']`).length>1 && container === 'inscriptions'){
            el.remove()
        }else if($(target).find(`[data-id='${id}']`).length>1){
            alertSwal('Ya se encuentra agregado al grupo!');
            source.appendChild(el);
            return;
        }

        request(id, assignment, source)

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
