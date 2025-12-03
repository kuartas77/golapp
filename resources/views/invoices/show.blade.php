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
                                        <span class="badge badge-secondary">Adicional</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-right">${{ number_format($item->total, 2) }}</td>
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
                                    <td class="text-right"><strong>${{ number_format($invoice->total_amount, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>Pagado:</strong></td>
                                    <td class="text-right"><strong>${{ number_format($invoice->paid_amount, 2) }}</strong></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right"><strong>Saldo Pendiente:</strong></td>
                                    <td class="text-right">
                                        <strong class="{{ $invoice->total_amount - $invoice->paid_amount > 0 ? 'text-danger' : 'text-success' }}">
                                            ${{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}
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

            <!-- Historial de pagos -->
            @if($invoice->payments->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="text-themecolor"><i class="fas fa-history"></i> Historial de Pagos</h5>
                </div>
                <div class="card-body">
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
                                    <td class="text-right">${{ number_format($payment->amount, 2) }}</td>
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

        <div class="col-md-4">
            <!-- Panel de pagos -->
            <div class="card">
                <div class="card-header">
                    <h5 class="text-themecolor"><i class="fas fa-money-bill-wave"></i> Registrar Pago</h5>
                </div>
                <div class="card-body col-md-12">
                    <form action="{{ route('invoices.addPayment', $invoice->id) }}" method="POST">
                        @csrf
                        <div class="row ">
                            <div class="col-md-6 col-sm-6 col-lg-6 col-xs-12">
                                <div class="form-group">
                                    <label>Monto a Pagar *</label>
                                    <input type="number" class="form-control" name="amount"
                                        step="0.01" min="0.01" max="{{ $invoice->total_amount - $invoice->paid_amount }}"
                                        value="{{ $invoice->total_amount - $invoice->paid_amount }}" required>
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
                                    <label>Marcar ítems como pagados (opcional):</label>
                                    <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                                        @foreach($invoice->items()->where('is_paid', false)->get() as $item)
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input"
                                                name="paid_items[]" value="{{ $item->id }}" id="item_{{ $item->id }}">
                                            <label class="form-check-label" for="item_{{ $item->id }}">
                                                {{ $item->description }} - ${{ number_format($item->total, 2) }}
                                                @if($item->type == 'monthly' && $item->month)
                                                <small class="text-muted">({{ ucfirst($item->month) }})</small>
                                                @endif
                                            </label>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>

                            <div class="col-md-12 mb-2">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-check-circle"></i> Registrar Pago
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="row ">

                        <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
                            <a href="{{ route('invoices.index') }}" class="btn btn-secondary btn-block mb-2">
                                <i class="fas fa-list"></i> Volver al Listado
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-4 col-lg-4 col-xs-12">
                            <a href="{{$invoice->url_print}}" class="btn btn-info btn-block mb-2" target="_blank">
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