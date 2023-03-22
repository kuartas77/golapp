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
    d.player.people.forEach(function ({tutor, relationship_name, names, phone, mobile}) {
        let is_tutor = tutor === 1 ? "ACUDIENTE" : "";
        data_people += '<tr>' +
            '<th><strong>' + is_tutor +'</strong></th><td></td>' +
            '<th>'+ relationship_name +'</th><th>' + names + '</th>' +
            '<th><strong>Teléfonos:</strong></th><th>' + phone + ' - ' + mobile + '</th>' +
            '<th></th><td></td>'+
            '</tr>';
    });

    return '<table class="w-100">' +
        data_people +
        '<tr>' +
        '<th><strong>EPS:</strong></th><td><strong>' + d.player.eps + '</strong></td>' +
        '<th><strong>Certificado EPS,SISBEN:</strong></th><td>' + validateCheck(d.eps_certificate) + '</td>' +
        '<th><strong>Fotocopia Doc Acudiente:</strong></th><td>' + validateCheck(d.study_certificate) + '</td>' +
        '<th></th><td></td>'+
        '</tr>' +
        '<tr>' +
        '<th><strong>Certificado médico:</strong></th><td>' + validateCheck(d.medic_certificate) + '</td>' +
        '<th><strong>Fotos:</strong></th><td>' + validateCheck(d.photos) + '</td>' +
        '<th><strong>Fotocopia Doc Identificación:</strong></th><td>' + validateCheck(d.copy_identification_document) + '</td>' +        
        '<th></th><td></td>'+
        '</tr>' +

        '<tr>' +
        '<th><strong>Pagó Inscripción Torneo 1:</strong></th><td>' + validateCheck(d.tournament_pay) + '</td>' +
        '<th><strong>Uniforme presentación:</strong></th><td>' + validateCheck(d.presentation_uniform) + '</td>' +
        '<th></th><td></td>'+
        '<th></th><td></td>'+
        '</tr>' +
        '<tr>' +
        '<th><strong>Pagó Inscripción Torneo 2:</strong></th><td>' + validateCheck(d.bag) + '</td>' +        
        '<th><strong>Uniforme competencia:</strong></th><td>' + validateCheck(d.competition_uniform) + '</td>' +
        '<th></th><td></td>'+
        '<th></th><td></td>'+
        '</tr>' +
        '<tr>' +
        '<th><strong>Pagó Inscripción Torneo 3:</strong></th><td>' + validateCheck(d.ball) + '</td>' +
        '<th><strong>Peto:</strong></th><td>' + validateCheck(d.overalls) + '</td>' +
        '<th></th><td></td>'+
        '<th></th><td></td>'+
        '</tr>' +
        '</table>';
}

const validateCheck = (value) => {
    return value !== 1 ? '<span class="label label-warning">NO</span>' : '<span class="label label-success">SI</span>';
}

function filterTable() {
    // Apply the search
    let column = this.api().columns(11);
    $("<input type='search' class='' placeholder='Buscar Categoría' />")
        .appendTo($(column.header()).empty())
        .on('keyup change search', function () {
            if (column.search() !== this.value) {
                column.search(this.value)
                    .draw();
            }
        });

    let start_date = this.api().columns(10);
    $("<input type='search' class='' placeholder='Buscar F.Inicio' />")
        .appendTo($(start_date.header()).empty())
        .on('keyup change search', function () {
            if (start_date.search() !== this.value) {
                start_date.search(this.value)
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
            return "<img class='img-fluid rounded img-thumbnail' width='70' height='50' src='" + row.player.photo_url + "' alt='" + row.player.full_names + "'>";
        }
    },
    {data: 'unique_code'},//2
    {data: 'player.identification_document'},//3
    {data: 'player.full_names'},//4
    {data: 'player.date_birth'},//5
    {data: 'player.gender'},//6
    {data: 'training_group.name'},//7
    {
        data: 'medic_certificate', "render": function (data) {
            return data === 1 ? '<span class="label label-success">SI</span>' : '<span class="label label-warning">NO</span>';
        }
    },//8
    {data: 'player.mobile'},//9
    {data: 'start_date'},//10
    {data: 'category', name: 'category', "className": 'text-center'},//11
    {
        data: 'id',
        "render": function (data, type, row) {
            let edit = "";
            if(isAdmin){
                edit = '<a href="javascript:void(0)" data-toggle="modal" data-target-custom="#create_inscription" data-backdrop="static"\n' +
                    'data-keyboard="false" data-href="' + row.url_edit + '" data-update="'+row.url_update+'" class="btn btn-warning btn-xs edit_inscription"><i class="fas fa-pencil-alt"></i></a>';
            }

            return '<form method="POST" action="' + row.url_destroy + '" accept-charset="UTF-8"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="' + window.token.csrfToken + '"><div class="btn-group">'
            + edit
            +'<a href="' + row.url_show + '" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>'
            + '<a href="' + row.url_impression + '" target="_blank" class="btn btn-info btn-xs"><i class="fas fa-print" aria-hidden="true"></i></a>'
            + '<button class="btn btn-danger btn-xs disable-inscription"><i class="fas fa-trash-alt"></i></button>'
            + '</div></form>';
        }
    },//12
];

const columnDefs = [
    {"searchable": false, "targets": [0, 1, 6, 8, 9, 12]},
    {"orderable": false, "targets": [0, 1, 6, 10, 11, 12]},
    {"width": "1%" , "targets": [10, 11] }

];

$(document).ready(function () {

    const active_table = $('#active_table').DataTable({
        "ordering": false,
        "scrollX": true,
        "scrollY": true,
        "lengthMenu": [[10, 30, 50, 70, 100], [10, 30, 50, 70, 100]],
        "order": [[2, "desc"]],
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
        $("#form_create #training_group_id").val('').trigger('change');
        $("#form_create #competition_group_id").val('').trigger('change');
        $("#btn_add_inscription").attr('disabled', true);
    });

    $('#active_table tbody').on('click', '.disable-inscription', function(event){
        event.preventDefault();
        let message = "";
        const form = $(this).closest('form');
        if ($(this).hasClass('btn-danger')) {
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
    });
});
