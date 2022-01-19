const onClickDetails = (tr, row) => {
    if (row.child.isShown()) {
        // This row is already open - close it
        row.child.hide();
        tr.removeClass('shown');
    } else {
        // Open this row
        row.child(format(row.data())).show();
        tr.addClass('shown');
    }
}

const format = (d) => {
    let data_people = "";
    d.player.people.forEach(function (people) {
        let tutor = people.is_tutor ? "Acudiente" : "";
        data_people += '<tr>' +
            '<th><strong>' + tutor + ' ' + people.relationship_name + '</strong></th><td>' + people.names + '</td>' +
            '<th><strong>teléfonos:</strong></th><td>' + people.phone + ' - ' + people.mobile + '</td>' +
            '<th></th><td></td>'
            '</tr>';
    });

    return '<table class="w-100">' +
        data_people +
        '<tr>' +
        '<th>EPS:</th><td><strong>' + d.player.eps + '</strong></td>' +
        '<th>Fotos:</th><td>' + validateCheck(d.photos) + '</td>' +
        '<th>Fotocopia Doc Identificación:</th><td>' + validateCheck(d.copy_identification_document) + '</td>' +
        '</tr>' +
        '<tr>' +
        '<th>Certificado EPS,SISBEN:</th><td>' + validateCheck(d.eps_certificate) + '</label></td>' +
        '<th>Certificado médico:</th><td>' + validateCheck(d.medic_certificate) + '</label></td>' +
        '<th>Fotocopia Doc Acudiente:</th><td>' + validateCheck(d.study_certificate) + '</label></td>' +
        '</tr>' +
        '<tr>' +
        '<th>Peto:</th><td>' + validateCheck(d.overalls) + '</label></td>' +
        '<th>Balón:</th><td>' + validateCheck(d.ball) + '</label></td>' +
        '<th>Morral:</th><td>' + validateCheck(d.presentation_uniform) + '</label></td>' +
        '</tr>' +
        '<tr>' +
        '<th>Uniforme presentación:</th><td>' + validateCheck(d.presentation_uniform) + '</label></td>' +
        '<th>Uniforme competencia:</th><td>' + validateCheck(d.competition_uniform) + '</label></td>' +
        '<th>Pagó inscripción en torneo:</th><td>' + validateCheck(d.tournament_pay) + '</label></td>' +
        '</tr>' +
        '</table>';
}

const validateCheck = (value) => {
    return value !== 1 ? '<span class="label label-warning">NO</span>' : '<span class="label label-success">SI</span>';
}

const confirmAction = (element, event) => {
    const form = $(element).closest('form');
    event.preventDefault();
    let message = "";
    if ($(element).hasClass('btn-danger')) {
        message = "Desactivar Este Deportista"
    } else {
        message = "Activar Este Deportista"
    }
    Swal.fire({
        title: app_name,
        text: `¿Estas Seguro Que Quieres ${message}?`,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.value) {
            form.submit();
        }
    })
}

function filterTable() {
    // Apply the search
    let column = this.api().columns(8);
    $("<input type='search' class='' placeholder='Buscar Categoría' />")
        .appendTo($(column.header()).empty())
        .on('keyup change search', function () {
            if (column.search() !== this.value) {
                column.search(this.value)
                    .draw();
            }
        });
    $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
}

const columns = [
    {
        "className": 'details-control',
        "orderable": false,
        "data": null,
        "defaultContent": ''
    },
    {
        data: 'id', "render": function (data, type, row) {
            return "<img class='media-object img-rounded' src='" + row.player.photo_url + "' width='60' height='60' alt='" + row.player.full_names + "'>";
        }
    },
    {data: 'unique_code'},//2
    {data: 'player.identification_document'},//3
    {data: 'player.full_names'},//4
    {data: 'player.date_birth'},//5
    {data: 'player.gender'},//6
    {data: 'start_date'},//7
    {data: 'category', name: 'category', "className": 'text-center'},//8
    {data: 'training_group.name'},//9
    {
        data: 'medic_certificate', "render": function (data) {
            return data === 1 ? '<span class="label label-success">SI</span>' : '<span class="label label-warning">NO</span>';
        }
    },//10
    {data: 'player.mobile'},//11
    {
        data: 'id',
        "render": function (data, type, row) {
            let edit = "";
            if(isAdmin){
                edit = '<a href="javascript:void(0)" data-toggle="modal" data-target-custom="#create_inscription" data-backdrop="static"\n' +
                    'data-keyboard="false" data-href="' + row.url_edit + '" data-update="'+row.url_update+'" class="btn btn-warning btn-xs edit_inscription"><i class="fas fa-pencil-alt"></i></a>';
            }

            return '<div class="btn-group">' +
                '<a href="' + row.url_show + '" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>' +
                edit +
                '<a href="' + row.url_impression + '" target="_blank" class="btn btn-info btn-xs"><i class="fas fa-print" aria-hidden="true"></i></a>' +
                '</div>';
        }
    },//12
];

const columnDefs = [
    {"targets": [0, 1, 6, 10, 11, 12], "searchable": false},
    {"targets": [0, 1, 6, 8, 10, 11, 12], "orderable": false},
];
$(document).ready(function () {

    const active_table = $('#active_table').DataTable({
        "lengthMenu": [[10, 30, 50, 70, 100], [10, 30, 50, 70, 100]],
        "order": [[2, "desc"]],
        "scrollX": true,
        "processing": true,
        "serverSide": true,
        "deferRender": true,
        "fixedColumns": true,
        "columns": columns,
        "columnDefs": columnDefs,
        initComplete: filterTable,
        "ajax": $.fn.dataTable.pipeline({
            url: url_inscriptions_enabled,
            pages: 5 // number of pages to cache
        })
    });

    $('a[data-toggle="tab"]').on('shown.bs.tab', function () {
        $.fn.dataTable.tables({visible: true, api: true}).columns.adjust();
    });

    $('#active_table tbody').on('click', 'td.details-control', function () {
        let tr = $(this).closest('tr');
        let row = active_table.row(tr);
        onClickDetails(tr, row);
    });

    $('#active_table tbody').on('click', 'a.edit_inscription', function () {
        let btn = $(this);
        let form = $("#form_create");
        form.clearForm();
        $.get(btn.data('href'), function(response){
            $("#modal_title").html(`Actualizar Inscripción: ${response.unique_code}`);
            form.attr('action', btn.data('update'));
            if (form.find('#method').length === 0){
                form.prepend("<input name='_method' value='PUT' type='hidden' id='method'>");
            }
            $("#form_create #unique_code").val(response.unique_code).attr('readonly',true);
            $("#form_create #member_name").val(response.player.full_names);
            $("#form_create #player_id").val(response.player_id);
            $("#form_create #start_date").val(response.start_date).attr('disabled',true);
            $("#form_create #training_group_id").val(response.training_group_id).trigger('change');
            $("#form_create #competition_group_id").val(response.competition_group_id).trigger('change');

            $("#form_create #photos").prop('checked', response.photos == 1 );
            $("#form_create #copy_identification_document").prop('checked', response.copy_identification_document == 1 );
            $("#form_create #eps_certificate").prop('checked', response.eps_certificate == 1 );
            $("#form_create #medic_certificate").prop('checked', response.medic_certificate == 1 );
            $("#form_create #study_certificate").prop('checked', response.study_certificate == 1 );
            $("#form_create #overalls").prop('checked', response.overalls == 1 );
            $("#form_create #ball").prop('checked', response.ball == 1 );
            $("#form_create #bag").prop('checked', response.bag == 1 );
            $("#form_create #presentation_uniform").prop('checked', response.presentation_uniform == 1 );
            $("#form_create #competition_uniform").prop('checked', response.competition_uniform == 1 );
            $("#form_create #tournament_pay").prop('checked', response.tournament_pay == 1 );

            $("#create_inscription").modal('show');
            $("#btn_add_inscription").attr('disabled', false);
        }).fail(function() {
            Swal.fire({
                title: app_name,
                text: 'No Tienes Los Permisos Suficientes.',
                type: 'info',
            });
        });
    });

    $(".create_inscription").on('click',function (){
        let form = $("#form_create");
        $("#modal_title").html("Nueva Inscripción");
        form.attr('action', urlCreate);
        form.find('#method').remove();
        $("#form_create #start_date").attr('disabled',false);
        form.clearForm();
        $("#btn_add_inscription").attr('disabled', true);
    });
});
