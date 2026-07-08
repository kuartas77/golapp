<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Deudores</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
</head>
<body>

<table class="table-full title">
    <tr>
        <td class="text-left" width="20%">
            <img src="{{ $school->logo_local }}" width="70" height="70">
        </td>
        <td class="text-center school-title" width="60%">
            {{ $school->name }}<br>INFORME DE DEUDORES<br>
            <small>Año {{ $year }} - {{ $group }} - {{ $date }}</small>
        </td>
        <td class="text-right" width="20%">
            <img src="{{ $school->logo_local }}" width="70" height="70">
        </td>
    </tr>
</table>

<table class="table-full detail detail-lines">
    <thead>
        <tr class="tr-tit">
            <th class="text-center">#</th>
            <th class="text-center">Código</th>
            <th>&nbsp;Deportista&nbsp;</th>
            <th>&nbsp;Categoría&nbsp;</th>
            <th>&nbsp;Grupo de entrenamiento&nbsp;</th>
            <th>&nbsp;Debe&nbsp;</th>
            @if($showTotalDebt)
                <th class="text-right">&nbsp;Total Deuda&nbsp;</th>
            @endif
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                <td class="text-center">&nbsp;{{ $loop->iteration }}&nbsp;</td>
                <td class="text-center">&nbsp;{{ $row['unique_code'] }}&nbsp;</td>
                <td>&nbsp;{{ $row['student_name'] }}&nbsp;</td>
                <td>&nbsp;{{ $row['category'] }}&nbsp;</td>
                <td>&nbsp;{{ $row['training_group'] }}&nbsp;</td>
                <td>
                    &nbsp;{{ collect($row['debt_items'])->map(fn ($item) => $item['label'].($showItemAmounts ? ' ('.number_format($item['amount'], 0, ',', '.').')' : ''))->implode(', ') }}&nbsp;
                </td>
                @if($showTotalDebt)
                    <td class="text-right">&nbsp;{{ number_format($row['total_debt'], 0, ',', '.') }}&nbsp;</td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ $showTotalDebt ? 7 : 6 }}" class="text-center">&nbsp;No se encontraron deudores para los filtros seleccionados.&nbsp;</td>
            </tr>
        @endforelse
        @if($showTotalDebt)
            <tr>
                <th colspan="6" class="text-right">Total deuda:</th>
                <th class="text-right">&nbsp;{{ number_format($rows->sum('total_debt'), 0, ',', '.') }}&nbsp;</th>
            </tr>
        @endif
    </tbody>
</table>
