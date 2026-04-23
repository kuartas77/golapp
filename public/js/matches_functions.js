const selectPositions = () => {
    let options = "<option value=''>Selecciona...</option>";
    $.each(positions, function (i, d) {
        options += "<option value='" + i + "'>" + d + "</option>";
    });
    return options;
}

const selectOptions = () => {
    return "<option value='1'>Sí</option><option value='0' selected>No</option>";
}

const selectMinutes = () => {
    let select = "<option value=''>Selecciona...</option>";
    for (let index = 0; index <= 90; index++) {
        if (index === 0) {
            select += "<option value='" + index + "' selected>" + index + " MIN</option>";
        } else {
            select += "<option value='" + index + "'>" + index + " MIN</option>";
        }
    }
    return select;
}

const selectGoals = () => {
    let select = "<option value='0'>0</option>";
    for (let index = 1; index <= 10; index++) {
        select += "<option value='" + index + "'>" + index + "</option>";
    }
    return select;
}

const selectScore = () => {
    let score = "<option value='' selected>Seleccionar...</option>";
    for (let index = 1; index <= 5; index++) {
        score += "<option value='" + index + "'>" + index + "</option>";
    }
    return score;
}

const selectYellowCards = () => {
    return "<option value='0'>0</option><option value='1'>1</option><option value='2'>2</option>";
}

const selectRedCards = () => {
    return "<option value='0'>0</option><option value='1'>1</option>";
}

const escapeMatchHtml = (value) => {
    return $('<div>').text(value ?? '').html();
}

const buildMemberContactLine = (player) => {
    let contacts = [];

    if (player.phones) {
        contacts.push('Tel: ' + escapeMatchHtml(player.phones));
    }

    if (player.mobile) {
        contacts.push('Cel: ' + escapeMatchHtml(player.mobile));
    }

    if (contacts.length === 0) {
        return '';
    }

    return "<small class='text-muted match-player-contact'>" + contacts.join(' | ') + "</small>";
}

const buildMemberIdentityCell = ({player, count, inscriptionId, includeIdField = false}) => {
    let hiddenFields = '';

    if (includeIdField) {
        hiddenFields += "<input name='ids[" + count + "]' type='hidden' value=''>";
    }

    hiddenFields += "<input name='inscriptions_id[" + count + "]' type='hidden' value='" + escapeMatchHtml(inscriptionId) + "' class='inscriptions'>";

    return "<td class='match-player-cell'>" +
        hiddenFields +
        "<div class='match-player-meta'>" +
        "<img class='match-player-avatar' src='" + escapeMatchHtml(player.photo_url) + "' alt='" + escapeMatchHtml(player.full_names) + "'>" +
        "<div>" +
        "<span class='match-player-name'>" + escapeMatchHtml(player.full_names) + "</span>" +
        "<span class='match-player-code'>" + escapeMatchHtml(player.unique_code) + "</span>" +
        buildMemberContactLine(player) +
        "</div>" +
        "</div>" +
        "</td>";
}

const updateMembersCount = () => {
    $('#members_count').text($('#body_members tr').length);
}

const alertSwalError = () => {
    Swal.fire('Atención!',
        'El Deportista ya hace parte del equipo ó no se encontro.',
        'warning'
    );
}

const findMemberInMatch = (inscription_id) => {
    let find = false;
    $('input.inscriptions').each(function () {
        if ($(this).val() == inscription_id)
            find = true;
    });
    return find;
}

const getAutoCompletes = () => {
    $.get(urlList, ({data}) => {
        $('#unique_code').typeahead({
            source: data,
            scrollBar: true
        });
    });
    $.get(urlAutoComplete, {fields: ['zone', 'rival_name']}, ({zone, rival_name}) => {
        $('#place').typeahead({
            source: zone,
            scrollBar: true
        });

        $('#rival_name').typeahead({
            source: rival_name,
            scrollBar: true
        });
    });
}

const cancelAddMember = () => {
    member_add = null;
    $("#member_name_add").addClass('hide');
    $("#unique_code").val('');
    $("#member_name").val('');
    $('#accept_add').attr('disabled', true);
}
