<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{$group->name}}</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
</head>
<body>

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
    <tr>
        <td></td>
        <td class="text-center bold">{{$group->name}}</td>
        <td></td>
    </tr>
</table>

<table class="table-full detail detail-lines">
    <thead>
    <tr class="tr-tit">
            <th class="text-center">Año</th>
            <th class="text-center">Nombres</th>
            <th class="text-center">Enero</th>
            <th class="text-center">Febrero</th>
            <th class="text-center">Marzo</th>
            <th class="text-center">Abril</th>
            <th class="text-center">Mayo</th>
            <th class="text-center">Junio</th>
            <th class="text-center">Julio</th>
            <th class="text-center">Agosto</th>
            <th class="text-center">Septiembre</th>
            <th class="text-center">Octubre</th>
            <th class="text-center">Noviembre</th>
            <th class="text-center">Diciembre</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $payment)
        <tr>
            <td class="text-center">&nbsp;{{$payment->year}}</td>
            <td>
                &nbsp;<small>{{ $payment->unique_code }}</small>&nbsp;<small>{{ $payment->inscription->player->full_names }}</small>
            </td>
            @include('templates.payments.color',['value' => $payment->january])
            @include('templates.payments.color',['value' => $payment->february])
            @include('templates.payments.color',['value' => $payment->march])
            @include('templates.payments.color',['value' => $payment->april])
            @include('templates.payments.color',['value' => $payment->may])
            @include('templates.payments.color',['value' => $payment->june])
            @include('templates.payments.color',['value' => $payment->july])
            @include('templates.payments.color',['value' => $payment->august])
            @include('templates.payments.color',['value' => $payment->september])
            @include('templates.payments.color',['value' => $payment->october])
            @include('templates.payments.color',['value' => $payment->november])
            @include('templates.payments.color',['value' => $payment->december])
        </tr>
        @endforeach
    </tbody>
</table>