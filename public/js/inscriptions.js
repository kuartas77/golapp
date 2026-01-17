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
    if(d.player){
        d.player.people.forEach(function ({tutor, relationship_name, names, phone, mobile}) {
            let is_tutor = tutor === 1 ? "ACUDIENTE" : "";
            let phones = (mobile == null) ?  phone : `${phone} ${mobile}`
            data_people += '<tr>' +
                '<th><strong>' + is_tutor +'</strong></th><td></td>' +
                '<th>'+ relationship_name +'</th><th>' + names + '</th>' +
                '<th><strong>Teléfonos:</strong></th><th>' + phones + '</th>' +
                '<th></th><td></td>'+
                '</tr>';
        });
    }

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
        '</tr>' +
        '</table>';
}

const validateCheck = (value) => {
    return value !== 1 ? '<span class="label label-warning">NO</span>' : '<span class="label label-success">SI</span>';
}

function addSelectTraining(column){
    let select = $('<select><option value="">G. Entrenamiento...</option></select>')
        .appendTo($(column.header()).empty())
        .on('change', function () {
            let val = $.fn.dataTable.util.escapeRegex(
                $(this).val()
            );

            column
                .search(val , true, false)
                .draw();
        });

        groups.forEach(function (d, j) {
            if (column.search() === d) {
                select.append('<option value="' + d + '" selected="selected">' + d + '</option>')
            } else {
                select.append('<option value="' + d.id + '">' + d.name + '</option>')
            }
        });
}
function addSelectCategory(column){
    let select = $('<select><option value="">Categoria...</option></select>')
        .appendTo($(column.header()).empty())
        .on('change', function () {
            let val = $.fn.dataTable.util.escapeRegex(
                $(this).val()
            );

            column
                .search(val ? '^' + val + '$' : '', true, false)
                .draw();
        });

        categories.forEach(function (d, j) {
            if (column.search() === '^' + d + '$') {
                select.append('<option value="' + d + '" selected="selected">' + d + '</option>')
            } else {
                select.append('<option value="' + d.category + '">' + d.category + '</option>')
            }
        });
}

function filterTable() {

    // Apply the search
    this.api().columns([11]).every(function () {
        let column = this;
        addSelectCategory(column)
    });

    this.api().columns([7]).every(function () {
        let column = this;
        addSelectTraining(column)
    });

    let start_date = this.api().columns(10);
    $("<input type='search' class='' placeholder='F.Inicio' />")
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
            return "<img class='rounded-circle' width='70' height='50' src='" + row.player.photo_url + "' alt='" + row.player.full_names + "'>";
        }, 'searchable': false, name:'inscriptions.id'
    },
    {data: 'unique_code'},//2
    {data: 'player.identification_document'},//3
    {data: 'player.full_names', name :'player.last_names'},//4
    {data: 'player.date_birth', 'searchable': false},//5
    {data: 'player.gender', 'searchable': false},//6
    {data: 'training_group.name', name: 'training_group_id'},//7
    {
        data: 'medic_certificate', "render": function (data) {
            return data === 1 ? '<span class="label label-success">SI</span>' : '<span class="label label-warning">NO</span>';
        }, 'searchable': false
    },//8
    {data: 'player.mobile', 'searchable': false},//9
    {data: 'start_date', 'searchable': true},//10
    {data: 'category', name: 'inscriptions.category', "className": 'text-center'},//11
    {
        data: 'id',
        "render": function (data, type, row) {
            let edit = ""
            let deleteButton = ''
            let invioceButton = ''

            if(row.deleted_at !== null) return ''

            if(isAdmin && (yearSelected >= currentYear)){
                edit = '<a href="javascript:void(0)" data-toggle="modal" data-target-custom="#create_inscription" data-backdrop="static"\n' +
                    'data-keyboard="false" data-href="' + row.url_edit + '" data-update="'+row.url_update+'" class="btn btn-warning btn-xs edit_inscription" title="Modificar Inscripción"><i class="fas fa-pencil-alt"></i></a>'
                deleteButton = '<button class="btn btn-danger btn-xs disable-inscription" title="Eliminar inscripción"><i class="fas fa-trash-alt"></i></button>'
                invioceButton = '<a href="'+row.url_invoice+'" class="btn btn-success btn-xs invoice-inscription" title="Factura"><i class="fas fa-file-alt"></i></a>'
            }

            return '<form method="POST" action="' + row.url_destroy + '" accept-charset="UTF-8"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="' + window.token.csrfToken + '"><div class="btn-group">'
            + edit
            +'<a href="' + row.url_show + '" class="btn btn-info btn-xs" title="Ficha"><i class="fas fa-eye"></i></a>'
            + '<a href="' + row.url_impression + '" target="_blank" class="btn btn-info btn-xs" title="PDF"><i class="fas fa-print" aria-hidden="true"></i></a>'
            + invioceButton
            + deleteButton
            + '</div></form>';
        }, 'searchable': false
    },//12
];

const columnsDelete = [
    {
        data: 'id', "render": function (data, type, row) {
            return "<img class='rounded-circle' width='70' height='50' src='" + row.player.photo_url + "' alt='" + row.player.full_names + "'>";
        }, 'searchable': false
    },
    {data: 'unique_code'},//2
    {data: 'player.identification_document'},//3
    {data: 'player.full_names', name :'player.last_names'},//4
    {data: 'player.date_birth', 'searchable': false},//5
    {data: 'player.gender', 'searchable': false},//6
    {
        data: 'medic_certificate', "render": function (data) {
            return data === 1 ? '<span class="label label-success">SI</span>' : '<span class="label label-warning">NO</span>';
        }, 'searchable': false
    },//7
    {data: 'player.mobile', 'searchable': false},//8
    {data: 'category', name: 'category', "className": 'text-center'},//9
];
const columnDefs = [
    { "targets": [2, 3, 4, 7, 11], "searchable": true },
    { "targets": [0, 1, 6, 7, 8, 9, 10, 11, 12], "orderable": false },
    { "targets": [7, 11], "width": "1%" },
    { "targets": [1, 2, 3, 5, 6, 8, 10,12], "width": "1px" },

];

$(document).ready(function () {

    const active_table = $('#active_table').DataTable({
        "lengthMenu": [[10, 30, 50, 70, 100], [10, 30, 50, 70, 100]],
        "order": [[2, "desc"]],
        "scrollX": true,
        "scrollY":"550px",
        "scrollCollapse":true,
        "processing": true,
        "serverSide": true,
        "deferRender": true,
        "fixedColumns": true,
        "columns": columns,
        "columnDefs": columnDefs,
        "createdRow": function (row, data, dataIndex) {
            if (data.pre_inscription == 1 && data.training_group_id == firstGroup) {
                $(row).addClass('bg-warning')
            }else if (data.training_group_id == firstGroup) {
                $(row).addClass('bg-info')
            }
        },
        initComplete: filterTable,
        "ajax": $.fn.dataTable.pipeline({
            url: url_inscriptions_enabled,
            pages: 5 // number of pages to cache
        })
    });

    const inactive_table = $('#inactive_table').DataTable({
        "lengthMenu": [[10, 30, 50, 70, 100], [10, 30, 50, 70, 100]],
        "order": [[2, "desc"]],
        "scrollX": true,
        "scrollY":"550px",
        "scrollCollapse":true,
        "processing": true,
        "serverSide": true,
        "deferRender": true,
        "fixedColumns": true,
        "columns": columnsDelete,
        // "columnDefs": columnDefs,
        // initComplete: filterTable,
        "ajax": $.fn.dataTable.pipeline({
            url: url_inscriptions_disabled,
            pages: 5 // number of pages to cache
        })
    });

    inscriptionYear()

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
            $("#form_create #competition_groups").val(response.competition_group ?? []).trigger('change');
            $("#form_create #pre_inscription").val(response.pre_inscription);

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
            $("#form_create #pre_inscription").prop('checked', response.pre_inscription == 1 );

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
        $("#form_create #competition_groups").val([]).trigger('change');
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

    $('#inscription_year').on('change', function(){
        inscriptionYear ()
    })

    function inscriptionYear() {
        let element = $('#inscription_year')
        yearSelected = element.val()
        if(yearSelected) {
            let parameter = "?" + element.attr('id') + "=" + element.val()
            let url_enabled = url_inscriptions_enabled + parameter
            // let url_disabled = url_inscriptions_disabled + parameter
            active_table.ajax.url($.fn.dataTable.pipeline({url: url_enabled})).load();
            // inactive_table.ajax.url($.fn.dataTable.pipeline({url: url_disabled})).load();
        }
    }
});