<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Items Facturas Pendientes</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
</head>
<body>

<table class="table-full title">
    <tr>
        <td class="text-left" width="20%">
            <img src="{{ $school->logo_local }}" width="70" height="70">
        </td>
        <td class="text-center school-title" width="60%">{{ $school->name }}<br>Items Facturas Pendientes {{$date}}</td>
        <td class="text-right" width="20%">
            <img src="{{ $school->logo_local }}" width="70" height="70">
        </td>
    </tr>
</table>

<table class="table-full detail detail-lines">
    <thead>
        <tr class="tr-tit">
            <th>&nbsp;Factura&nbsp;</th>
            <th>&nbsp;Creado&nbsp;</th>
            <th>&nbsp;Deportista&nbsp;</th>
            <th>&nbsp;Tipo&nbsp;</th>
            <th>&nbsp;Descripción&nbsp;</th>
            <!-- <th>&nbsp;Metodo Pago&nbsp;</th> -->
            <th class="text-center">&nbsp;Cantidad&nbsp;</th>
            <th class="text-right">&nbsp;Precio Unitario&nbsp;</th>
            <th class="text-right">&nbsp;Total&nbsp;</th>
            <th class="text-center">&nbsp;Estado&nbsp;</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>

            <td>&nbsp;{{ $item->invoice->invoice_number }}&nbsp;</td>
            <td>&nbsp;{{ $item->created_at->format('d/m/Y') }}&nbsp;</td>
            <td>&nbsp;{{ $item->invoice->student_name }}&nbsp;</td>
            <td>&nbsp;
                @if($item->type == 'monthly')
                Mensualidad
                @else
                Item
                @endif
            &nbsp;</td>
            <td>&nbsp;{{ $item->description }}&nbsp;</td>
            <!-- <td>&nbsp;
                @switch($item->payment_method)
                    @case('cash')
                    Efectivo
                    @break
                    @case('card')
                    Tarjeta
                    @break
                    @case('transfer')
                    Transferencia
                    @break
                    @case('check')
                    Cheque
                    @break
                    @case('other')
                    Otro
                    @break
                @endswitch
            &nbsp;</td> -->
            <td class="text-center">&nbsp;{{ $item->quantity }}&nbsp;</td>
            <td class="text-right">&nbsp;{{ number_format($item->unit_price, 0, ',', '.') }}&nbsp;</td>
            <td class="text-right">&nbsp;{{ number_format($item->total, 0, ',', '.') }}&nbsp;</td>
            @if($item->is_paid == true)
            <td style="background:green; color: black;" class="text-center">&nbsp;Pagada&nbsp;</td>
            @else
            <td style="background:red; color: white;" class="text-center">&nbsp;Pendiente&nbsp;</td>
            @endif
        </tr>
        @endforeach
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <!-- <th></th> -->
            <th>Totales:</th>
            <th class="text-right">&nbsp;{{ number_format($items->sum('unit_price'), 0, ',', '.') }}&nbsp;</th>
            <th class="text-right">&nbsp;{{ number_format($items->sum('total'), 0, ',', '.') }}&nbsp;</th>
            <th></th>
        </tr>
    </tbody>



</table>