<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Pagos Torneo {{$tournament->name}}</title>
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
</table>

<table class="table-full detail detail-lines">
    <thead>
    <tr class="tr-tit">
            <th class="text-center">Nombres</th>
            <th class="text-center">Torneo</th>
            <th class="text-center">Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($payments as $payment)
        <tr>
            <td class="text-center">
                &nbsp;<small>{{ $payment->unique_code }}</small>&nbsp;<small>{{ $payment->inscription->player->full_names }}</small>
            </td>
            <td class="text-center">{{$tournament->name}}</td>
            @include('templates.payments.tournaments.color',['value' => $payment->status])
        </tr>
        @endforeach
    </tbody>
</table>