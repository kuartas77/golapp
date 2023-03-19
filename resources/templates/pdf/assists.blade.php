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
            <img src="{{ $school->logo_local }}" width="70" height="70">
        </td>
        <td class="text-center school-title" width="60%">{{ $school->name }}<br>PLANILLA DE ASISTENCIA
            AÑO {{$year}} - MES: {{strtoupper($month)}}
        </td>
        <td class="text-right" width="20%">
            <img src="{{ $school->logo_local }}" width="70" height="70">
        </td>
    </tr>
</table>

<table class="table-full detail detail-lines">
    <tbody>
    <tr class="tr-tit">
        <td class="center texto" colspan="4">Grupo: {{$group->name}}</td>
    </tr>
    <tr class="tr-tit">
        <td class="texto">&nbsp;Formador(es): {{$group->instructors_names}}</td>
        <td class="texto">&nbsp;Categoria: {{implode(',',$group->category)}}</td>
        <td class="texto">&nbsp;Días de entrenamiento: {{$group->days}}</td>
        <td class="texto">&nbsp;Horarios: {{$group->schedules}}</td>
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
            <td class="center texto" style="width:1%">{{$loop->iteration}}</td>
            <td class="texto" style="width:15%">&nbsp;<small>{{ $assist->inscription->player->unique_code }}</small> - {{ $assist->inscription->player->full_names }}</td>
            <td class="center texto" style="width:3%">{{ $assist->inscription->category }}</td>
            <td class="center texto" style="width:7%">
                <small>{{ ($assist->inscription->player->mobile ?? ($assist->inscription->player->phones ?? '')) }}</small>
            </td>
            @for ($index = 1; $index <= count($classDays); $index++)
                <td class="center texto" style="width:2.5%">
                    @php
                        $column = numbersToLetters($index);
                        $countAS += $assist->$column == 'as' ? 1 : 0;
                    @endphp
                    {{ $assist->$column == null ? '': $optionAssist[$assist->$column] }}
                </td>
            @endfor
            <td class="center texto" style="width:3%"> {{percent($countAS, count($classDays))}}%</td>
        </tr>
    @endforeach
    @for ($i = 0; $i <= $result; $i++)
        <tr class="tr-info">
            <td class="center texto" style="width:1%">{{ $count++ }}</td>
            <td class="texto" style="width:15%">&nbsp;</td>
            <td class="texto" style="width:3%">&nbsp;</td>
            <td class="texto" style="width:7%">&nbsp;</td>
            @for ($j = 1; $j <= count($classDays); $j++)
                <td class="texto" style="width:2.5%">&nbsp;</td>
            @endfor
            <td class="texto" style="width:3%">&nbsp;</td>
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
