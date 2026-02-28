<div class="modal" id="modal_invoice_custom_item">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{route('invoice-items-custom.store')}}" id="form_invoice_custom_item" class="form-material m-t-0"
                method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title" id="modal_title">Items factura</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">

                    <div class="col-lg-12">
                        <div class="alert alert-info" role="alert">
                            <p>Items que por defecto se agregarán a cualquier factura, sólo podrás agregar un item de cada tipo: Uniformes, Balón, Medias, Guayos, Pantaloneta, Camisa.</p>
                        </div>
                        <div class="alert alert-danger hide" id="alert-error" role="alert"></div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="item_type">Item</label>
                                    <span class="bar"></span>
                                    {{ html()->select('item_type', $uniform_request_types, null)->attributes(['id'=>'item_type','class' => 'form-control form-control-sm','required'])->placeholder('Selecciona...') }}
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="item_name">Nombre</label>
                                    <span class="bar"></span>
                                    <input type="text" name="item_name" id="item_name" class="form-control form-control-sm" required
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="item_unit_price">Precio unitario</label>
                                    <span class="bar"></span>
                                    <input type="text" name="item_unit_price" id="item_unit_price" class="form-control form-control-sm money" required
                                        autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger waves-effect text-left" data-dismiss="modal">Cerrar
                    </button>
                    <button type="submit" class="btn btn-info waves-effect text-left">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
    $(document).ready(function() {

        $('#item_type').on('change', function() {
            const selectedValue = $(this).val()
            const selectedText = $(this).find("option:selected").text()

            // Select the input element
            var $myInput = $('#item_name');

            // Reset properties first to handle all cases;
            $myInput.prop('readonly', false);

            if (selectedValue !== 'OTHER') {
                $myInput.prop('readonly', true);
                $myInput.val(selectedText);
            } else {
                $myInput.val('');
            }

        })

        $("#add_custom_item_invoice").on('click', function() {
            let form = $("#form_invoice_custom_item");

            form.attr('action', urlCustomItemInvoices);
            form.find('#method').remove();
            form.clearForm();
            $("#form_invoice_custom_item #item_type").val('')
            $("#form_invoice_custom_item #item_name").val('').attr('readonly', false);
            $("#form_invoice_custom_item #item_unit_price").val('')

        })

        $('#invoiceItemCustomTable tbody').on('click', 'a.update_custom_item_invoice', function() {
            let btn = $(this);
            let form = $("#form_invoice_custom_item");
            form.clearForm();
            $.get(btn.data('href'), function(response) {
                form.attr('action', btn.data('update'));
                if (form.find('#method').length === 0) {
                    form.prepend("<input name='_method' value='PUT' type='hidden' id='method'>");
                }
                $("#form_invoice_custom_item #item_type").val(response.type);
                let itemName = $("#form_invoice_custom_item #item_name");
                itemName.val(response.name)
                if (response.type !== 'OTHER') {
                    itemName.attr('readonly', true);
                }

                $("#form_invoice_custom_item #item_unit_price").val(response.unit_price)
            })
        })


        $("#form_invoice_custom_item").validate()

    });
</script>
@endpush