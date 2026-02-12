@extends('layouts.app')
@section('title', 'Escuela')
@section('content')
<x-bread-crumb title="Escuela" :option="0" />
<div class="row">
    <div class="col-md-12 col-sm-12 col-lg-12 col-xl-8">
        <div class="card m-b-2">
            <div class="card-body">
                {{html()->form('PUT', route('school.update', ['school' => $school]))->attributes(['id' => 'form_player', 'accept-charset' => 'UTF-8', 'enctype' => "multipart/form-data", 'class' => 'form-material m-t-0'])->open()}}
                <div class="form-body">
                    @include('admin.school.form')
                </div>
                <div class="form-actions m-t-0 text-center">
                    <button type="submit" class="btn waves-effect waves-light btn-rounded btn-info">Actualizar</button>
                </div>
                {{ html()->form()->close() }}
            </div>
        </div>
    </div>
    <div class="col-md-12 col-sm-12 col-lg-12 col-xl-4">
        <div class="card m-b-0">
            <div class="card-body">
                <strong>En el listado aparecerán los items que por defecto se agregarán a cualquier factura.</strong>
                <a class="float-right btn waves-effect waves-light btn-rounded btn-info"
                href="javascript:void(0)" data-toggle="modal" data-target="#modal_invoice_custom_item" data-backdrop="static" data-keyboard="false" id="add_custom_item_invoice">
                <i class="fa fa-plus" aria-hidden="true"></i>

            </a>
                <div class="table-responsive-md">
                    <table class="display compact cell-border" id="invoiceItemCustomTable">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Precio unitario</th>
                                <th>Creado</th>
                                <th></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('modals')
@include('modals.invoice_custom_item')
@endsection
@section('scripts')
<script>
    $(document).ready(function() {
        $('.money').inputmask("pesos");
        $('.notify_day').inputmask('numeric', {
            min: 1,
            max: 31
        });

        const urlCustomItemInvoices = "{{route('invoice-items-custom.index')}}";
        let active_table = $('#invoiceItemCustomTable');

        active_table = $('#invoiceItemCustomTable').DataTable({
            "lengthMenu": [
                [10, 30, 50, 70, 100],
                [10, 30, 50, 70, 100]
            ],
            "order": [
                [1, "desc"]
            ],
            "processing": true,
            "serverSide": true,
            "deferRender": true,
            "fixedColumns": true,
            "columns": [{
                    data: 'name',
                    name: 'name',
                    searchable: true,
                    orderable: true
                },
                {
                    data: 'unit_price',
                    searchable: false,
                    orderable: true,
                    render: (data, type, row) => `$${formatMoney(data)}`
                },
                {
                    data: 'created_at',
                    searchable: false,
                    render: (data, type, row) => moment(data).format('DD-MM-YYYY')
                },
                {
                    data: 'id',
                    searchable: false,
                    orderable: false,
                    render: function(data, type, row) {
                        let edit = '<a href="javascript:void(0)" data-toggle="modal" data-target="#modal_invoice_custom_item" data-backdrop="static"\n' +
                                'data-keyboard="false" data-href="' + row.url_show + '" data-update="'+row.url_show+'" class="btn btn-warning btn-xs update_custom_item_invoice" title="Modificar"><i class="fas fa-pencil-alt"></i></a>'
                        let deleteButton = '<button class="btn btn-danger btn-xs disable-inscription" title="Eliminar"><i class="fas fa-trash-alt"></i></button>'

                        return '<form method="POST" action="' + row.url_show + '" accept-charset="UTF-8"><input name="_method" type="hidden" value="DELETE"><input name="_token" type="hidden" value="' + window.token.csrfToken + '">'
                        + '<div class="btn-group">'
                        + edit
                        + deleteButton
                        + '</div>'
                        +'</form>';
                    }
                },
            ],
            "ajax": $.fn.dataTable.pipeline({
                url: urlCustomItemInvoices,
                pages: 5 // number of pages to cache
            }),
        })
    });

    function readFile(input) {
        let label = $(input).next('label.custom-file-label')
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                $('#player-img').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
            // label.empty().html(input.files[0].name)
            label.empty().html('Seleccionada.')
        } else {
            label.empty().html("Seleccionar...")
            $('#player-img').attr('src', 'https://app.golapp.com.co/img/ballon.png');
        }
    }
    $('#file-upload').on('change', function() {
        readFile(this);
    });
</script>
@endsection