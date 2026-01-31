@extends('layouts.app')
@section('title', 'Facturas')
@section('content')
<x-bread-crumb title="Crear Factura" :option="0" />
<x-row-card col-inside="6" col-outside="3">


    <div class="row mb-4">
        <div class="col-md-6">
            <h5>Información del Estudiante</h5>
            <p><strong>Nombre:</strong> {{ $inscription->player->full_names }}</p>
            <p><strong>Grupo:</strong> {{ $inscription->trainingGroup->name }}</p>
            <p><strong>Año:</strong> {{ date('Y') }}</p>
        </div>
        <div class="col-md-6 text-right">
            <h5>Factura #</h5>
            <p class="text-muted">Se generará automáticamente</p>
        </div>
    </div>

    <form id="invoiceForm" action="{{ route('invoices.store') }}" method="POST">
        @csrf
        <input type="hidden" name="inscription_id" value="{{ $inscription->id }}">
        <input type="hidden" name="training_group_id" value="{{ $inscription->training_group_id }}">
        <input type="hidden" name="year" value="{{ date('Y') }}">
        <input type="hidden" name="student_name" value="{{ $inscription->player->full_names }}">

        <!-- Sección de meses pendientes -->
        @if(count($pendingMonths) > 0)
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt"></i> Meses Pendientes
                    <small class="text-muted">(Desde Mensualidades) Hasta que no tengan estado pagado seguiran apareciendo en las facturas nuevas, presta atencíon ya que se puede facturar varias veces.</small>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%">Incluir</th>
                                <th width="25%">Mes</th>
                                <th width="20%">Descripción</th>
                                <th width="15%">Cantidad</th>
                                <th width="15%">Precio Unitario</th>
                                <th width="15%">Total</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="pendingMonthsBody">
                            @foreach($pendingMonths as $index => $month)
                            <tr class="pending-month-row" data-type="monthly">
                                <td class="text-center">

                                    <!-- <div class="checkbox"> -->
                                        <input type="checkbox" name="items[{{ $index }}][include]" id="items[{{ $index }}][include]" class="include-item" checked data-index="{{ $index }}">
                                        <label for="items[{{ $index }}][include]" class="checkboxsizeletter"></label>
                                    <!-- </div> -->
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm"
                                        value="{{ $month['name'] }}" readonly>
                                    <input type="hidden" name="items[{{ $index }}][month]"
                                        value="{{ $month['month'] }}">
                                    <input type="hidden" name="items[{{ $index }}][payment_id]"
                                        value="{{ $month['payment_id'] }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm item-description"
                                        name="items[{{ $index }}][description]"
                                        value="{{ $month['name'] }}" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm item-quantity"
                                        name="items[{{ $index }}][quantity]" value="1" min="1" readonly>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm item-unit-price"
                                        name="items[{{ $index }}][unit_price]"
                                        value="{{ $month['amount'] }}" step="0.01" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm item-total"
                                        value="{{ $month['amount'] }}" step="0.01" readonly>
                                </td>
                                <td>
                                    <input type="hidden" name="items[{{ $index }}][type]" value="monthly">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Sección de ítems adicionales -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-plus-circle"></i> Ítems
                    <small class="text-muted">Uniformes, balones, rifas ...</small>
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th width="25%">Descripción</th>
                                <th width="15%">Cantidad</th>
                                <th width="20%">Precio Unitario</th>
                                <th width="20%">Total</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="additionalItemsBody">
                            <!-- Los ítems adicionales se agregarán aquí -->

                            @if(count($pendingUniformRequests) > 0)
                            @foreach($pendingUniformRequests as $uniformRequest)
                            <tr class="item-row" data-index="{{$loop->index}}">
                                <td>
                                    <input type="text" class="form-control form-control-sm item-description" name="items[{{$loop->index}}][description]" placeholder="Descripción del ítem" required="" value="{{$uniformRequest['description']}}">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm item-quantity" name="items[{{$loop->index}}][quantity]" value="{{$uniformRequest['quantity']}}" min="1" required="">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm item-unit-price" name="items[{{$loop->index}}][unit_price]" value="0" step="1" min="1" required="">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm item-total" value="0" readonly="">
                                    <input type="hidden" name="items[{{$loop->index}}][type]" value="additional">
                                    <input type="hidden" name="items[{{$loop->index}}][uniform_request_id]" value="{{$uniformRequest['uniform_request_id']}}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            @endif

                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                    <i class="fas fa-plus"></i> Agregar Ítem
                </button>
            </div>
        </div>

        <!-- Información de la factura -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información de Factura</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Fecha de Vencimiento</label>
                            <input type="date" class="form-control" name="due_date"
                                value="{{ date('Y-m-d', strtotime('+15 days')) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Notas</label>
                            <textarea class="form-control" name="notes" rows="3"
                                placeholder="Observaciones adicionales..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Totales -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0"><i class="fas fa-calculator"></i> Totales</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <th class="text-right">Subtotal:</th>
                                <td width="150" class="text-right">
                                    <span id="subtotalAmount">$0.00</span>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-right">Total Factura:</th>
                                <td class="text-right">
                                    <h4 id="totalAmount">$0.00</h4>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group text-right">
            <a href="{{route('inscriptions.index')}}"
                class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Factura
            </button>
        </div>
    </form>
</x-row-card>

@endsection
@push('styles')
<style>
    .item-row:hover {
        background-color: #f8f9fa;
    }

    .form-control-sm {
        height: calc(1.5em + .5rem + 2px);
        padding: .25rem .5rem;
        font-size: .875rem;
        line-height: 1.5;
    }
</style>
@endpush

@push('scripts')
<script>
    let itemIndex = {{count($pendingMonths)}};
    let additionalItemIndex = 0;

    $(document).ready(function() {
        // Plantilla para ítem adicional
        const itemTemplate = `
    <tr class="item-row" data-index="__INDEX__">
        <td>
            <input type="text" class="form-control form-control-sm item-description"
                   name="items[__INDEX__][description]" placeholder="Descripción del ítem" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-quantity"
                   name="items[__INDEX__][quantity]" value="1" min="1" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-unit-price"
                   name="items[__INDEX__][unit_price]" value="0" step="0.01" min="0" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-total" value="0" readonly>
            <input type="hidden" name="items[__INDEX__][type]" value="additional">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-danger remove-item">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>`;

        // Agregar ítem adicional
        $('#addItemBtn').click(function() {
            const newItem = itemTemplate.replace(/__INDEX__/g, itemIndex);
            $('#additionalItemsBody').append(newItem);
            itemIndex++;
            updateTotals();
        });

        // Eliminar ítem
        $(document).on('click', '.remove-item', function() {
            $(this).closest('tr').remove();
            updateTotals();
        });

        // Calcular total cuando cambia cantidad o precio
        $(document).on('input', '.item-quantity, .item-unit-price', function() {
            const row = $(this).closest('tr');
            calculateRowTotal(row);
            updateTotals();
        });

        // Manejar checkboxes de meses pendientes
        $(document).on('change', '.include-item', function() {
            const row = $(this).closest('tr');

            if (!$(this).is(':checked')) {
                // Deshabilitar solo los inputs específicos que necesitamos, excluyendo el checkbox
                row.find('input.item-description, input.item-unit-price, input.item-quantity, input[type="hidden"]')
                .prop('disabled', true)
                .prop('readonly', true);
                row.addClass('table-secondary');
            } else {
                // Habilitar inputs con configuración específica
                row.find('input.item-description, input.item-unit-price')
                .prop('disabled', false)
                .prop('readonly', false);

                row.find('input.item-quantity')
                .prop('disabled', false)
                .prop('readonly', true);

                // Los hidden inputs también deben estar habilitados
                row.find('input[type="hidden"]')
                .prop('disabled', false);

                row.removeClass('table-secondary');
            }

            updateTotals();
        });

        function calculateRowTotal(row) {
            const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
            const unitPrice = parseFloat(row.find('.item-unit-price').val()) || 0;
            const total = quantity * unitPrice;

            row.find('.item-total').val(total.toFixed(0));
            return total;
        }

        function updateTotals() {
            let subtotal = 0;

            // Sumar meses pendientes incluidos
            $('.pending-month-row').each(function() {
                if ($(this).find('.include-item').is(':checked')) {
                    subtotal += calculateRowTotal($(this));
                }
            });

            // Sumar ítems adicionales
            $('.item-row').each(function() {
                subtotal += calculateRowTotal($(this));
            });

            // Actualizar display
            $('#subtotalAmount').text('$' + subtotal.toFixed(0));
            $('#totalAmount').text('$' + subtotal.toFixed(0));

            // Actualizar índices del formulario
            updateFormIndexes();
        }

        function updateFormIndexes() {
            let newIndex = 0;

            // Reindexar meses pendientes incluidos
            $('.pending-month-row').each(function() {
                if ($(this).find('.include-item').is(':checked')) {
                    $(this).find('input[name]').each(function() {
                        const oldName = $(this).attr('name');
                        if (oldName) {
                            const newName = oldName.replace(/items\[\d+\]/, `items[${newIndex}]`);
                            $(this).attr('name', newName);
                        }
                    });
                    newIndex++;
                }
            });

            // Reindexar ítems adicionales
            $('.item-row').each(function() {
                $(this).find('input[name], select[name]').each(function() {
                    const oldName = $(this).attr('name');
                    if (oldName) {
                        const newName = oldName.replace(/items\[\d+\]/, `items[${newIndex}]`);
                        $(this).attr('name', newName);
                    }
                });
                newIndex++;
            });

            itemIndex = newIndex;
        }

        // Inicializar
        updateTotals();
    });
</script>
@endpush