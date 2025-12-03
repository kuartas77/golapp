<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}" media="all">
    <title>Factura {{ $invoice->invoice_number }}</title>
    <style>
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }

        a {
            color: #5D6975;
            text-decoration: underline;
        }

        body {
            position: relative;
            width: 21cm;
            height: 29.7cm;
            margin: 0 auto;
            color: #001028;
            background: #FFFFFF;
            font-family: Arial, sans-serif;
            font-size: 12px;
            font-family: Arial;
        }

        header {
            padding: 10px 0;
            margin-bottom: 30px;
        }

        #logo {
            text-align: center;
            margin-bottom: 10px;
        }

        #logo img {
            width: 90px;
        }

        h1 {
            border-top: 1px solid #5D6975;
            border-bottom: 1px solid #5D6975;
            color: #5D6975;
            font-size: 2.4em;
            line-height: 1.4em;
            font-weight: normal;
            text-align: center;
            margin: 0 0 20px 0;
            background: url(img/dimension.png);
        }

        /* TABLA para alinear perfectamente cliente y empresa */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .header-table td {
            vertical-align: top;
            /* padding: 0; */
            width: 50%;
        }

        #project {
            text-align: left; /* Aseguramos alineación izquierda */
        }

        #project span.project-label {
            color: #5D6975;
            text-align: right;
            width: 80px;
            margin-right: 10px;
            display: inline-block;
            font-size: 0.8em;
            font-weight: bold;
            vertical-align: top;
        }

        #company {
            text-align: right;
        }

        #project div,
        #company div {
            margin-bottom: 5px;
            line-height: 1.4;
            text-align: left; /* Aseguramos alineación izquierda para los divs de project */
        }

        /* Estructura de tabla dentro de project para mejor control */
        .project-row {
            display: table;
            width: 100%;
        }

        .project-label {
            display: table-cell;
            text-align: right;
            padding-right: 10px;
            width: 80px;
            color: #5D6975;
            font-weight: bold;
            font-size: 0.8em;
            vertical-align: top;
            white-space: nowrap;
        }

        .project-value {
            display: table-cell;
            vertical-align: top;
            text-align: left; /* Aseguramos alineación izquierda */
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            margin-bottom: 20px;
        }

        table tr:nth-child(2n-1) td {
            background: #F5F5F5;
        }

        table th,
        table td {
            text-align: center;
        }

        table th {
            padding: 5px 20px;
            color: #5D6975;
            border-bottom: 1px solid #C1CED9;
            white-space: nowrap;
            font-weight: normal;
        }

        table .service,
        table .desc {
            text-align: left;
        }

        table td {
            padding: 5px;
            text-align: right;
        }

        table td.service,
        table td.desc {
            vertical-align: top;
        }

        table td.unit,
        table td.qty,
        table td.total {
            font-size: 1.2em;
        }

        table td.grand {
            border-top: 1px solid #5D6975;
        }

        #notices .notice {
            padding: 3mm;

            background-color: #f9f9f9;
            font-size: 1.2em;
        }

        footer {
            color: #5D6975;
            width: 100%;
            height: 30px;
            position: absolute;
            bottom: 0;
            border-top: 1px solid #C1CED9;
            padding: 8px 0;
            text-align: center;
        }
        .badge {
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
    <header class="clearfix">
        <div id="logo">
            <img src="{{ $school->logo_local }}" width="70" height="70">
        </div>
        <h1>Factura #{{ $invoice->invoice_number }}</h1>

        <!-- TABLA para alinear perfectamente cliente y empresa -->
        <table class="header-table">
            <tr>
                <td style="text-align: left;">
                    <!-- Información del cliente -->
                    <div id="project">
                        @if($tutor?->names)
                        <div class="project-row">
                            <span class="project-label">&nbsp;ACUDIENTE</span>
                            <span class="project-value">&nbsp;{{$tutor->names}} CC:{{$tutor->identification_card}}</span>
                        </div>
                        @endif
                        <div class="project-row">
                            <span class="project-label">&nbsp;DEPORTISTA</span>
                            <span class="project-value">&nbsp;<strong>{{ $invoice->student_name }}</strong></span>
                        </div>
                        <div class="project-row">
                            <span class="project-label">&nbsp;CÓDIGO</span>
                            <span class="project-value">&nbsp;<strong>{{ $invoice->inscription->player->unique_code ?? 'N/A' }}</strong></span>
                        </div>
                        <div class="project-row">
                            <span class="project-label">&nbsp;EMAIL</span>
                            <span class="project-value">&nbsp;<strong>{{ $invoice->inscription->player->email ?? 'N/A' }}</strong></span>
                        </div>
                        <div class="project-row">
                            <span class="project-label">&nbsp;FECHA</span>
                            <span class="project-value"><strong class="bold">&nbsp;{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</strong></span>
                        </div>
                        <div class="project-row">
                            <span class="project-label">&nbsp;VENCIMIENTO</span>
                            <span class="project-value"><strong class="bold">&nbsp;{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</strong></span>
                        </div>
                    </div>
                </td>
                <td style="text-align: right;">
                    <!-- Información de la empresa -->
                    <div id="company">
                        <div><strong class="bold">&nbsp;{{ $school->name ?? 'INSTITUCIÓN EDUCATIVA' }}</strong></div>
                        <div><strong class="bold">&nbsp;{{ $school->address ?? 'Dirección no especificada' }}</strong></div>
                        <div><strong class="bold">&nbsp;Tel {{ $school->phone ?? 'N/A' }}</strong></div>
                        <div><strong class="bold">&nbsp;Email {{ $school->email ?? 'N/A' }}</strong></div>
                        <div><strong class="bold">&nbsp;Estado @if($invoice->status == 'paid')
                        <span class="badge badge-success ">Pagada</span>
                        @elseif($invoice->status == 'partial')
                        <span class="badge badge-warning">Parcial</span>
                        @elseif($invoice->status == 'pending')
                        <span class="badge badge-danger">Pendiente</span>
                        @else
                        <span class="badge badge-secondary">Cancelada</span>
                        @endif</strong>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </header>

    <main>
        <table>
            <thead>
                <tr>
                    <th class="service">#</th>
                    <th class="desc">DESCRIPCIÓN</th>
                    <th class="desc">TIPO</th>
                    <th class="text-center">CANT</th>
                    <th class="text-right">PRECIO UNIT.</th>
                    <th class="text-right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                <tr>
                    <td class="service">{{ $index + 1 }}</td>
                    <td class="desc">
                        @if($item->month)
                            <strong>Mes:</strong> {{ $item->description }}
                        @else
                            {{ $item->description }}
                        @endif
                    </td>
                    <td class="desc">
                        @if($item->type == 'monthly')
                            <span class="badge badge-secondary">MENSUALIDAD</span>
                        @elseif($item->type == 'enrollment')
                            <span class="badge badge-secondary">MATRÍCULA</span>
                        @else
                            <span class="badge badge-secondary">OTROS</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 0) }}</td>
                    <td class="text-right">${{ number_format($item->total, 0) }}</td>
                </tr>
                @endforeach

                <tr>
                    <td colspan="5">SUBTOTAL</td>
                    <td class="total">${{ number_format($invoice->total_amount, 0) }}</td>
                </tr>
                <tr>
                    <td colspan="5">SALDO PENDIENTE</td>
                    <td class="total">${{ number_format($invoice->total_amount - $invoice->paid_amount, 0) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="grand total">PAGADO</td>
                    <td class="grand total text-right">${{ number_format($invoice->paid_amount, 0) }}</td>
                </tr>

            </tbody>
        </table>
        @if($invoice->payments && $invoice->payments->count() > 0)
        <div >
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
                        <td class="text-right">${{ number_format($payment->amount, 0) }}</td>
                        <td>{{ $payment->creator->name ?? 'SISTEMA' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        @if($invoice->notes)
        <div id="notices">
            <div>OBSERVACIONES</div>
            <div class="notice">{{ $invoice->notes }}</div>
        </div>
        @endif
    </main>
</body>
</html>