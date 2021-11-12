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
            <img src="{{ asset('ms-icon-310x310.png') }}" width="70" height="70">
        </td>
        <td class="text-center school-title" width="60%">{{env('APP_NAME', 'Laravel')}}<br>CONTROL DE COMPETENCIA
        </td>
        <td class="text-right" width="20%">
            <img src="{{ asset('ms-icon-310x310.png') }}" width="70" height="70">
        </td>
    </tr>
</table>
<table class="table-full detail detail-lines">
    <tr>
        <td class="bold">Grupo: {{ $match->competitionGroup->full_name_group }}</td>
        <td class="bold">Torneo: {{ $match->tournament->name }}</td>
        <td class="bold">Partido #: {{ $match->num_match }}</td>
        <td class="bold">Fecha: {{ $match->date }}</td>
        <td class="bold text-center">Resultado Final</td>
    </tr>
    <tr>
        <td class="bold">Hora: {{ $match->hour }}</td>
        <td class="bold">Lugar: {{ $match->place }}</td>
        <td class="bold">Director Tecnico: {{ $match->competitionGroup->professor->name }}</td>
        <td class="bold">Nombre del rival: {{ $match->nombre_rival }}</td>
        <td class="bold text-center">SOCCER: {{ $match->final_score_format }} :RIVAL</td>
    </tr>
</table>

<table class="table-full detail detail-lines">
    <tr class="tr-tit">
        <td width="2%" class="bold text-center">#</td>
        <td width="20%" class="bold text-center">Deportista</td>
        <td width="4%" class="bold text-center">Cat</td>
        <td width="6%" class="bold text-center">Tel</td>
        <td width="3%" class="bold text-center">Ast</td>
        <td width="4%" class="bold text-center">Titular</td>
        <td width="5%" class="bold text-center">Jugó Apx.</td>
        <td width="15%" class="bold text-center">Pos</td>
        <td width="3%" class="bold text-center">Goles</td>
        <td width="4%" class="bold text-center">T. Ama</td>
        <td width="4%" class="bold text-center">T. Roj</td>
        <td width="3%" class="bold text-center">Cal</td>
        <td width="27%" class="bold text-center">Observación</td>
    </tr>
    @php
        $cantidad = 0;
    @endphp
    @foreach($match->skillsControls as $control)
        @php
            $cantidad = $loop->count + 1;
        @endphp
        <tr class="tr-info">
            <td class="bold text-center">{{$loop->iteration}}</td>
            <td class="text-center">{{ $control->inscription->player->full_names }}</td>
            <td class="text-center">{{ $control->inscription->category }}</td>
            <td class="text-center">{{ $control->inscription->player->mobile }}</td>
            <td class="text-center">{!! $control->assistance == 1 ? '&#10003;':'' !!}</td>
            <td class="text-center">{!! $control->titular == 1 ? '&#10003;':'' !!}</td>
            <td class="text-center">{{ $control->played_approx }}</td>
            <td class="text-center">{{ $control->position }}</td>
            <td class="text-center">{{ $control->goals == 0 ? 0 : $control->goles }}</td>
            <td class="text-center">{{ $control->yellow_cards }}</td>
            <td class="text-center">{{ $control->red_cards }}</td>
            <td class="text-center">{{ $control->qualification }}</td>
            <td>{{ mb_strtolower($control->observation, 'UTF-8') }}</td>

        </tr>
    @endforeach
    @php
        $resultado = (20 - $cantidad);
    @endphp
    @for ($i = 0; $i <= $resultado; $i++)
        <tr class="tr-info">
            <td class="bold text-center">{{$cantidad++}}</td>
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
        <td colspan="11" class="text-center">{{ mb_strtoupper($match->general_concept, 'UTF-8') }}</td>
    </tr>
</table>

</body>
</html>
