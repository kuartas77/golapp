<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Control de competencia</title>
    <link rel="stylesheet" href="{{ asset('css/dompdf.css') }}">
</head>
<body>

<table class="table-full title">
    <tr>
        <td class="text-left" width="20%">
            <img src="{{ $school->logo_local }}" width="70" height="70">
        </td>
        <td class="text-center school-title" width="60%">{{ $school->name }}<br>CONTROL DE COMPETENCIA
        </td>
        <td class="text-right" width="20%">
            <img src="{{ $school->logo_local }}" width="70" height="70">
        </td>
    </tr>
</table>
<table class="table-full detail detail-lines">
    <tr>
        <td class="bold">&nbsp;Grupo: {{ $match->competitionGroup->full_name_group }}</td>
        <td class="bold">&nbsp;Torneo: {{ $match->tournament->name }}</td>
        <td class="bold">&nbsp;Partido #: {{ $match->num_match }}</td>
        <td class="bold">&nbsp;Fecha: {{ $match->date }} {{ $match->hour }}</td>

    </tr>
    <tr>
        <td class="bold">&nbsp;Lugar: {{ $match->place }} </td>
        <td class="bold">&nbsp;Director Tecnico: {{ $match->competitionGroup->professor->name }}</td>
        <td colspan="2" class="bold text-center">{{ $school->name }}: {{ $match->final_score->soccer }} - {{ $match->final_score->rival }} :{{ $match->rival_name }}</td>
    </tr>
</table>

<table class="table-full detail detail-lines">
<tr class="tr-tit">
        <td width="2%" class="bold text-center">#</td>
        <td width="20%" class="bold text-center">Deportista</td>
        <td width="4%" class="bold text-center">Cat</td>
        <td width="3%" class="bold text-center">Ast</td>
        <td width="4%" class="bold text-center">Titular</td>
        <td width="5%" class="bold text-center">Jugó Apx.</td>
        <td width="15%" class="bold text-center">Posición</td>
        <td width="3%" class="bold text-center">Goles</td>
        <td width="3%" class="bold text-center">Asist Gol</td>
        <td width="3%" class="bold text-center">Salvadas</td>
        <td width="4%" class="bold text-center">T. Ama</td>
        <td width="4%" class="bold text-center">T. Roj</td>
        <td width="3%" class="bold text-center">Cal</td>
        <td width="33%" class="bold text-center">Observación</td>
    </tr>
    @foreach($match->skillsControls as $control)
        <tr class="tr-info">
            <td class="bold text-center">{{$loop->iteration}}</td>
            <td class=""><small>{{$control->inscription->player->unique_code}} - {{ $control->inscription->player->full_names }}</small></td>
            <td class="text-center">{{ $control->inscription->category }}</td>
            <td class="text-center">{!! $control->assistance == 1 ? '&#10004;':'&#10008;' !!}</td>
            <td class="text-center">{!! $control->titular == 1 ? '&#10004;':'&#10008;' !!}</td>
            <td class="text-center">{{ $control->played_approx }}</td>
            <td class="text-center">{{ $control->position }}</td>
            <td class="text-center">{{ $control->goals}}</td>
            <td class="text-center">{{ $control->goal_assists}}</td>
            <td class="text-center">{{ $control->goal_saves}}</td>
            <td class="text-center">{{ $control->yellow_cards }}</td>
            <td class="text-center">{{ $control->red_cards }}</td>
            <td class="text-center">{{ $control->qualification }}</td>
            <td>{{ mb_strtolower($control->observation, 'UTF-8') }}</td>
        </tr>
    @endforeach
    @for ($i = 0; $i <= $result; $i++)
        <tr class="tr-info">
            <td class="bold text-center">{{$count++}}</td>
            <td>&nbsp;</td>
            <!-- <td>&nbsp;</td> -->
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
    <tr>
        <td colspan="2" class="bold">Concepto general</td>
        <td colspan="13" class="text-center">{{ mb_strtoupper($match->general_concept, 'UTF-8') }}</td>
    </tr>
</table>

</body>
</html>
