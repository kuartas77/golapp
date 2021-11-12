<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{strtoupper($month)}} {{$group_name}}</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
    <style type="text/css">
        .texto {
            font-size: 9.5px;
        }
        html {
            margin-top: 0px;
            margin-bottom: 0px;
        }
    </style>
</head>
<body>

<div class="page">
    <table class="table-encabezado" style="margin:10px auto 0;" border="0">
        <tr>
            <td align="center" style="display:block;margin:auto;width: 60px;height: 60px;">
                <img src="{{ asset('ms-icon-310x310.png') }}">
            </td>
            <td></td>
            <td align="center" valign="middle">{{env('APP_NAME', 'Laravel')}}<br>PLANILLA DE ASISTENCIA AÑO_ {{$year}} - MES: {{strtoupper($month)}}
            </td>
            <td></td>
            <td align="center" style="display:block;margin:auto;width: 60px;height: 60px;">
                <img src="{{ asset('ms-icon-310x310.png') }}">
            </td>
        </tr>
    </table>
    <br>
    <table class="table tabla-bordeada table-condensed" style="margin:0 auto 10px;">
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

    <table class="table table-condensed tabla-bordeada" style="margin:auto;">
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
                <td class="center texto">{{ $assist->inscription->player->phones}} - {{ $assist->inscription->player->mobile}}</td>
                @for ($index = 1; $index <= $classDays->count(); $index++)
                    <td class="center texto">
                        @php
                            $column = numbersToLetters($index);
                            $countAS += $assist->$column == 'as' ? 1 : 0;
                        @endphp
                        {{ $assist->$column == null ? '': $optionAssist[$assist->$column] }}
                    </td>
                @endfor
                <td class="center texto"> {{percent($countAS, $classDays->count())}}%</td>
            </tr>
        @endforeach

        @for ($i = 0; $i <= $result; $i++)
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
    <br>
    <table class="table-condensed">
        <tr class="tr-tit">
            <td class="texto">ASISTENCIA:X</td>
            <td class="texto">FALTA:F</td>
            <td class="texto">EXCUSA:E</td>
            <td class="texto">RETIRO:R</td>
            <td class="texto">INCAPACIDAD:I</td>
        </tr>
    </table>
</div>
</body>

</html>
