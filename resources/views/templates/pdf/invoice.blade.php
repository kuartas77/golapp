<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}" media="all">
    <title>Factura {{ $invoice->invoice_number }}</title>
<style>.badge {
            display: inline-block;
            padding: 5mm;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 2mm;
        }
        .badge-success { background-color: #28a745; color: #fff; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-danger { background-color: #dc3545; color: #fff; }
        .badge-secondary { background-color: #000000ff; color: #fff; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado con información de la empresa y la factura -->
        <table class="table-full title">
            <tr>
                <td class="text-left" width="20%">
                    <img src="{{ $school->logo_local }}" width="70" height="70">
                </td>
                <td class="text-center school-title" width="60%">{{ $school->name }}
                    <br>FACTURA
                    <br><strong>Nº: {{ $invoice->invoice_number }}</strong>
                </td>
                <td class="text-right" width="20%">
                    <img src="{{ $school->logo_local }}" width="70" height="70">
                </td>
            </tr>
            <!-- <tr class="tr-tit">
                <td class="text-center bold" width="45%">
                    <h3 class="school-title"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</h3>
                </td>
                <td class="text-center" width="10%"></td>
                <td class="text-center bold" width="45%">
                    <h3 class="school-title"><strong>Vencimiento:</strong> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</h3>
                </td>
            </tr> -->
        </table>

        <table class="table-full title">
            <tbody>
                <tr>
                    <td class="text-left" width="50%"><strong class="bold">&nbsp;{{ $school->name ?? 'INSTITUCIÓN EDUCATIVA' }}</strong></td>
                    <td class="text-right" width="50%"><strong class="bold">&nbsp;Fecha: {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</strong></td>
                </tr>
                <tr>
                    <td class="text-left" width="50%"><strong class="bold">&nbsp;{{ $school->address ?? 'Dirección no especificada' }}</strong></td>
                    <td class="text-right" width="50%"><strong class="bold">&nbsp;Vencimiento: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</strong></td>
                </tr>
                <tr>
                    <td class="text-left" width="50%"><strong class="bold">&nbsp;Tel: {{ $school->phone ?? 'N/A' }}</strong></td>
                    <td class="text-right" width="50%">
                        <strong>Estado:</strong>
                        @if($invoice->status == 'paid')
                        <span class="badge badge-success ">Pagada</span>
                        @elseif($invoice->status == 'partial')
                        <span class="badge badge-warning">Parcial</span>
                        @elseif($invoice->status == 'pending')
                        <span class="badge badge-danger">Pendiente</span>
                        @else
                        <span class="badge badge-secondary">Cancelada</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="text-left" width="50%"><strong class="bold">&nbsp;Email: {{ $school->email ?? 'N/A' }}</strong></td>
                    <td class="text-right" width="50%"><strong>Factura creada por:</strong> {{ $invoice->creator->name ?? 'SISTEMA' }}</td>
                </tr>
            </tbody>
        </table>

        <h3 style="margin-top:0;">DATOS DEL DEPORTISTA</h3>
        <table class="table-full title">
            <tbody>
                <tr>
                    <td class="text-left" width="50%"><strong>Nombre:</strong> {{ $invoice->student_name }}</td>
                    <td class="text-right" width="50%"><strong class="bold">&nbsp;<strong>Grupo:</strong> {{ $invoice->trainingGroup->name ?? 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <td class="text-left" width="50%"><strong>Documento:</strong> {{ $invoice->inscription->player->identification_document ?? 'N/A' }}</td>
                    <td class="text-right" width="50%"><strong class="bold">&nbsp;<strong>Año:</strong> {{ $invoice->year }}</strong></td>
                </tr>
                <tr>
                    <td class="text-left" width="50%"><strong>Teléfono:</strong> {{ $invoice->inscription->player->phones ?? 'N/A' }}</td>
                    <td class="text-right" width="50%"><strong>Email:</strong> {{ $invoice->inscription->player->email ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Tabla de productos/servicios -->
        <h3 style="margin-top:0;">DETALLE DE LA FACTURA</h3>
        <table class="table-full title ">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="45%">DESCRIPCIÓN</th>
                    <th width="10%">TIPO</th>
                    <th width="1%" class="text-center">CANT</th>
                    <th width="20%" class="text-right">PRECIO UNIT.</th>
                    <th width="19%" class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if($item->month)
                            <strong>Mes:</strong> {{ $item->description }}
                        @else
                            {{ $item->description }}
                        @endif
                    </td>
                    <td>
                        @if($item->type == 'monthly')
                            <span class="badge badge-secondary">MENSUALIDAD</span>
                        @elseif($item->type == 'enrollment')
                            <span class="badge badge-secondary">MATRÍCULA</span>
                        @else
                            <span class="badge badge-secondary">OTROS</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr style="margin-top: 10mm; padding-top: 5mm; border-top: 1px solid #ddd;">

        <!-- Totales -->
        <div >
            <table class="table-full title ">
                <tr>
                    <td><strong>SUBTOTAL:</strong></td>
                    <td class="text-right">${{ number_format($invoice->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>PAGADO:</strong></td>
                    <td class="text-right">${{ number_format($invoice->paid_amount, 2) }}</td>
                </tr>
                <tr style="background-color: #f5f5f5;">
                    <td><strong>SALDO PENDIENTE:</strong></td>
                    <td class="text-right">
                        <strong>${{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</strong>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Observaciones -->
        @if($invoice->notes)
        <div>
            <h3>OBSERVACIONES</h3>
            <div style="padding: 3mm; border: 1px solid #ddd; border-radius: 2mm; background-color: #f9f9f9;">
                {{ $invoice->notes }}
            </div>
        </div>
        @endif

        <!-- Historial de pagos (si existe) -->
        <!-- @if($invoice->payments && $invoice->payments->count() > 1)
        <div style="margin-top: 10mm;">
            <h3>HISTORIAL DE PAGOS</h3>
            <table>
                <thead>
                    <tr>
                        <th width="20%">FECHA</th>
                        <th width="20%">MÉTODO</th>
                        <th width="30%">REFERENCIA</th>
                        <th width="15%" class="text-right">MONTO</th>
                        <th width="15%">REGISTRADO POR</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</td>
                        <td>
                            @if($payment->payment_method == 'cash')
                                <span class="badge badge-success">EFECTIVO</span>
                            @elseif($payment->payment_method == 'card')
                                <span class="badge badge-primary">TARJETA</span>
                            @elseif($payment->payment_method == 'transfer')
                                <span class="badge badge-info">TRANSFERENCIA</span>
                            @else
                                <span class="badge badge-secondary">{{ strtoupper($payment->payment_method) }}</span>
                            @endif
                        </td>
                        <td>{{ $payment->reference ?? 'N/A' }}</td>
                        <td class="text-right">${{ number_format($payment->amount, 2) }}</td>
                        <td>{{ $payment->creator->name ?? 'SISTEMA' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif -->

        <!-- Información adicional -->
        <!-- <div style="margin-top: 10mm; padding-top: 5mm; border-top: 1px solid #ddd;">
            <div style="width: 50%; float: left;">
                <p><strong>Factura creada por:</strong> {{ $invoice->creator->name ?? 'SISTEMA' }}</p>
                <p><strong>Fecha de creación:</strong> {{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y H:i:s') }}</p>
            </div>
             <div style="width: 50%; float: right;">
                <p><strong>Condiciones:</strong></p>
                <p style="font-size: 8pt;">Esta factura es válida según las políticas de la institución. El pago después de la fecha de vencimiento puede generar recargos.</p>
            </div>
        </div> -->

        <!-- Pie de página -->
        <!-- <div class="footer clearfix">
            <p>{{ $school->name ?? 'Institución Educativa' }} - Factura generada electrónicamente</p>
            <p>Documento válido sin firma ni sello según Resolución DIAN 00000 de 2023</p>
            <p>Impreso el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}</p>
        </div> -->
    </div>
</body>
</html>