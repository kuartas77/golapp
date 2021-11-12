<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{strtoupper($month)}} {{$group_name}}</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
</head>
<body>

<table class="table-full title">
    <tr>
        <td class="text-left" width="20%">
            <img src="{{ asset('ms-icon-310x310.png') }}" width="70" height="70">
        </td>
        <td class="text-center school-title" width="60%">{{env('APP_NAME', 'Laravel')}}<br>PLANILLA DE ASISTENCIA
            AÑO {{$year}} - MES: {{strtoupper($month)}}
        </td>
        <td class="text-right" width="20%">
            <img src="{{ asset('ms-icon-310x310.png') }}" width="70" height="70">
        </td>
    </tr>
</table>

<table class="table-full detail detail-lines">
    <tbody>
    <tr class="tr-tit">
        <td class="center texto" colspan="4">Grupo: {{$group_name}}</td>
    </tr>
    <tr class="tr-tit">
        <td class="texto">Profesor: {{$group->professor->name}}</td>
        <td class="texto">Categoria: {{implode(',',$group->category)}}</td>
        <td class="texto">Días: {{$group->schedule->day->days}}</td>
        <td class="texto">Horario: {{$group->schedule->schedule}}</td>
    </tr>
    </tbody>
</table>

<table class="table-full detail detail-lines">
    {{--Inicio cabeceras--}}
    @include('templates.assists.thead_pdf', ['classDays' => $classDays])
    {{--Fin cabeceras--}}
    @foreach($assists as $assist)
        @php
            $countAS = 0;
        @endphp
        <tr class="tr-info">
            <td class="center texto">{{$loop->iteration}}</td>
            <td class="center texto">{{ $assist->inscription->player->full_names }}</td>
            <td class="center texto">{{ $assist->inscription->category }}</td>
            <td class="center texto">{{ $assist->inscription->player->phones}}
                - {{ $assist->inscription->player->mobile}}</td>
            @for ($index = 1; $index <= count($classDays); $index++)
                <td class="center texto">
                    @php
                        $column = numbersToLetters($index);
                        $countAS += $assist->$column == 'as' ? 1 : 0;
                    @endphp
                    {{ $assist->$column == null ? '': $optionAssist[$assist->$column] }}
                </td>
            @endfor
            <td class="center texto"> {{percent($countAS, count($classDays))}}%</td>
        </tr>
    @endforeach
    @php
        $resultado = (40 - $count);
    @endphp
    @for ($i = 0; $i <= $resultado; $i++)
        <tr class="tr-info">
            <td class="center texto">{{ $count++ }}</td>
            <td class="texto">&nbsp;</td>
            <td class="texto">&nbsp;</td>
            <td class="texto">&nbsp;</td>
            @for ($j = 1; $j <= count($classDays); $j++)
                <td class="texto">&nbsp;</td>
            @endfor
            <td class="texto">&nbsp;</td>
        </tr>
    @endfor
</table>

<table class="table-full detail">
    <tr class="tr-tit">
        <td class="texto">ASISTENCIA:X</td>
        <td class="texto">FALTA:F</td>
        <td class="texto">EXCUSA:E</td>
        <td class="texto">RETIRO:R</td>
        <td class="texto">INCAPACIDAD:I</td>
    </tr>
</table>
</body>

</html>
