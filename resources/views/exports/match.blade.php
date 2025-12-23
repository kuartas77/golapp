<!DOCTYPE html>
<html lang="es"><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>Control de competencia</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
    <style type="text/css">
        .texto{
            font-size: 10px;
        }
        html {
            margin-top: 0px;
            margin-bottom: 0px;
        }
    </style>
</head><body>
<table class="table-encabezado" style="margin:10px auto 0;" border="0">
    <tr>
        <td align="center" style="display:block;margin:auto;width: 60px;height: 60px;">
            <img src="{{ asset('ms-icon-310x310.png') }}">
        </td>
        <td></td>
        <td align="center" valign="middle">{{env('APP_NAME', 'Laravel')}}<br>CONTROL DE COMPETENCIA
        </td>
        <td></td>
        <td align="center" style="display:block;margin:auto;width: 60px;height: 60px;">
            <img src="{{ asset('ms-icon-310x310.png') }}">
        </td>
    </tr>
</table>
<br>
<table class="tabla-bordeada table-condensed" style="margin:auto">
    <tr >
        <td class="texto">Grupo: {{ $match->competitionGroup->full_name_group }}</td>
        <td class="texto">Torneo: {{ $match->tournament->name }}</td>
        <td class="texto">Partido #: {{ $match->num_match }}</td>
        <td class="texto">Fecha: {{ $match->date }}</td>
        <td class="center texto">Resultado Final</td>
    </tr>
    <tr >
        <td class="texto">Hora: {{ $match->hour }}</td>
        <td class="texto">Lugar: {{ $match->place }}</td>
        <td class="texto">Director Tecnico: {{ $match->competitionGroup->professor->name }}</td>
        <td class="texto">Nombre del rival: {{ $match->nombre_rival }}</td>
        <td class="center texto">SOCCER: {{ $match->final_score_format }} :RIVAL</td>
    </tr>
</table>

<table class="tabla-bordeada table-condensed" style="margin:auto;">
    <tr class="tr-tit">
        <td width="2%" align="center" class="texto">#</td>
        <td width="20%" align="center" class="texto">Deportista</td>
        <td width="5%" align="center" class="texto">Cat</td>
        <td width="5%" align="center" class="texto">Tel</td>
        <td width="3%" align="center" class="texto">Ast</td>
        <td width="3%" align="center" class="texto">Titular</td>
        <td width="7%" align="center" class="texto">Jugó Apx.</td>
        <td width="6%" align="center" class="texto">Pos</td>
        <td width="5%" align="center" class="texto">Goles</td>
        <td width="5%" align="center" class="texto">Asist Gol</td>
        <td width="5%" align="center" class="texto">Atajadas</td>
        <td width="6%" align="center" class="texto">Amarillas</td>
        <td width="6%" align="center" class="texto">Rojas</td>
        <td width="5%" align="center" class="texto">Cal</td>
        <td width="30%" class="texto">Observación</td>
    </tr>
    @php
        $cantidad = 0;
    @endphp
    @foreach($match->skillsControls as $control)
        @php
            $cantidad = $loop->count + 1;
        @endphp
        <tr class="tr-info">
            <td class="texto" align="center">{{$loop->iteration}}</td>
            <td class="texto">{{ $control->inscription->player->full_names }}</td>
            <td class="texto" align="center">{{ $control->inscription->category }}</td>
            <td class="texto" align="center">{{ $control->inscription->player->mobile }}</td>
            <td class="texto" align="center">{{ html()->checkbox('assistance', $control->assistance ==1)->attributes(['disabled']) }}</td>
            <td class="texto" align="center">{{ html()->checkbox('titular', $control->titular == 1)->attributes(['disabled']) }}</td>
            <td class="texto" align="center">{{ $control->played_approx }}</td>
            <td class="texto" align="center">{{ $control->position }}</td>
            <td class="texto" align="center">{{ $control->goals == 0 ? 0 : $control->goles }}</td>
            <td class="texto" align="center">{{ $control->goal_assists == 0 ? 0 : $control->goal_assists }}</td>
            <td class="texto" align="center">{{ $control->goal_saves == 0 ? 0 : $control->goal_saves }}</td>
            <td class="texto" align="center">{{ $control->yellow_cards }}</td>
            <td class="texto" align="center">{{ $control->red_cards }}</td>
            <td class="texto" align="center">{{ $control->qualification }}</td>
            <td class="texto">{{ mb_strtolower($control->observation, 'UTF-8') }}</td>
        </tr>
    @endforeach
    @php
        $resultado = (20 - $cantidad);
    @endphp
    @for ($i = 0; $i <= $resultado; $i++)
        <tr class="tr-info">
            <td class="texto">{{$cantidad++}}</td>
            <td class="texto">&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    @endfor
    <tr >
        <td colspan="2" class="texto">Concepto general</td>
        <td colspan="11" class="texto">{{ mb_strtolower($match->general_concept, 'UTF-8') }}</td>
    </tr>
</table>

</body></html>
