const formMatches = $('#form_matches');
$("#date").bootstrapMaterialDatePicker({
    time: false,
    clearButton: false,
    lang: 'es',
    cancelText: 'Cancelar',
    okText: 'Aceptar',
    minDate: moment().subtract(1, 'month'),
    maxDate: moment()
});

$(".timepicker").bootstrapMaterialDatePicker({
    format: 'hh:mm A',
    shortTime: true,
    time: true,
    date: false,
    lang: 'es',
    clearButton: false,
    cancelText: 'Cancelar',
    okText: 'Aceptar',
});

$(document).ready(() => {
    getAutoCompletes();
    formMatches.validate({
        rules: {
            competition_group_id: {required: true},
            tournament_id: {required: true},
            num_match: {required: true, numbers: true},
            place: {required: true},
            user_id: {required: true},
            rival_name: {required: true},
            date: {required: true},
            hour: {required: true},
            "final_score[soccer]": {required: true, numbers: true},
            "final_score[rival]": {required: true, numbers: true},
        }
    });

    $('#form_search').on('submit', function(e) {

        e.preventDefault();
        let code = $('#form_search #unique_code').val();
        let group_id = $('#competition_group_id').val();
        if (code === "") return;
        const form = {};
        form.unique_code = code;
        form.competition_group_id = group_id;
        $.get(urlSearch, form, ({data}) => {
            if (data != null && !findMemberInMatch(data.id)) {

                member_add = data;
                $("#member_name_add").removeClass('hide');
                $("#member_name").val(member_add.player.full_names);
                $('#accept_add').attr('disabled', false);

            } else {
                alertSwalError();
            }
        }).fail(() => {
            alertSwalError();
        });
    });
});
