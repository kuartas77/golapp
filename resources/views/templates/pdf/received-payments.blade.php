<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Pagos</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
</head>
<body>

<table class="table-full title">
    <tr>
        <td class="text-left" width="20%">
            <img src="{{ $school->logo_local }}" width="70" height="70">
        </td>
        <td class="text-center school-title" width="60%">
            {{ $school->name }}<br>INFORME DE PAGOS<br>
            <small>Año {{ $year }} - {{ $group }} - {{ $date }}</small>
            @if($playerSearch ?? '')
                <br><small>Deportista: {{ $playerSearch }}</small>
            @endif
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
            <th>&nbsp;Pagó&nbsp;</th>
            @if($showTotalPaid)
                <th class="text-right">&nbsp;Total Pagado&nbsp;</th>
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
                    &nbsp;{{ collect($row['payment_items'])->map(fn ($item) => $item['label'].($showItemAmounts ? ' ('.number_format($item['amount'], 0, ',', '.').')' : ''))->implode(', ') }}&nbsp;
                </td>
                @if($showTotalPaid)
                    <td class="text-right">&nbsp;{{ number_format($row['total_paid'], 0, ',', '.') }}&nbsp;</td>
                @endif
            </tr>
        @empty
            <tr>
                <td colspan="{{ $showTotalPaid ? 7 : 6 }}" class="text-center">&nbsp;No se encontraron pagos para los filtros seleccionados.&nbsp;</td>
            </tr>
        @endforelse
        @if($showTotalPaid)
            <tr>
                <th colspan="6" class="text-right">Total pagado:</th>
                <th class="text-right">&nbsp;{{ number_format($rows->sum('total_paid'), 0, ',', '.') }}&nbsp;</th>
            </tr>
        @endif
    </tbody>
</table>
</body>
</html>
