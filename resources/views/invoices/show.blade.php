@extends('layouts.app')
@section('title', 'Facturas')
@section('content')
<x-bread-crumb title="Factura #{{ $invoice->invoice_number }}" :option="0" />

<div class="container-fluid">
    <div class="row ">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-themecolor float-left">Factura #{{ $invoice->invoice_number }}</h4>
                    <h4 class="float-right">
                        @if($invoice->status == 'paid')
                        <span class="badge badge-success ">Pagada</span>
                        @elseif($invoice->status == 'partial')
                        <span class="badge badge-warning float-right">Parcial</span>
                        @elseif($invoice->status == 'pending')
                        <span class="badge badge-danger float-right">Pendiente</span>
                        @else
                        <span class="badge badge-secondary">Cancelada</span>
                        @endif
                    </h4>
                </div>

                <div class="card-body">

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5>Información del Deportista</h5>
                            <p><strong>Nombre:</strong> {{ $invoice->student_name }}</p>
                            <p><strong>Grupo:</strong> {{ $invoice->trainingGroup->name ?? 'N/A' }}</p>
                            <p><strong>Año:</strong> {{ $invoice->year }}</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <h5>Detalles de Factura</h5>
                            <p><strong>Fecha Emisión:</strong> {{ $invoice->issue_date->format('d-m-Y') }}</p>
                            <p><strong>Fecha Vencimiento:</strong> {{ $invoice->due_date->format('d-m-Y') }}</p>
                            <p><strong>Creada por:</strong> {{ $invoice->creator->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Ítems de la factura -->
                    <div class="table-responsive-md">
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-right">Precio Unitario</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                <tr>
                                    <td>
                                        @if($item->type == 'monthly')
                                        <span class="badge badge-info">Mensualidad</span>
                                        @else
                                        <span class="badge badge-secondary">Item</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">${{ number_format($item->unit_price, 0) }}</td>
                                    <td class="text-right">${{ number_format($item->total, 0) }}</td>
                                    <td class="text-center">
                                        @if($item->is_paid)
                                        <span class="badge badge-success">Pagado</span>
                                        @else
                                        <span class="badge badge-warning">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td colspan="4" class="text-right"><strong>Total Factura:</strong></td>
                                    <td class="text-right"><strong>${{ number_format($invoice->total_amount, 0) }}</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>Pagado:</strong></td>
                                    <td class="text-right"><strong>${{ number_format($invoice->paid_amount, 0) }}</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>Saldo Pendiente:</strong></td>
                                    <td class="text-right">
                                        <strong class="{{ $invoice->total_amount - $invoice->paid_amount > 0 ? 'text-danger' : 'text-success' }}">
                                            ${{ number_format($invoice->total_amount - $invoice->paid_amount, 0) }}
                                        </strong>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($invoice->notes)
                    <div class="alert alert-info">
                        <h6 class="text-themecolor"><i class="fas fa-sticky-note"></i> Notas:</h6>
                        <p>{{ $invoice->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Solicitudes de pago -->
            @if($invoice->paymentRequests->count() > 0)
            <div class="card">
                <div class="card-body">
                    <h5 class="text-themecolor">
                        <i class="fas fa-history"></i> Solicitudes de Pago
                        <small class="text-muted">"Enviadas desde la App GOLAPPLINK"</small>
                        <p>
                            Información importante
                            <i class="fas fa-question-circle text-muted"
                            data-toggle="tooltip"
                            data-placement="right"
                            title="Revisa las solicitudes, para poder registrar los pagos por su valor."></i>
                        </p>
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Enviado en</th>
                                    <th>Método</th>
                                    <th>Referencia</th>
                                    <th class="text-right">Monto</th>
                                    <th>Comprobante</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->paymentRequests as $paymentRequest)
                                <tr>
                                    <td>{{ $paymentRequest->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        @if($paymentRequest->payment_method == 'cash')
                                        <span class="badge badge-success">Efectivo</span>
                                        @elseif($paymentRequest->payment_method == 'card')
                                        <span class="badge badge-primary">Tarjeta</span>
                                        @elseif($paymentRequest->payment_method == 'transfer')
                                        <span class="badge badge-info">Transferencia</span>
                                        @else
                                        <span class="badge badge-secondary">{{ $paymentRequest->payment_method }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $paymentRequest->reference_number ?? 'N/A' }}</td>
                                    <td class="text-right">${{ number_format($paymentRequest->amount, 0) }}</td>
                                    <td>
                                        <a href="javascript:void(0)"
                                            data-toggle="modal"
                                            data-target="#imageModal"
                                            data-image="{{ $paymentRequest->url_image }}"
                                            data-title="{{ $paymentRequest->reference_number }}"
                                            alt="{{ $paymentRequest->reference_number }}">Ver</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif


            <!-- Historial de pagos -->
            @if($invoice->payments->count() > 0)
            <div class="card">
                <div class="card-body">
                    <h5 class="text-themecolor"><i class="fas fa-history"></i> Historial de Pagos</h5>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Método</th>
                                    <th>Referencia</th>
                                    <th class="text-right">Monto</th>
                                    <th>Registrado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->payments as $payment)
                                <tr>
                                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                    <td>
                                        @if($payment->payment_method == 'cash')
                                        <span class="badge badge-success">Efectivo</span>
                                        @elseif($payment->payment_method == 'card')
                                        <span class="badge badge-primary">Tarjeta</span>
                                        @elseif($payment->payment_method == 'transfer')
                                        <span class="badge badge-info">Transferencia</span>
                                        @else
                                        <span class="badge badge-secondary">{{ $payment->payment_method }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $payment->reference ?? 'N/A' }}</td>
                                    <td class="text-right">${{ number_format($payment->amount, 0) }}</td>
                                    <td>{{ $payment->creator->name ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Panel de pagos -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="text-themecolor"><i class="fas fa-money-bill-wave"></i> Registrar Pago</h5>
                </div>
                <div class="card-body col-md-12">
                    <form action="{{ route('invoices.addPayment', $invoice->id) }}" method="POST" id="paymentForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label>Monto a Pagar *</label>
                                    <input type="number" class="form-control" name="amount" id="paymentAmount"
                                        step="0.01" min="0.01" max="{{ $invoice->total_amount - $invoice->paid_amount }}"
                                        value="0" required readonly>
                                    <small class="text-muted" id="amountHelper">Seleccione ítems para calcular el monto</small>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label>Método de Pago *</label>
                                    <select class="form-control" name="payment_method" required>
                                        <option value="cash">Efectivo</option>
                                        <option value="card">Tarjeta</option>
                                        <option value="transfer">Transferencia</option>
                                        <option value="check">Cheque</option>
                                        <option value="other">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label>Referencia</label>
                                    <input type="text" class="form-control" name="reference"
                                        placeholder="Nº de transacción, cheque, etc.">
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label>Fecha del Pago *</label>
                                    <input type="date" class="form-control" name="payment_date"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Notas</label>
                                    <textarea class="form-control" name="notes" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <!-- Selección de ítems a marcar como pagados (opcional) -->
                                @if($invoice->items()->where('is_paid', false)->exists())
                                <div class="form-group">
                                    <label>Marcar ítems como pagados:</label>
                                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background-color: #f8f9fa;" id="itemsContainer">
                                        @foreach($invoice->items()->where('is_paid', false)->get() as $item)
                                        <div class="form-check mb-2 item-checkbox-container">
                                            <input type="checkbox" class="form-check-input item-checkbox"
                                                name="paid_items[]" value="{{ $item->id }}"
                                                id="item_{{ $item->id }}"
                                                data-amount="{{ $item->total }}"
                                                data-description="{{ $item->description }}"
                                                data-type="{{ $item->type }}"
                                                data-month="{{ $item->month ?? '' }}">
                                            <label class="form-check-label" for="item_{{ $item->id }}">
                                                <span class="item-description">{{ $item->description }}</span>
                                                - <span class="item-amount">${{ number_format($item->total, 0) }}</span>
                                                {{--@if($item->type == 'monthly' && $item->month)
                                                <small class="text-muted">(Mensualidad)</small>
                                                @endif--}}
                                            </label>
                                        </div>
                                        @endforeach

                                        <!-- Botones de selección rápida -->
                                        <div class="mt-3 text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary mr-1" id="selectAllItems">
                                                <i class="fas fa-check-square"></i> Seleccionar Todos
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllItems">
                                                <i class="fas fa-times-circle"></i> Deseleccionar Todos
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Resumen de selección -->
                                    <div class="mt-3" id="selectionSummary" style="display: none;">
                                        <h6><i class="fas fa-list-check"></i> Resumen de Selección</h6>
                                        <div id="selectedItemsList" class="small text-muted mb-2">
                                            <!-- Los ítems seleccionados aparecerán aquí -->
                                        </div>
                                        <div class="alert alert-info py-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span><strong>Total Seleccionado:</strong></span>
                                                <span id="selectedTotal" class="font-weight-bold">$0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i> Todos los ítems ya están pagados.
                                </div>
                                @endif
                            </div>

                            <div class="col-md-12 mb-2">
                                <button type="submit" class="btn btn-success btn-block" id="submitPaymentBtn">
                                    <i class="fas fa-check-circle"></i> Registrar Pago
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="row">
                        <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
                            <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-block mb-2">
                                <i class="fas fa-list"></i> Volver al Listado
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
                            <a href="{{ $invoice->url_print }}" class="btn btn-info btn-block mb-2" target="_blank">
                                <i class="fas fa-print"></i> Imprimir Factura
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
                            <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST"
                                onsubmit="return confirm('¿Está seguro de eliminar esta factura?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block">
                                    <i class="fas fa-trash"></i> Eliminar Factura
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('modals')
    @include('modals.image')
@endsection
@push('scripts')
<script>
$(document).ready(function() {
    // Variables globales
    let selectedItems = [];
    let totalAmount = 0;
    const maxAmount = parseFloat({{ $invoice->total_amount - $invoice->paid_amount }});
    const unpaidItems = {!! $invoice->items->where('is_paid', false) !!};

    // Inicializar array de ítems disponibles
    const availableItems = unpaidItems.map(item => ({
        id: item.id,
        description: item.description,
        amount: parseFloat(item.total),
        type: item.type,
        month: item.month || '',
        isSelected: false
    }));

    // Función para actualizar el resumen
    function updateSelectionSummary() {
        selectedItems = availableItems.filter(item => item.isSelected);
        totalAmount = selectedItems.reduce((sum, item) => sum + item.amount, 0);

        // Actualizar campo amount
        $('#paymentAmount').val(formatMoney(totalAmount));

        // Actualizar helper text
        if (selectedItems.length > 0) {
            $('#amountHelper').html(`
                <i class="fas fa-info-circle"></i>
                ${selectedItems.length} ítem(s) seleccionado(s) - Total: $${formatMoney(totalAmount)}
            `);
            $('#selectionSummary').show();
        } else {
            $('#amountHelper').html('Seleccione ítems para calcular el monto');
            $('#selectionSummary').hide();
        }

        // Actualizar lista de ítems seleccionados
        updateSelectedItemsList();

        // Actualizar total en resumen
        $('#selectedTotal').text('$' + formatMoney(totalAmount));

        // Validar si se puede enviar el formulario
        validateForm();
    }

    // Función para actualizar la lista de ítems seleccionados
    function updateSelectedItemsList() {
        const listContainer = $('#selectedItemsList');
        listContainer.empty();

        if (selectedItems.length === 0) {
            listContainer.html('<p class="text-muted">No hay ítems seleccionados</p>');
            return;
        }

        const list = $('<ul class="list-unstyled mb-0"></ul>');

        selectedItems.forEach(item => {
            let itemText = item.description;
            // if (item.type === 'monthly' && item.month) {
            //     itemText += ` <small class="text-muted">(${item.month})</small>`;
            // }
            itemText += ` - <strong>$${formatMoney(item.amount)}</strong>`;

            list.append(`
                <li class="mb-1">
                    <i class="fas fa-check text-success mr-1"></i>
                    ${itemText}
                </li>
            `);
        });

        listContainer.append(list);
    }

    // Función para validar el formulario
    function validateForm() {
        const submitBtn = $('#submitPaymentBtn');

        if (totalAmount <= 0) {
            submitBtn.prop('disabled', true).html('<i class="fas fa-ban"></i> Seleccione al menos un ítem');
            submitBtn.removeClass('btn-success').addClass('btn-secondary');
        } else if (totalAmount > maxAmount) {
            submitBtn.prop('disabled', true).html('<i class="fas fa-exclamation-triangle"></i> Monto excede el saldo');
            submitBtn.removeClass('btn-success').addClass('btn-warning');
        } else {
            submitBtn.prop('disabled', false).html('<i class="fas fa-check-circle"></i> Registrar Pago');
            submitBtn.removeClass('btn-secondary btn-warning').addClass('btn-success');
        }
    }

    // Evento para checkboxes de ítems
    $(document).on('change', '.item-checkbox', function() {
        const itemId = parseInt($(this).val());
        const isChecked = $(this).is(':checked');

        // Encontrar y actualizar el ítem en el array
        const itemIndex = availableItems.findIndex(item => item.id === itemId);
        if (itemIndex !== -1) {
            availableItems[itemIndex].isSelected = isChecked;

            // Actualizar estilo visual del contenedor
            const container = $(this).closest('.item-checkbox-container');
            if (isChecked) {
                container.addClass('bg-light border-left border-primary pl-2');
            } else {
                container.removeClass('bg-light border-left border-primary pl-2');
            }

            // Actualizar resumen
            updateSelectionSummary();
        }
    });

    // Botón para seleccionar todos
    $('#selectAllItems').click(function() {
        $('.item-checkbox').prop('checked', true).trigger('change');
        $(this).addClass('btn-primary').removeClass('btn-outline-primary');
        $('#deselectAllItems').addClass('btn-secondary').removeClass('btn-outline-secondary');
    });

    // Botón para deseleccionar todos
    $('#deselectAllItems').click(function() {
        $('.item-checkbox').prop('checked', false).trigger('change');
        $(this).addClass('btn-secondary').removeClass('btn-outline-secondary');
        $('#selectAllItems').addClass('btn-primary').removeClass('btn-outline-primary');
    });

    // Permitir modificación manual del monto (opcional)
    $('#paymentAmount').on('input', function() {
        const manualAmount = parseFloat($(this).val()) || 0;

        if (manualAmount > maxAmount) {
            $(this).val(maxAmount.toFixed(0));
            alert('El monto no puede exceder el saldo pendiente de $' + formatMoney(maxAmount));
            updateSelectionSummary();
            return;
        }

        // Si el usuario modifica manualmente, deseleccionar todos los checkboxes
        // y actualizar la lógica si es necesario
        if (manualAmount !== totalAmount) {
            // Opcional: Aquí podrías implementar lógica para ajustar checkboxes
            // automáticamente según el monto ingresado
            // Por ahora, solo actualizamos el total
            totalAmount = manualAmount;
            updateSelectionSummary();
        }
    });

    // Validar envío del formulario
    $('#paymentForm').submit(function(e) {
        if (totalAmount <= 0) {
            e.preventDefault();
            alert('Por favor, seleccione al menos un ítem para pagar.');
            return false;
        }

        if (totalAmount > maxAmount) {
            e.preventDefault();
            alert('El monto seleccionado excede el saldo pendiente. Por favor, ajuste la selección.');
            return false;
        }

        // Confirmar pago
        if (!confirm(`¿Desea registrar el pago de $${formatMoney(totalAmount)}?`)) {
            e.preventDefault();
            return false;
        }

        // Mostrar indicador de procesamiento
        $('#submitPaymentBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
    });

    // Inicializar validación
    validateForm();

    // Función adicional: Selección por tipo de ítem
    function selectByType(type) {
        $('.item-checkbox').each(function() {
            const itemType = $(this).data('type');
            if (itemType === type) {
                $(this).prop('checked', true).trigger('change');
            }
        });
    }

    // Función adicional: Selección por mes
    function selectByMonth(month) {
        $('.item-checkbox').each(function() {
            const itemMonth = $(this).data('month');
            if (itemMonth === month) {
                $(this).prop('checked', true).trigger('change');
            }
        });
    }

    $('#imageModal').on('shown.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var imageUrl = button.data('image');
        var imageTitle = button.data('title');

        $(this).find('#modalImage').attr('src', imageUrl);
        $(this).find('#imageTitle').text('Referencia: ' + imageTitle);
    });

    // Botones de selección rápida por tipo (opcional - puedes agregarlos si lo necesitas)
    // Ejemplo: agregar botones para seleccionar solo matrículas o solo mensualidades
});


</script>

<style>
.item-checkbox-container {
    transition: all 0.2s ease;
    padding: 5px;
    border-radius: 4px;
}

.item-checkbox-container:hover {
    background-color: #f0f8ff;
}

.item-checkbox-container.bg-light {
    background-color: #e8f4fd !important;
    border-left: 3px solid #007bff !important;
}

#selectedItemsList ul {
    max-height: 150px;
    overflow-y: auto;
}

#selectedItemsList li {
    padding: 3px 0;
    border-bottom: 1px solid #f0f0f0;
}

#selectedItemsList li:last-child {
    border-bottom: none;
}

#submitPaymentBtn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

#amountHelper {
    display: block;
    margin-top: 5px;
    font-size: 0.85em;
}
</style>
@endpush