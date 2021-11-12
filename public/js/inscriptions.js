const onClickDetails=(t,e)=>{e.child.isShown()?(e.child.hide(),t.removeClass("shown")):(e.child(format(e.data())).show(),t.addClass("shown"))},format=t=>{let e="";return t.player.peoples.forEach((function(t){let a=t.is_tutor?"Acudiente":"";e+="<tr><th><strong>"+a+" "+t.relationship_name+"</strong></th><td>"+t.names+"</td><th><strong>teléfonos:</strong></th><td>"+t.phone+" - "+t.mobile+"</td><th></th><td></td>"})),'<table class="w-100">'+e+"<tr><th>EPS:</th><td><strong>"+t.player.eps+"</strong></td><th>Fotos:</th><td>"+validateCheck(t.photos)+"</td><th>Fotocopia Doc Identificación:</th><td>"+validateCheck(t.copy_identification_document)+"</td></tr><tr><th>Certificado EPS,SISBEN:</th><td>"+validateCheck(t.eps_certificate)+"</label></td><th>Certificado médico:</th><td>"+validateCheck(t.medic_certificate)+"</label></td><th>Fotocopia Doc Acudiente:</th><td>"+validateCheck(t.study_certificate)+"</label></td></tr><tr><th>Peto:</th><td>"+validateCheck(t.overalls)+"</label></td><th>Balón:</th><td>"+validateCheck(t.ball)+"</label></td><th>Morral:</th><td>"+validateCheck(t.presentation_uniform)+"</label></td></tr><tr><th>Uniforme presentación:</th><td>"+validateCheck(t.presentation_uniform)+"</label></td><th>Uniforme competencia:</th><td>"+validateCheck(t.competition_uniform)+"</label></td><th>Pagó inscripción en torneo:</th><td>"+validateCheck(t.tournament_pay)+"</label></td></tr></table>"},validateCheck=t=>1!==t?'<span class="label label-warning">NO</span>':'<span class="label label-success">SI</span>',confirmAction=(t,e)=>{const a=$(t).closest("form");e.preventDefault();let i="";i=$(t).hasClass("btn-danger")?"Desactivar Este Deportista":"Activar Este Deportista",Swal.fire({title:app_name,text:`¿Estas Seguro Que Quieres ${i}?`,type:"warning",showCancelButton:!0,confirmButtonColor:"#3085d6",cancelButtonColor:"#d33",confirmButtonText:"Sí",cancelButtonText:"No"}).then((t=>{t.value&&a.submit()}))};function filterTable(){let t=this.api().columns(8);$("<input type='search' class='' placeholder='Buscar Categoría' />").appendTo($(t.header()).empty()).on("keyup change search",(function(){t.search()!==this.value&&t.search(this.value).draw()})),$.fn.dataTable.tables({visible:!0,api:!0}).columns.adjust()}const columns=[{className:"details-control",orderable:!1,data:null,defaultContent:""},{data:"id",render:function(t,e,a){return"<img class='media-object img-rounded' src='"+a.player.photo+"' width='60' height='60' alt='"+a.player.full_names+"'>"}},{data:"unique_code"},{data:"player.identification_document"},{data:"player.full_names"},{data:"player.date_birth"},{data:"player.gender"},{data:"start_date"},{data:"category",name:"category",className:"text-center"},{data:"training_group.name"},{data:"medic_certificate",render:function(t){return 1===t?'<span class="label label-success">SI</span>':'<span class="label label-warning">NO</span>'}},{data:"player.mobile"},{data:"id",render:function(t,e,a){let i="";return isAdmin&&(i='<a href="javascript:void(0)" data-toggle="modal" data-target-custom="#create_inscription" data-backdrop="static"\ndata-keyboard="false" data-href="'+a.url_edit+'" data-update="'+a.url_update+'" class="btn btn-warning btn-xs edit_inscription"><i class="fas fa-pencil-alt"></i></a>'),'<div class="btn-group"><a href="'+a.url_show+'" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>'+i+'<a href="'+a.url_impression+'" target="_blank" class="btn btn-info btn-xs"><i class="fas fa-print" aria-hidden="true"></i></a></div>'}}],columnDefs=[{targets:[0,1,6,10,11,12],searchable:!1},{targets:[0,1,6,8,10,11,12],orderable:!1}];$(document).ready((function(){const t=$("#active_table").DataTable({lengthMenu:[[10,30,50,70,100],[10,30,50,70,100]],order:[[2,"desc"]],scrollX:!0,processing:!0,serverSide:!0,deferRender:!0,fixedColumns:!0,columns:columns,columnDefs:columnDefs,initComplete:filterTable,ajax:$.fn.dataTable.pipeline({url:url_inscriptions_enabled,pages:5})});$('a[data-toggle="tab"]').on("shown.bs.tab",(function(){$.fn.dataTable.tables({visible:!0,api:!0}).columns.adjust()})),$("#active_table tbody").on("click","td.details-control",(function(){let e=$(this).closest("tr"),a=t.row(e);onClickDetails(e,a)})),$("#active_table tbody").on("click","a.edit_inscription",(function(){let t=$(this),e=$("#form_create");e.clearForm(),$.get(t.data("href"),(function(a){$("#modal_title").html(`Actualizar Inscripción: ${a.unique_code}`),e.attr("action",t.data("update")),0===e.find("#method").length&&e.prepend("<input name='_method' value='PUT' type='hidden' id='method'>"),$("#form_create #unique_code").val(a.unique_code).attr("readonly",!0),$("#form_create #member_name").val(a.player.full_names),$("#form_create #player_id").val(a.player_id),$("#form_create #start_date").val(a.start_date).attr("readonly",!0),$("#form_create #training_group_id").val(a.training_group_id).trigger("change"),$("#form_create #competition_group_id").val(a.competition_group_id).trigger("change"),$("#form_create #photos").prop("checked",1==a.photos),$("#form_create #copy_identification_document").prop("checked",1==a.copy_identification_document),$("#form_create #eps_certificate").prop("checked",1==a.eps_certificate),$("#form_create #medic_certificate").prop("checked",1==a.medic_certificate),$("#form_create #study_certificate").prop("checked",1==a.study_certificate),$("#form_create #overalls").prop("checked",1==a.overalls),$("#form_create #ball").prop("checked",1==a.ball),$("#form_create #bag").prop("checked",1==a.bag),$("#form_create #presentation_uniform").prop("checked",1==a.presentation_uniform),$("#form_create #competition_uniform").prop("checked",1==a.competition_uniform),$("#form_create #tournament_pay").prop("checked",1==a.tournament_pay),$("#create_inscription").modal("show"),$("#btn_add_inscription").attr("disabled",!1)})).fail((function(){Swal.fire({title:app_name,text:"No Tienes Los Permisos Suficientes.",type:"info"})}))})),$(".create_inscription").on("click",(function(){let t=$("#form_create");$("#modal_title").html("Nueva Inscripción"),t.attr("action",urlCreate),t.find("#method").remove(),$("#form_create #start_date").attr("disabled",!1),t.clearForm(),$("#btn_add_inscription").attr("disabled",!0)}))}));
