<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Pagos</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
</head>
<body>
@php
    $paymentFields = \App\Models\Payment::paymentFields();
    $debtStatus = \App\Models\Payment::$debt;
    $pendingStatus = \App\Models\Payment::$pending;
    $selectedStatus = (int) ($selected_status ?? -1);
@endphp

<table class="table-full title">
    <tr>
        <td class="text-left" width="20%">
            <img src="{{ $school->logo_local }}" width="70" height="70">
        </td>
        <td class="text-center school-title" width="60%">{{ $school->name }}<br>PLANILLA DE PAGOS</td>
        <td class="text-right" width="20%">
            <img src="{{ $school->logo_local }}" width="70" height="70">
        </td>
    </tr>
    <!--<tr>-->
    <!--    <td></td>-->
    <!--    <td class="text-center bold">{{$group->name}}</td>-->
    <!--    <td></td>-->
    <!--</tr>-->
</table>

<table class="table-full detail detail-lines">
    <thead>
    <tr class="tr-tit">
            <th class="text-center">#</th>
            <th class="text-center">Año</th>
            <th class="text-center">Nombres</th>
            <th class="text-center">Categoria</th>
            <th class="text-center">Matrícula</th>
            <th class="text-center">Ene</th>
            <th class="text-center">Feb</th>
            <th class="text-center">Mar</th>
            <th class="text-center">Abr</th>
            <th class="text-center">May</th>
            <th class="text-center">Jun</th>
            <th class="text-center">Jul</th>
            <th class="text-center">Ago</th>
            <th class="text-center">Sep</th>
            <th class="text-center">Oct</th>
            <th class="text-center">Nov</th>
            <th class="text-center">Dic</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $payment)
        <tr>
            <td class="text-center">&nbsp;{{$loop->iteration}}</td>
            <td class="text-center">&nbsp;{{$payment->year}}</td>
            <td>
                &nbsp;<small>{{ $payment->unique_code }}</small>&nbsp;<small>{{ $payment->inscription->player->full_names }}</small>
            </td>
            <td class="text-center">&nbsp;<small>{{ $payment->category }}</small>&nbsp;</td>
            @foreach($paymentFields as $field)
                @php
                    $value = (int) $payment->{$field};
                    $amount = null;

                    if ($selectedStatus === $debtStatus) {
                        if ($value === $debtStatus) {
                            $amountField = \App\Models\Payment::amountFieldFor($field);
                            $amount = number_format((float) data_get($payment, $amountField, 0), 0, ',', '.');
                        } elseif ($value === $pendingStatus) {
                            $amount = '--';
                        }
                    }
                @endphp
                @include('templates.payments.color', ['value' => $value, 'amount' => $amount])
            @endforeach
        </tr>
        @endforeach
    </tbody>
</table>
