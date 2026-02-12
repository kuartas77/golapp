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
                                <th width="5%">
                                    <input type="checkbox" name="select_all" id="select_all" class="include-all" checked data-index="all">
                                    <label for="select_all" class="checkboxsizeletter"></label>
                                </th>
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
                    @if(filter_var($settings->get('SYSTEM_NOTIFY'), FILTER_VALIDATE_BOOLEAN))
                    <small class="text-muted">En esta sección se agregarán las solicitudes de uniformes desde GOLAPPLINK. Además se pueden agregar otros items para la factura.</small>
                    @else
                    <small class="text-muted">En esta sección se pueden agregar otros items para la factura.</small>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th width="5%" class="text-center">
                                    <input type="checkbox" id="select_all_custom" checked>
                                    <label for="select_all_custom" class="checkboxsizeletter"></label>
                                </th>
                                <th width="25%">Descripción</th>
                                <th width="15%">Cantidad</th>
                                <th width="20%">Precio Unitario</th>
                                <th width="20%">Total</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody id="additionalItemsBody">
                            @if(count($customItems) > 0)
                            @foreach($customItems as $customItem)
                            <tr class="item-row" data-index="{{$loop->index}}">
                                <td class="text-center">
                                    <input
                                        type="checkbox"
                                        class="include-custom"
                                        id="custom_include_{{$loop->index}}"
                                        name="items[{{$loop->index}}][include]"
                                        checked
                                    >
                                    <label for="custom_include_{{$loop->index}}" class="checkboxsizeletter"></label>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm item-description" name="items[{{$loop->index}}][description]" placeholder="Descripción del ítem" required="" value="{{$customItem->name}}">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm item-quantity" name="items[{{$loop->index}}][quantity]" value="1" min="1" required="">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm item-unit-price" name="items[{{$loop->index}}][unit_price]" value="{{intval($customItem->unit_price)}}" step="1" min="1" required="">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm item-total" value="0" readonly="">
                                    <input type="hidden" name="items[{{$loop->index}}][type]" value="additional">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger remove-item">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                            @endif

                            @if(count($pendingUniformRequests) > 0)
                            @foreach($pendingUniformRequests as $uniformRequest)
                            <tr class="item-row" data-index="{{$loop->index}}">
                                <td >
                                    <p class="text-center">
                                        <i class="fas fa-question-circle text-muted"
                                        data-toggle="tooltip"
                                        data-placement="right"
                                        title="Estos Items fueron solicitados desde GOLAPPLINK"></i>
                                    </p>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm item-description" name="items[{{$loop->index}}][description]" placeholder="Descripción del ítem" required="" value="{{$uniformRequest['description']}}">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm item-quantity" name="items[{{$loop->index}}][quantity]" value="{{$uniformRequest['quantity']}}" min="1" required="">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm item-unit-price" name="items[{{$loop->index}}][unit_price]" value="{{$uniformRequest['unit_price']}}" step="1" min="1" required="">
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
                            <!-- Los ítems adicionales se agregarán aquí -->

                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-success" id="addItemBtn">
                    <i class="fas fa-plus"></i> Agregar Ítem
                </button>
            </div>
        </div>

        <!-- Sección de custom items -->

        <!-- <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-plus-circle"></i> Ítems personalizados
                    <small class="text-muted">Se mostrarán los items personalizados con su precio unitario, en cualquier factura se agregarán si no los requieres eliminalos de la factura.</small>
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
                        <tbody id="customItemsBody">



                        </tbody>
                    </table>
                </div>
            </div>
        </div> -->


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
            <td></td>
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



        // Aplica el estado visual/disabled según el checkbox (custom quedarán deshabilitados)
        $('.pending-month-row .include-item, .custom-item-row .include-custom').each(function() {
            toggleRow($(this).closest('tr'), $(this).is(':checked'));
        });
        syncSelectAll('#select_all', '.include-item');
        syncSelectAll('#select_all_custom', '.include-custom');

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

        // Manejar checkbox "Seleccionar todos"
        $('#select_all').change(function() {
            const isChecked = $(this).prop('checked');

            // Seleccionar/deseleccionar todos los checkboxes de meses pendientes
            $('.include-item').prop('checked', isChecked).trigger('change');
        });

        // Actualizar estado de "Seleccionar todos" cuando cambien checkboxes individuales
        $(document).on('change', '.include-item, .include-custom', function() {
            const row = $(this).closest('tr');
            const checked = $(this).is(':checked');

            toggleRow(row, checked);

            // Sync de "seleccionar todos"
            syncSelectAll('#select_all', '.include-item');
            syncSelectAll('#select_all_custom', '.include-custom');

            updateTotals();
        });

        $('#select_all_custom').change(function() {
            const isChecked = $(this).prop('checked');
            $('.include-custom').prop('checked', isChecked).trigger('change');
        });

        // Manejar checkboxes de meses pendientes
        $(document).on('change', '.include-item', function() {
            const row = $(this).closest('tr');

            if (!$(this).is(':checked')) {
                // Deshabilitar inputs cuando se desmarca el checkbox
                row.find('input.item-description, input.item-unit-price, input.item-quantity, input[type="hidden"]')
                .prop('disabled', true)
                .prop('readonly', true);
                row.addClass('table-secondary');
            } else {
                // Habilitar inputs cuando se marca el checkbox
                row.find('input.item-description, input.item-unit-price')
                .prop('disabled', false)
                .prop('readonly', false);

                row.find('input.item-quantity')
                .prop('disabled', false)
                .prop('readonly', true);

                row.find('input[type="hidden"]')
                .prop('disabled', false);

                row.removeClass('table-secondary');
            }

            updateTotals();
        });

        function rowIsIncluded(row) {
            // Si no tiene checkbox (ej: uniformes o items nuevos), siempre cuenta
            const cb = row.find('.include-item, .include-custom');
            return cb.length ? cb.is(':checked') : true;
        }

        function syncSelectAll(selectAllId, itemSelector) {
            const total = $(itemSelector).length;
            const checked = $(itemSelector + ':checked').length;
            $(selectAllId).prop('checked', total > 0 && total === checked);
        }

        function toggleRow(row, enabled) {
            // deshabilita todo excepto el checkbox y el botón de eliminar
            row.find(':input')
                .not('.include-item, .include-custom')
                .prop('disabled', !enabled);

            row.toggleClass('table-secondary', !enabled);

            if (!enabled) {
                row.find('.item-description, .item-unit-price, .item-quantity').prop('readonly', true);
                return;
            }

            // habilitado
            row.find('.item-description, .item-unit-price').prop('readonly', false);

            // mensualidades: quantity siempre readonly
            if (row.hasClass('pending-month-row')) {
                row.find('.item-quantity').prop('readonly', true);
            } else {
                row.find('.item-quantity').prop('readonly', false);
            }
        }

        // Inicializar estado de "Seleccionar todos"
        const totalItems = $('.include-item').length;
        const checkedItems = $('.include-item:checked').length;
        $('#select_all').prop('checked', totalItems === checkedItems);

        function calculateRowTotal(row) {
            const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
            const unitPrice = parseFloat(row.find('.item-unit-price').val()) || 0;
            const total = quantity * unitPrice;

            row.find('.item-total').val(total.toFixed(0));
            return total;
        }

        function updateTotals() {
            let subtotal = 0;

            // mensualidades (solo si están incluidas)
            $('.pending-month-row').each(function() {
                const row = $(this);
                if (rowIsIncluded(row)) {
                    subtotal += calculateRowTotal(row);
                }
            });

            // item-row (custom solo si está incluido; uniformes/nuevos siempre)
            $('.item-row').each(function() {
                const row = $(this);
                if (rowIsIncluded(row)) {
                    subtotal += calculateRowTotal(row);
                }
            });

            $('#subtotalAmount').text('$' + subtotal.toFixed(0));
            $('#totalAmount').text('$' + subtotal.toFixed(0));

            updateFormIndexes();
        }

        function updateFormIndexes() {
            let newIndex = 0;

            // mensualidades incluidas
            $('.pending-month-row').each(function() {
                const row = $(this);
                if (!rowIsIncluded(row)) return;

                row.find('[name]').each(function() {
                    const oldName = $(this).attr('name');
                    if (!oldName) return;
                    $(this).attr('name', oldName.replace(/items\[\d+\]/, `items[${newIndex}]`));
                });

                newIndex++;
            });

            // item-row incluidos (custom incluidos + otros siempre)
            $('.item-row').each(function() {
                const row = $(this);
                if (!rowIsIncluded(row)) return;

                row.find('[name]').each(function() {
                    const oldName = $(this).attr('name');
                    if (!oldName) return;
                    $(this).attr('name', oldName.replace(/items\[\d+\]/, `items[${newIndex}]`));
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