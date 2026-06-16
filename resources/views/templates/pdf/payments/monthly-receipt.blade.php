<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Recibo de mensualidad</title>
    <style>
        body {
            color: #1f2933;
            font-family: sans-serif;
            font-size: 10px;
            line-height: 1.4;
        }

        .header {
            border-bottom: 1px solid #d7dde5;
            margin-bottom: 10px;
            padding-bottom: 8px;
            text-align: center;
        }

        .school {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .title {
            color: #4b5563;
            font-size: 10px;
            margin-top: 2px;
        }

        .row {
            border-bottom: 1px solid #eef1f4;
            padding: 5px 0;
        }

        .label {
            color: #6b7280;
            display: block;
            font-size: 8px;
            text-transform: uppercase;
        }

        .value {
            font-size: 10px;
            font-weight: bold;
        }

        .amount {
            background: #f3f7fb;
            border: 1px solid #d7dde5;
            margin-top: 10px;
            padding: 8px;
            text-align: center;
        }

        .amount .value {
            font-size: 16px;
        }

        .footer {
            color: #6b7280;
            font-size: 8px;
            margin-top: 10px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school">{{ $school->name }}</div>
        <div class="title">Recibo de pago de mensualidad</div>
    </div>

    <div class="row">
        <span class="label">Deportista</span>
        <span class="value">{{ $player->full_names }}</span>
    </div>
    <div class="row">
        <span class="label">Codigo unico</span>
        <span class="value">{{ $payment->unique_code }}</span>
    </div>
    <div class="row">
        <span class="label">Periodo</span>
        <span class="value">{{ $month_label }} {{ $payment->year }}</span>
    </div>
    <div class="row">
        <span class="label">Estado</span>
        <span class="value">{{ $status_label }}</span>
    </div>

    <div class="amount">
        <span class="label">Valor recibido</span>
        <span class="value">$ {{ number_format($amount, 0, ',', '.') }}</span>
    </div>

    <div class="footer">
        Emitido el {{ $issued_at->format('Y-m-d H:i') }}
    </div>
</body>
</html>
